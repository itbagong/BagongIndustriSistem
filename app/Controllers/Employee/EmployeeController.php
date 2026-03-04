<?php

namespace App\Controllers\Employee;

use App\Controllers\BaseController;
use App\Models\DepartmentModel;
use App\Models\DivisionModel;
use App\Models\EmployeeMaster\DepartmentModel as EmployeeMasterDepartmentModel;
use App\Models\EmployeeMaster\DivisionModel as EmployeeMasterDivisionModel;
use App\Models\EmployeeMaster\EmployeeStatusModel;
use App\Models\EmployeeMaster\EmploymentStatusModel;
use App\Models\EmployeeMaster\GenderModel;
use App\Models\EmployeeMaster\GroupModel;
use App\Models\EmployeeMaster\LastEducationModel;
use App\Models\EmployeeMaster\ReligionModel;
use App\Models\EmployeeMaster\SiteModel;
use App\Models\EmployeeModel;
use Config\ImportEmployee;
use PhpOffice\PhpSpreadsheet\IOFactory;

class EmployeeController extends BaseController
{
    protected EmployeeModel $model;
    protected EmployeeMasterDepartmentModel $dept;
    protected EmployeeMasterDivisionModel $div;
    protected GenderModel $gend;
    protected GroupModel $grp;
    protected EmployeeStatusModel $empss;
    protected EmploymentStatusModel $empns;
    protected LastEducationModel $edu;
    protected SiteModel $site;
    protected ReligionModel $rlg;

    public function __construct()
    {
        $this->model = new EmployeeModel();
        $this->dept = new EmployeeMasterDepartmentModel();
        $this->div = new EmployeeMasterDivisionModel();
        $this->gend = new GenderModel();
        $this->grp = new GroupModel();
        $this->empss = new EmployeeStatusModel();
        $this->empns = new EmploymentStatusModel();
        $this->edu = new LastEducationModel();
        $this->site = new SiteModel();
        $this->rlg = new ReligionModel();
        helper(['form', 'url']);
    }

    // ── Index ────────────────────────────────────────────────────
    public function index(): string
    {
        $data = [
            'departments' => $this->model->getDistinct('departments'),
            'divisions'   => $this->model->getDistinct('divisions'),
            'menus' => $this->data['menus'] ?? []
        ];
        return view('employees/index', $data);
    }

    // ── DataTables server-side AJAX ──────────────────────────────
    public function data(): \CodeIgniter\HTTP\ResponseInterface
    {
        if (! $this->request->is('post')) {
            return $this->response->setStatusCode(403);
        }

        $post = $this->request->getPost();

        $draw     = (int)    ($post['draw']               ?? 1);
        $start    = (int)    ($post['start']              ?? 0);
        $length   = (int)    ($post['length']             ?? 10);
        $search   = (string) ($post['search']['value']    ?? '');
        $orderCol = (int)    ($post['order'][0]['column']  ?? 2);
        $orderDir =           $post['order'][0]['dir']    ?? 'asc';

        // Resolve column index → DB expression via model's map
        $orderColumn = $this->model->columnMap[$orderCol] ?? 'e.name';

        $filters = [
            'department'        => $post['department']        ?? '',
            'division'          => $post['division']          ?? '',
            'employment_status' => $post['employment_status'] ?? '',
            'employee_status'   => $post['employee_status']   ?? '',
        ];

        $recordsTotal    = $this->model->countAll();
        $recordsFiltered = $this->model->countFiltered($search, $filters);
        $rows            = $this->model->getFiltered($search, $filters, $start, $length, $orderColumn, $orderDir);
        $stats           = $this->model->getStats();

        return $this->response->setJSON([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $rows,
            'stats'           => $stats,
        ]);
    }

    // ── Detail — AJAX partial for modal ──────────────────────────
    public function detail(string $id): string
    {
        $employee = $this->model->findEmployee($id);
        if (! $employee) {
            return '<div class="alert alert-danger">Data tidak ditemukan.</div>';
        }
        return view('employees/detail_partial', ['employee' => $employee]);
    }

    // ── Create ───────────────────────────────────────────────────
    public function create(): string
    {
        return view('employees/form', [
            'employee' => null,
            'mode'     => 'create',
            ...$this->formDropdowns(),
        ]);
    }

    public function store(): \CodeIgniter\HTTP\ResponseInterface
    {
        if (! $this->validate($this->validationRules())) {
            return redirect()->back()->withInput()
                             ->with('errors', $this->validator->getErrors());
        }

        $data       = $this->collectPost();
        $data['id'] = $this->model->generateUuid();

        $this->model->insert($data);

        return redirect()->to(base_url('employees'))
                         ->with('success', 'Karyawan berhasil ditambahkan.');
    }

    // ── Edit ─────────────────────────────────────────────────────
    public function edit(string $id): \CodeIgniter\HTTP\ResponseInterface|string
    {
        $employee = $this->model->findEmployee($id);
        if (! $employee) {
            return redirect()->to(base_url('employees'))
                             ->with('error', 'Data tidak ditemukan.');
        }

        return view('employees/form', [
            'employee' => $employee,
            'mode'     => 'edit',
            ...$this->formDropdowns(),
        ]);
    }

    public function update(string $id): \CodeIgniter\HTTP\ResponseInterface
    {
        if (! $this->validate($this->validationRules($id))) {
            return redirect()->back()->withInput()
                             ->with('errors', $this->validator->getErrors());
        }

        $this->model->update($id, $this->collectPost());

        return redirect()->to(base_url('employees'))
                         ->with('success', 'Data karyawan berhasil diperbarui.');
    }

    // ── Delete (soft) ────────────────────────────────────────────
    public function delete(string $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $employee = $this->model->findEmployee($id);
        if (! $employee) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
            ]);
        }

        $this->model->softDelete($id);

        return $this->response->setJSON(['success' => true]);
    }

    // ── Import ───────────────────────────────────────────────
    public function upload()
    {
        $file = $this->request->getFile('file_upload');

        if (!$file || !$file->isValid()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'File tidak valid'
            ]);
        }

        $newName = $file->getRandomName();
        $file->move(WRITEPATH . 'uploads/employee', $newName);

        return $this->response->setJSON([
            'status' => 'success',
            'file'   => $newName
        ]);
    }

    public function process()
    {
        $fileName = $this->request->getPost('file');
        $offset   = (int) $this->request->getPost('offset');
        $limit    = 20;

        $filePath = WRITEPATH . 'uploads/employee/' . $fileName;
        $config   = new \Config\ImportEmployee();
        $fieldMap = $config->fields;

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
        $sheet       = $spreadsheet->getActiveSheet();

        // ── Read all rows as formatted strings ───────────────────────
        $rows = [];
        foreach ($sheet->getRowIterator() as $row) {
            $rowData = [];
            foreach ($row->getCellIterator() as $cell) {
                $rowData[$cell->getColumn()] = (string)$cell->getFormattedValue();
            }
            $rows[] = $rowData;
        }

        // ── Normalize header row ─────────────────────────────────────
        $headerRow = array_shift($rows);
        $header    = array_map(fn($h) => strtolower(trim($h)), $headerRow);
        $colIndex  = array_flip($header);

        $slice = array_slice($rows, $offset, $limit, true);

        $logs      = [];
        $processed = 0;

        foreach ($slice as $index => $row) {
            $rowNumber = $index + 2;

            try {
                $record = [];

                foreach ($fieldMap as $excelHeader => $cfg) {
                    if (! isset($colIndex[$excelHeader])) {
                        continue;
                    }

                    $col     = $colIndex[$excelHeader];
                    $raw = $row[$col] ?? '';                     // original raw value (before any cleaning)
                    $trimmedRaw = trim($raw);                     // for emptiness check
                    $value   = trim(preg_replace('/[\x00-\x1F\xA0]/u', '', $row[$col] ?? ''));
                    $dbField = $cfg['db_field'] ?? str_replace(' ', '_', $excelHeader);

                    if ($value === '') {
                        $record[$dbField] = null;
                        // If raw had visible content, log it
                        if ($trimmedRaw !== '') {
                            $logs[] = "Row {$rowNumber}: {$excelHeader} - " . json_encode($raw);
                        }
                        continue;
                    }

                    switch ($cfg['type']) {
                        case 'direct':
                            $record[$dbField] = $value;
                            break;

                        case 'date':
                            $record[$dbField] = $this->parseDate($value, $cfg['format'] ?? 'Y-m-d');
                            break;

                        case 'master':
                            $modelProp = $cfg['model'];
                            $related   = null;

                            $related = $this->{$modelProp}
                                            ->where('LOWER(name)', strtolower($value))
                                            ->first();

                            if (! $related && ($cfg['partial_match'] ?? false)) {
                                $all = $this->{$modelProp}->findAll();
                                foreach ($all as $item) {
                                    if (stripos($value, $item['name']) !== false) {
                                        $related = $item;
                                        break;
                                    }
                                }
                            }

                            $record[$dbField] = $related['id'] ?? null;
                            break;
                    }
                        // After processing, if final value is empty but raw had content, log it
                    if ($trimmedRaw !== '' && ($record[$dbField] === null || $record[$dbField] === '')) {
                        $logs[] = "Row {$rowNumber}: {$excelHeader} - " . json_encode($raw);
                    }
                }

                // ── Strict empty check ───────────────────────────────
                if (($record['nik'] ?? '') === '' || ($record['name'] ?? '') === '') {
                    throw new \Exception('NIK / Name kosong');
                }

                // ── Upsert by NIK ────────────────────────────────────
                $existing = $this->model->where('nik', $record['nik'])->first();

                if ($existing) {
                    $this->model->update($existing['id'], $record);
                    $logs[] = "Row {$rowNumber}: 🔄 {$record['name']} updated (NIK {$record['nik']})";
                } else {
                    $record['id'] = $this->model->generateUuid(); // ← add this
                    $this->model->insert($record);
                    $logs[] = "Row {$rowNumber}: ✅ {$record['name']} inserted";
                }

            } catch (\Exception $e) {
                $logs[] = "Row {$rowNumber}: ❌ " . $e->getMessage();
            }

            $processed++;
        }

        $done = ($offset + $limit) >= count($rows);

        return $this->response->setJSON([
            'logs'       => $logs,
            'nextOffset' => $offset + $processed,
            'done'       => $done,
        ]);
    }

    // ── Export CSV ───────────────────────────────────────────────
    public function export(): void
    {
        $filters = [
            'department'        => $this->request->getGet('department')        ?? '',
            'division'          => $this->request->getGet('division')          ?? '',
            'employment_status' => $this->request->getGet('employment_status') ?? '',
            'employee_status'   => $this->request->getGet('employee_status')   ?? '',
        ];

        $rows = $this->model->getFiltered('', $filters, 0, PHP_INT_MAX, 'e.name', 'asc');

        $filename = 'employees_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        // UTF-8 BOM so Excel opens it correctly
        fwrite($out, "\xEF\xBB\xBF");

        fputcsv($out, [
            'NIK', 'BIS ID', 'Nama', 'Gender', 'Department', 'Division',
            'User', 'Job Position', 'PKWT Date', 'Tenure',
            'Employee Status', 'Employment Status', 'Cutoff Date',
            'National ID', 'Phone', 'Place of Birth', 'Age',
            'Last Education', 'Site', 'Address', 'Religion',
        ]);

        foreach ($rows as $r) {
            fputcsv($out, [
                $r['nik'],               $r['bis_id'],
                $r['name'],              $r['gender'],
                $r['department'],        $r['division'],
                $r['user'],              $r['job_position'],
                $r['pkwt_date'],         $r['tenure'],
                $r['employee_status'],   $r['employment_status'],
                $r['cutoff_date'],       $r['national_id'],
                $r['phone_number'],      $r['place_of_birth'],
                $r['age'],               $r['last_education'],
                $r['site'],              $r['address'],
                $r['religion'],
            ]);
        }

        fclose($out);
        exit;
    }

    // ── Private helpers ──────────────────────────────────────────

    /** Validation rules — NIK uniqueness excludes current record on update. */
    private function validationRules(string $exceptId = ''): array
    {
        $nikRule = 'required|min_length[3]|is_unique[employees.nik,id,' . $exceptId . ']';

        return [
            'nik'                 => $nikRule,
            'name'                => 'required|min_length[2]',
            'gender_id'           => 'required',
            'employment_status_id'=> 'required',
            'employee_status_id'  => 'required',
        ];
    }

    /**
     * Collect only the DB-storable fields from POST.
     * Note: tenure and age are NOT stored — they are computed at runtime.
     */
    private function collectPost(): array
    {
        $fields = [
            'nik', 'bis_id', 'name',
            'gender_id', 'department_id', 'division_id', 'job_position_id',
            'site_id', 'employee_status_id', 'employment_status_id',
            'pkwt_date', 'cutoff_date',
            'national_id', 'phone_number', 'place_of_birth', 'date_of_birth',
            'last_education_id', 'religion_id', 'address', 'user_id',
        ];

        $data = [];
        foreach ($fields as $field) {
            $value        = $this->request->getPost($field);
            $data[$field] = ($value !== '' && $value !== null) ? $value : null;
        }
        return $data;
    }

    /**
     * Load all lookup tables needed for create/edit form <select> elements.
     */
    private function formDropdowns(): array
    {
        return [
            'genders'              => $this->model->getDistinct('genders'),
            'departments'          => $this->model->getDistinct('departments'),
            'divisions'            => $this->model->getDistinct('divisions'),
            'job_positions'        => $this->model->getDistinct('groups'),
            'sites'                => $this->model->getDistinct('sites'),
            'employee_statuses'    => $this->model->getDistinct('employee_statuses'),
            'employment_statuses'  => $this->model->getDistinct('employment_statuses'),
            'last_educations'      => $this->model->getDistinct('last_educations'),
            'religions'            => $this->model->getDistinct('religions'),
        ];
    }

    /**
     * Parse a date string from Excel into Y-m-d for the DB.
     *
     * Handles three cases:
     *  a) Excel numeric serial  → PhpSpreadsheet converts it automatically
     *  b) String matching $format (e.g. 31/12/2024)
     *  c) Any other parseable string (fallback via strtotime)
     */
    private function parseDate(string $value, string $format): ?string
    {
        // 1. Already ISO Y-m-d? (fast path)
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }

        // 2. Numeric Excel serial date?
        if (is_numeric($value) && $value > 0 && $value < 100000) {
            try {
                $dateTime = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
                return $dateTime->format('Y-m-d');
            } catch (\Exception $e) {
                // fall through
            }
        }

        // 3. Try the configured format (from $fieldMap)
        $date = \DateTime::createFromFormat($format, $value);
        if ($date && $date->format($format) === $value) {
            return $date->format('Y-m-d');
        }

        // 4. Try common formats, including those that allow single-digit day/month
        $commonFormats = [
            // ISO with/without leading zeros
            'Y-m-d', 'Y-n-j', 'Y-m-j', 'Y-n-d',
            // European
            'd/m/Y', 'j/n/Y', 'd/n/Y', 'j/m/Y',
            // US
            'm/d/Y', 'n/j/Y', 'm/j/Y', 'n/d/Y',
            // Dotted
            'd.m.Y', 'j.n.Y', 'd.n.Y', 'j.m.Y',
            // Text month
            'd-M-Y', 'j-M-Y',
        ];

        foreach ($commonFormats as $tryFormat) {
            $date = \DateTime::createFromFormat($tryFormat, $value);
            if ($date && $date->format($tryFormat) === $value) {
                return $date->format('Y-m-d');
            }
        }

        // 5. Last resort: strtotime (handles many cases, but can be ambiguous)
        $ts = strtotime($value);
        if ($ts !== false) {
            return date('Y-m-d', $ts);
        }

        return null;
    }

    private function normalize($string)
    {
        $string = strtolower($string);
        $string = trim($string);

        // remove spaces
        $string = str_replace(' ', '', $string);

        // remove special characters
        $string = preg_replace('/[^a-z0-9]/', '', $string);

        return $string;
    }
}