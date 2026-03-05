<?php

namespace App\Controllers\Employee;

use App\Controllers\BaseController;
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
        $this->dept  = new EmployeeMasterDepartmentModel();
        $this->div   = new EmployeeMasterDivisionModel();
        $this->gend  = new GenderModel();
        $this->grp   = new GroupModel();
        $this->empss = new EmployeeStatusModel();
        $this->empns = new EmploymentStatusModel();
        $this->edu   = new LastEducationModel();
        $this->site  = new SiteModel();
        $this->rlg   = new ReligionModel();
        helper(['form', 'url']);
    }

    // ── Index ────────────────────────────────────────────────────
    public function index(): string
    {
        return view('employees/index', [
            'departments'          => $this->model->getDistinct('departments'),
            'divisions'            => $this->model->getDistinct('divisions'),
            // In index()
            'employee_statuses'   => array_column($this->empss->orderBy('name')->findAll(), 'name'),
            'employment_statuses' => array_column($this->empns->orderBy('name')->findAll(), 'name'),
            'menus'                => $this->data['menus'] ?? [],
        ]);
    }

    // ── DataTables server-side AJAX ──────────────────────────────
    public function data(): \CodeIgniter\HTTP\ResponseInterface
    {
        if (! $this->request->is('post')) {
            return $this->response->setStatusCode(403);
        }

        $post = $this->request->getPost();

        $draw     = (int)    ($post['draw']                ?? 1);
        $start    = (int)    ($post['start']               ?? 0);
        $length   = (int)    ($post['length']              ?? 25);
        $search   = (string) ($post['search']['value']     ?? '');
        $orderCol = (int)    ($post['order'][0]['column']  ?? 2);
        $orderDir =           $post['order'][0]['dir']     ?? 'asc';

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

    // ── Upload ───────────────────────────────────────────────────
    public function upload()
    {
        $file = $this->request->getFile('file_upload');

        if (!$file || !$file->isValid()) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'File tidak valid',
            ]);
        }

        $newName = $file->getRandomName();
        $file->move(WRITEPATH . 'uploads/employee', $newName);

        $filePath    = WRITEPATH . 'uploads/employee/' . $newName;
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
        $sheet       = $spreadsheet->getActiveSheet();
        $totalRows   = max(0, $sheet->getHighestDataRow() - 1);

        return $this->response->setJSON([
            'status'    => 'success',
            'file'      => $newName,
            'totalRows' => $totalRows,
        ]);
    }

    // ── Stream ───────────────────────────────────────────────────
    public function stream()
    {
        while (ob_get_level()) ob_end_clean();

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');
        header('Connection: keep-alive');

        set_time_limit(0);
        ignore_user_abort(false);

        $fileName = $this->request->getGet('file');
        $filePath = WRITEPATH . 'uploads/employee/' . $fileName;

        if (!$fileName || !file_exists($filePath)) {
            $this->emit('error', ['message' => 'File not found on server.']);
            exit();
        }

        try {
            $config      = new \Config\ImportEmployee();
            $fieldMap    = $config->fields;
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
            $sheet       = $spreadsheet->getActiveSheet();
        } catch (\Exception $e) {
            $this->emit('error', ['message' => 'Cannot read file: ' . $e->getMessage()]);
            exit();
        }

        $rows = [];
        foreach ($sheet->getRowIterator() as $row) {
            $rowData = [];
            foreach ($row->getCellIterator() as $cell) {
                $rowData[$cell->getColumn()] = (string) $cell->getFormattedValue();
            }
            $rows[] = $rowData;
        }

        $headerRow = array_shift($rows);
        $header    = array_map(fn($h) => strtolower(trim($h)), $headerRow);
        $colIndex  = array_flip($header);
        $totalRows = count($rows);

        $this->emit('meta', ['total' => $totalRows]);

        $processed = 0;
        $inserted  = 0;
        $updated   = 0;
        $skipped   = 0;

        foreach ($rows as $index => $row) {
            if (connection_aborted()) break;

            $rowNumber = $index + 2;

            try {
                $record = [];

                foreach ($fieldMap as $excelHeader => $cfg) {
                    if (!isset($colIndex[$excelHeader])) continue;

                    $col        = $colIndex[$excelHeader];
                    $raw        = $row[$col] ?? '';
                    $trimmedRaw = trim($raw);
                    $value      = trim(preg_replace('/[\x00-\x1F\xA0]/u', '', $row[$col] ?? ''));
                    $dbField    = $cfg['db_field'] ?? str_replace(' ', '_', $excelHeader);

                    if ($value === '') {
                        $record[$dbField] = null;
                        if ($trimmedRaw !== '') {
                            $this->emit('log', [
                                'level'   => 'warn',
                                'message' => "Row {$rowNumber}: {$excelHeader} — invisible chars stripped",
                            ]);
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
                            $lower     = strtolower($value);

                            // 1. Exact name match
                            $related = $this->{$modelProp}
                                ->where('LOWER(name)', $lower)
                                ->first();

                            // 2. Alias match via Postgres unnest
                            if (!$related) {
                                try {
                                    $tableName = $this->{$modelProp}->getTable();
                                    $related   = $this->{$modelProp}->query(
                                        "SELECT * FROM {$tableName}
                                         WHERE LOWER(?) = ANY(SELECT LOWER(unnest(aliases)))
                                         LIMIT 1",
                                        [$lower]
                                    )->getRowArray() ?: null;
                                } catch (\Throwable) {
                                    $related = null;
                                }
                            }

                            // 3. Partial name match fallback
                            if (!$related && ($cfg['partial_match'] ?? false)) {
                                foreach ($this->{$modelProp}->findAll() as $item) {
                                    if (stripos($value, $item['name']) !== false) {
                                        $related = $item;
                                        break;
                                    }
                                }
                            }

                            $record[$dbField] = $related['id'] ?? null;
                            break;
                    }

                    if ($trimmedRaw !== '' && ($record[$dbField] === null || $record[$dbField] === '')) {
                        $this->emit('log', [
                            'level'   => 'warn',
                            'message' => "Row {$rowNumber}: {$excelHeader} — no match for " . json_encode($raw),
                        ]);
                    }
                }

                if (($record['nik'] ?? '') === '' || ($record['name'] ?? '') === '') {
                    throw new \Exception('NIK / Name kosong');
                }

                $existing = $this->model->where('nik', $record['nik'])->first();

                if ($existing) {
                    $this->model->update($existing['id'], $record);
                    $updated++;
                    $this->emit('log', [
                        'level'   => 'update',
                        'message' => "Row {$rowNumber}: 🔄 {$record['name']} updated (NIK {$record['nik']})",
                    ]);
                } else {
                    $record['id'] = $this->model->generateUuid();
                    $this->model->insert($record);
                    $inserted++;
                    $this->emit('log', [
                        'level'   => 'success',
                        'message' => "Row {$rowNumber}: ✅ {$record['name']} inserted",
                    ]);
                }
            } catch (\Exception $e) {
                $skipped++;
                $this->emit('log', [
                    'level'   => 'error',
                    'message' => "Row {$rowNumber}: ❌ " . $e->getMessage(),
                ]);
            }

            $processed++;
            $this->emit('progress', ['processed' => $processed, 'total' => $totalRows]);
        }

        @unlink($filePath);

        $this->emit('done', [
            'processed' => $processed,
            'inserted'  => $inserted,
            'updated'   => $updated,
            'skipped'   => $skipped,
        ]);

        exit();
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

    private function emit(string $event, array $data): void
    {
        echo "event: {$event}\n";
        echo 'data: ' . json_encode($data) . "\n\n";
        if (ob_get_level()) ob_flush();
        flush();
    }

    private function validationRules(string $exceptId = ''): array
    {
        return [
            'nik'                  => 'required|min_length[3]|is_unique[employees.nik,id,' . $exceptId . ']',
            'name'                 => 'required|min_length[2]',
            'gender_id'            => 'required',
            'employment_status_id' => 'required',
            'employee_status_id'   => 'required',
        ];
    }

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

    private function formDropdowns(): array
    {
        return [
            'genders'             => $this->gend->where('is_deleted', false)->orderBy('name')->findAll(),
            'departments'         => $this->dept->where('is_deleted', false)->orderBy('name')->findAll(),
            'divisions'           => $this->div->where('is_deleted', false)->orderBy('name')->findAll(),
            'job_positions'       => $this->grp->where('is_deleted', false)->orderBy('name')->findAll(),
            'sites'               => $this->site->where('is_deleted', false)->orderBy('name')->findAll(),
            'employee_statuses'   => $this->empss->where('is_deleted', false)->orderBy('name')->findAll(),
            'employment_statuses' => $this->empns->where('is_deleted', false)->orderBy('name')->findAll(),
            'last_educations'     => $this->edu->where('is_deleted', false)->orderBy('name')->findAll(),
            'religions'           => $this->rlg->where('is_deleted', false)->orderBy('name')->findAll(),
        ];
    }

    private function parseDate(string $value, string $format): ?string
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) return $value;

        if (is_numeric($value) && $value > 0 && $value < 100000) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
            } catch (\Exception) {}
        }

        $date = \DateTime::createFromFormat($format, $value);
        if ($date && $date->format($format) === $value) return $date->format('Y-m-d');

        foreach ([
            'Y-m-d','Y-n-j','d/m/Y','j/n/Y','m/d/Y','n/j/Y',
            'd.m.Y','j.n.Y','d-M-Y','j-M-Y',
        ] as $fmt) {
            $date = \DateTime::createFromFormat($fmt, $value);
            if ($date && $date->format($fmt) === $value) return $date->format('Y-m-d');
        }

        $ts = strtotime($value);
        return $ts !== false ? date('Y-m-d', $ts) : null;
    }

    private function normalize(string $string): string
    {
        return preg_replace('/[^a-z0-9]/', '', strtolower(trim(str_replace(' ', '', $string))));
    }
}