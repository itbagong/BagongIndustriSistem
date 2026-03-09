<?php

namespace App\Controllers\Employee;

use App\Controllers\BaseController;
use App\Libraries\ChunkReadFilter;
use App\Models\EmployeeMaster\DepartmentModel as EmployeeMasterDepartmentModel;
use App\Models\EmployeeMaster\DivisionModel as EmployeeMasterDivisionModel;
use App\Models\EmployeeMaster\EmployeeStatusModel;
use App\Models\EmployeeMaster\EmploymentStatusModel;
use App\Models\EmployeeMaster\GenderModel;
use App\Models\EmployeeMaster\GroupModel;
use App\Models\EmployeeMaster\LastEducationModel;
use App\Models\EmployeeMaster\ReligionModel;
use App\Models\EmployeeMaster\SiteModel;
use App\Models\EmployeeImportJobModel;
use App\Models\EmployeeModel;
use CodeIgniter\HTTP\ResponseInterface;

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

    /**
     * Rows read from the spreadsheet per reload cycle.
     * PhpSpreadsheet loads only (CHUNK_SIZE + 1 header) rows into RAM at a time,
     * keeping peak memory well under the 128 MB limit even for 10 000+ row files.
     */
    private const CHUNK_SIZE = 200;

    /**
     * Hard memory ceiling.  If RSS exceeds this between chunks the import is
     * aborted gracefully rather than OOM-killed.
     */
    private const MEMORY_CEILING = 100 * 1024 * 1024; // 100 MB

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

    // ── Index ────────────────────────────────────────────────────────────
    public function index(): string
    {
        return view('employees/index', [
            'departments'         => $this->model->getDistinct('departments'),
            'divisions'           => $this->model->getDistinct('divisions'),
            'employee_statuses'   => array_column($this->empss->orderBy('name')->findAll(), 'name'),
            'employment_statuses' => array_column($this->empns->orderBy('name')->findAll(), 'name'),
            'menus'               => $this->data['menus'] ?? [],
        ]);
    }

    // ── DataTables ───────────────────────────────────────────────────────
    public function data(): ResponseInterface
    {
        if (! $this->request->is('post')) {
            return $this->response->setStatusCode(403);
        }

        $post     = $this->request->getPost();
        $draw     = (int)    ($post['draw']               ?? 1);
        $start    = (int)    ($post['start']              ?? 0);
        $length   = (int)    ($post['length']             ?? 25);
        $search   = (string) ($post['search']['value']    ?? '');
        $orderCol = (int)    ($post['order'][0]['column'] ?? 2);
        $orderDir =           $post['order'][0]['dir']    ?? 'asc';

        $orderColumn = $this->model->columnMap[$orderCol] ?? 'e.name';

        $filters = [
            'department'        => $post['department']        ?? '',
            'division'          => $post['division']          ?? '',
            'employment_status' => $post['employment_status'] ?? '',
            'employee_status'   => $post['employee_status']   ?? '',
        ];

        return $this->response->setJSON([
            'draw'            => $draw,
            'recordsTotal'    => $this->model->countAll(),
            'recordsFiltered' => $this->model->countFiltered($search, $filters),
            'data'            => $this->model->getFiltered($search, $filters, $start, $length, $orderColumn, $orderDir),
            'stats'           => $this->model->getStats(),
        ]);
    }

    // ── Detail ───────────────────────────────────────────────────────────
    public function detail(string $id): string
    {
        $employee = $this->model->findEmployee($id);
        if (! $employee) {
            return '<div class="alert alert-danger">Data tidak ditemukan.</div>';
        }
        return view('employees/partials/employee_detail', ['employee' => $employee]);
    }

    // ── Create ───────────────────────────────────────────────────────────
    public function create(): string
    {
        return view('employees/form', [
            'employee' => null,
            'mode'     => 'create',
            'menus'    => $this->data['menus'] ?? [],
            ...$this->formDropdowns(),
        ]);
    }

    public function store(): ResponseInterface
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

    // ── Edit ─────────────────────────────────────────────────────────────
    public function edit(string $id): ResponseInterface|string
    {
        $employee  = $this->model->findEmployee($id);
        $userModel = new \App\Models\UserModel();
        $hasLogin  = $userModel->where('username', $employee['nik'])->first();

        if (! $employee) {
            return redirect()->to(base_url('employees'))->with('error', 'Data tidak ditemukan.');
        }

        return view('employees/form', [
            'employee' => $employee,
            'hasLogin' => (bool) $hasLogin,
            'mode'     => 'edit',
            'menus'    => $this->data['menus'] ?? [],
            ...$this->formDropdowns(),
        ]);
    }

    public function update(string $id): ResponseInterface
    {
        if (! $this->validate($this->validationRules($id))) {
            return redirect()->back()->withInput()
                             ->with('errors', $this->validator->getErrors());
        }

        $this->model->update($id, $this->collectPost());

        return redirect()->to(base_url('employees'))
                         ->with('success', 'Data karyawan berhasil diperbarui.');
    }

    // ── Delete (soft) ────────────────────────────────────────────────────
    public function delete(string $id): ResponseInterface
    {
        $employee = $this->model->findEmployee($id);
        if (! $employee) {
            return $this->response->setJSON(['success' => false, 'message' => 'Data tidak ditemukan.']);
        }

        $this->model->softDelete($id);

        return $this->response->setJSON(['success' => true]);
    }

    // ═════════════════════════════════════════════════════════════════════
    //  IMPORT — page
    // ═════════════════════════════════════════════════════════════════════

    public function import(): string
    {
        return view('employees/import/index', [
            'menus' => $this->data['menus'] ?? [],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    //  JOBS LIST
    // ─────────────────────────────────────────────────────────────────────

    public function importJobs(): string
    {
        $jobModel = new EmployeeImportJobModel();

        $perPage = 20;
        $page    = max(1, (int) ($this->request->getGet('page') ?? 1));
        $offset  = ($page - 1) * $perPage;

        $jobs  = $jobModel->getAllJobs($perPage, $offset);
        $total = $jobModel->countJobs();

        // Annotate each job with whether its file is still on disk
        $uploadDir = WRITEPATH . 'uploads/employee/';
        foreach ($jobs as &$job) {
            $job['file_exists'] = file_exists($uploadDir . $job['file_name']);
        }
        unset($job);

        return view('employees/import/jobs', [
            'jobs'       => $jobs,
            'total'      => $total,
            'page'       => $page,
            'perPage'    => $perPage,
            'totalPages' => (int) ceil($total / $perPage),
            'menus'      => $this->data['menus'] ?? [],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    //  DOWNLOAD FILE
    // ─────────────────────────────────────────────────────────────────────

    public function jobDownload(int $id): ResponseInterface
    {
        $jobModel = new EmployeeImportJobModel();
        $job      = $jobModel->find($id);

        if (! $job) {
            return $this->response->setStatusCode(404)
                         ->setJSON(['error' => 'Job not found.']);
        }

        $filePath = WRITEPATH . 'uploads/employee/' . $job['file_name'];

        if (! file_exists($filePath)) {
            return $this->response->setStatusCode(404)
                         ->setJSON(['error' => 'File no longer exists on disk.']);
        }

        // Derive a friendly download name from the original stored name.
        // Format: employees_import_<job_id>.<ext>
        $ext          = pathinfo($job['file_name'], PATHINFO_EXTENSION);
        $downloadName = "employees_import_{$id}.{$ext}";

        return $this->response
            ->setHeader('Content-Type', 'application/octet-stream')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $downloadName . '"')
            ->setHeader('Content-Length', (string) filesize($filePath))
            ->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->setBody(file_get_contents($filePath));
    }

    // ─────────────────────────────────────────────────────────────────────
    //  RESTART JOB
    //  Resets counters + logs to zero so the SSE stream can re-process.
    // ─────────────────────────────────────────────────────────────────────

    public function jobRestart(int $id): ResponseInterface
    {
        $jobModel = new EmployeeImportJobModel();
        $job      = $jobModel->find($id);

        if (! $job) {
            return $this->response->setStatusCode(404)
                         ->setJSON(['success' => false, 'message' => 'Job not found.']);
        }

        if ($job['status'] === 'running') {
            return $this->response->setStatusCode(409)
                         ->setJSON(['success' => false, 'message' => 'Job is currently running. Stop it before restarting.']);
        }

        $filePath = WRITEPATH . 'uploads/employee/' . $job['file_name'];
        if (! file_exists($filePath)) {
            return $this->response->setStatusCode(422)
                         ->setJSON(['success' => false, 'message' => 'The original file is no longer on disk. Please upload the file again.']);
        }

        // Check global lock (another job may be running)
        $running = $jobModel->getRunningJob();
        if ($running && $running['id'] !== $id) {
            return $this->response->setStatusCode(409)
                         ->setJSON([
                             'success' => false,
                             'message' => "Another import (Job #{$running['id']}) is currently running. Please wait.",
                         ]);
        }

        if (! $jobModel->resetJob($id)) {
            return $this->response->setStatusCode(500)
                         ->setJSON(['success' => false, 'message' => 'Failed to reset job.']);
        }

        return $this->response->setJSON([
            'success'  => true,
            'message'  => 'Job reset to pending. Open the import page to start streaming.',
            'job_id'   => $id,
            'file'     => $job['file_name'],
            'total'    => (int) $job['total'],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    //  DELETE JOB
    // ─────────────────────────────────────────────────────────────────────

    public function jobDelete(int $id): ResponseInterface
    {
        $jobModel = new EmployeeImportJobModel();
        $job      = $jobModel->find($id);

        if (! $job) {
            return $this->response->setStatusCode(404)
                         ->setJSON(['success' => false, 'message' => 'Job not found.']);
        }

        if ($job['status'] === 'running') {
            return $this->response->setStatusCode(409)
                         ->setJSON(['success' => false, 'message' => 'Cannot delete a running job.']);
        }

        // Remove file from disk (if it still exists)
        $filePath = WRITEPATH . 'uploads/employee/' . $job['file_name'];
        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        $jobModel->deleteJob($id);

        return $this->response->setJSON(['success' => true, 'message' => 'Job deleted.']);
    }

    // ─────────────────────────────────────────────────────────────────────
    //  UPLOAD  (Step 1 — creates pending job, does NOT delete the file)
    // ─────────────────────────────────────────────────────────────────────

    public function upload(): ResponseInterface
    {
        $jobModel = new EmployeeImportJobModel();

        // Concurrency lock
        $running = $jobModel->getRunningJob();
        if ($running) {
            return $this->response->setJSON([
                'status'   => 'locked',
                'message'  => 'An import is already in progress. Please wait until it completes.',
                'job_id'   => $running['id'],
                'progress' => [
                    'processed' => (int) $running['processed'],
                    'total'     => (int) $running['total'],
                ],
            ]);
        }

        $file = $this->request->getFile('file_upload');
        if (! $file || ! $file->isValid()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'File tidak valid.']);
        }

        $newName = $file->getRandomName();
        $file->move(WRITEPATH . 'uploads/employee', $newName);
        $filePath = WRITEPATH . 'uploads/employee/' . $newName;

        // Count rows with a read-data-only pass (low memory)
        try {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);
            $totalRows   = max(0, $spreadsheet->getActiveSheet()->getHighestDataRow() - 1);
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet, $reader);
            gc_collect_cycles();
        } catch (\Exception $e) {
            @unlink($filePath);
            return $this->response->setJSON(['status' => 'error', 'message' => 'Cannot read file: ' . $e->getMessage()]);
        }

        $jobId = $jobModel->insert([
            'file_name' => $newName,
            'status'    => 'pending',
            'total'     => $totalRows,
            'logs'      => '[]',
        ], true);

        return $this->response->setJSON([
            'status'    => 'success',
            'file'      => $newName,
            'totalRows' => $totalRows,
            'job_id'    => (int) $jobId,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    //  STREAM  (Step 2 — chunked, memory-safe SSE)
    //
    //  PhpSpreadsheet's ChunkReadFilter reloads the file for every
    //  CHUNK_SIZE-row window, so peak RSS stays bounded regardless of
    //  spreadsheet size.  The file is intentionally NOT deleted here;
    //  it is removed only when the job is deleted (jobDelete()).
    // ─────────────────────────────────────────────────────────────────────

    public function stream(): void
    {
        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');
        header('Connection: keep-alive');

        set_time_limit(0);
        ignore_user_abort(false);  // stop processing when the client disconnects

        $jobId    = (int) $this->request->getGet('job_id');
        $jobModel = new EmployeeImportJobModel();
        $job      = $jobModel->find($jobId);

        if (! $job) {
            $this->emit('error', ['message' => 'Job not found.']);
            exit();
        }
        if ($job['status'] !== 'pending') {
            $this->emit('error', ['message' => "Job is already in state '{$job['status']}'. Use the status endpoint to poll progress."]);
            exit();
        }
        if (! $jobModel->claimJob($jobId)) {
            $this->emit('error', ['message' => 'Job was claimed by another process.']);
            exit();
        }

        $filePath = WRITEPATH . 'uploads/employee/' . $job['file_name'];
        if (! file_exists($filePath)) {
            $jobModel->markFailed($jobId, 'File not found on server.');
            $this->emit('error', ['message' => 'File not found on server.']);
            exit();
        }

        // ── Resolve column → DB field mapping from the header row ────────
        $config   = new \Config\ImportEmployee();
        $fieldMap = $config->fields;

        // Read ONLY the header row to build colIndex cheaply
        try {
            $headerFilter = new ChunkReadFilter();
            $headerFilter->setRows(2, 1); // row 1 is always returned by the filter
            $headerReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filePath);
            $headerReader->setReadDataOnly(true);
            $headerReader->setReadFilter($headerFilter);
            $headerSheet = $headerReader->load($filePath)->getActiveSheet();

            $colIndex = [];
            foreach ($headerSheet->getRowIterator(1, 1) as $row) {
                foreach ($row->getCellIterator() as $cell) {
                    $colIndex[$cell->getColumn()] = strtolower(trim((string) $cell->getFormattedValue()));
                }
            }
            // Flip: header-label → column letter
            $colIndex = array_flip($colIndex);
            unset($headerSheet);
        } catch (\Exception $e) {
            $jobModel->markFailed($jobId, $e->getMessage());
            $this->emit('error', ['message' => 'Cannot read file: ' . $e->getMessage()]);
            exit();
        }

        // Use the row count already determined accurately during upload().
        // getHighestDataRow() on a chunk-filtered sheet only sees the loaded
        // window and returns 1, which would stop the loop after the first row.
        $totalRows = (int) $job['total'];

        $this->emit('meta', ['total' => $totalRows]);

        // ── Counters + per-chunk log buffer ───────────────────────────────
        $processed = 0;
        $inserted  = 0;
        $updated   = 0;
        $skipped   = 0;
        $logBuf    = [];

        $flushBatch = function () use ($jobId, $jobModel, &$processed, &$inserted, &$updated, &$skipped, &$logBuf): void {
            $jobModel->bumpProgress($jobId, $processed, $inserted, $updated, $skipped);
            if ($logBuf) {
                $jobModel->appendLogs($jobId, $logBuf);
                $logBuf = [];
            }
        };

        // ── Chunk reader ──────────────────────────────────────────────────
        $chunkFilter = new ChunkReadFilter();
        $reader      = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $reader->setReadFilter($chunkFilter);

        for ($chunkStart = 2; $chunkStart <= $totalRows + 1; $chunkStart += self::CHUNK_SIZE) {

            // ── Memory guard (checked between chunks) ─────────────────
            if (memory_get_usage(true) > self::MEMORY_CEILING) {
                $msg = sprintf(
                    '⚠️ Memory ceiling reached (%.1f MB). Import aborted to protect the server.',
                    memory_get_usage(true) / 1024 / 1024
                );
                $logBuf[] = ['level' => 'error', 'message' => $msg];
                $this->emit('log', ['level' => 'error', 'message' => $msg]);
                $flushBatch();
                $jobModel->markFailed($jobId, 'Memory ceiling reached.');
                $this->emit('done', compact('processed', 'inserted', 'updated', 'skipped'));
                exit();
            }

            // ── Load just this chunk ──────────────────────────────────
            $chunkFilter->setRows($chunkStart, self::CHUNK_SIZE);

            try {
                $spreadsheet = $reader->load($filePath);
                $sheet       = $spreadsheet->getActiveSheet();
            } catch (\Exception $e) {
                $msg = '❌ Failed to load chunk starting at row ' . $chunkStart . ': ' . $e->getMessage();
                $logBuf[] = ['level' => 'error', 'message' => $msg];
                $this->emit('log', ['level' => 'error', 'message' => $msg]);
                break;
            }

            $chunkEnd = min($chunkStart + self::CHUNK_SIZE - 1, $totalRows + 1);

            foreach ($sheet->getRowIterator($chunkStart, $chunkEnd) as $row) {
                // Stop immediately if the client closed the connection
                if (connection_aborted()) {
                    $flushBatch();
                    $jobModel->markFailed($jobId, "Stopped by user at row {$processed}.");
                    $spreadsheet->disconnectWorksheets();
                    unset($spreadsheet, $sheet);
                    exit();
                }

                $rowNumber = $row->getRowIndex();

                // Collect raw cell values
                $rowData      = [];
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                foreach ($cellIterator as $cell) {
                    $rowData[$cell->getColumn()] = (string) $cell->getFormattedValue();
                }

                try {
                    $record = [];

                    foreach ($fieldMap as $excelHeader => $cfg) {
                        if (! isset($colIndex[$excelHeader])) {
                            continue;
                        }

                        $col        = $colIndex[$excelHeader];
                        $raw        = $rowData[$col] ?? '';
                        $trimmedRaw = trim($raw);
                        $value      = trim(preg_replace('/[\x00-\x1F\xA0]/u', '', $raw));
                        $dbField    = $cfg['db_field'] ?? str_replace(' ', '_', $excelHeader);

                        if ($value === '') {
                            $record[$dbField] = null;
                            if ($trimmedRaw !== '') {
                                $warnMsg  = "Row {$rowNumber}: {$excelHeader} — invisible chars stripped";
                                $logBuf[] = ['level' => 'warn', 'message' => $warnMsg];
                                $this->emit('log', ['level' => 'warn', 'message' => $warnMsg]);
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

                                $related = $this->{$modelProp}->where('LOWER(name)', $lower)->first();

                                if (! $related) {
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

                                if (! $related && ($cfg['partial_match'] ?? false)) {
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
                            $warnMsg  = "Row {$rowNumber}: {$excelHeader} — no match for " . json_encode($raw);
                            $logBuf[] = ['level' => 'warn', 'message' => $warnMsg];
                            $this->emit('log', ['level' => 'warn', 'message' => $warnMsg]);
                        }
                    }

                    if (($record['nik'] ?? '') === '' || ($record['name'] ?? '') === '') {
                        throw new \Exception('NIK / Name kosong');
                    }

                    $existing = $this->model->where('nik', $record['nik'])->first();

                    if ($existing) {
                        $this->model->update($existing['id'], $record);
                        $updated++;
                        $msg      = "Row {$rowNumber}: 🔄 {$record['name']} updated (NIK {$record['nik']})";
                        $logBuf[] = ['level' => 'update', 'message' => $msg];
                        $this->emit('log', ['level' => 'update', 'message' => $msg]);
                    } else {
                        $record['id'] = $this->model->generateUuid();
                        $this->model->insert($record);
                        $inserted++;
                        $msg      = "Row {$rowNumber}: ✅ {$record['name']} inserted";
                        $logBuf[] = ['level' => 'success', 'message' => $msg];
                        $this->emit('log', ['level' => 'success', 'message' => $msg]);
                    }
                } catch (\Exception $e) {
                    $skipped++;
                    $msg      = "Row {$rowNumber}: ❌ " . $e->getMessage();
                    $logBuf[] = ['level' => 'error', 'message' => $msg];
                    $this->emit('log', ['level' => 'error', 'message' => $msg]);
                }

                $processed++;
                $this->emit('progress', ['processed' => $processed, 'total' => $totalRows]);
            }

            // ── Release the chunk from memory ─────────────────────────
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet, $sheet);
            gc_collect_cycles();

            // ── Persist batch to DB ───────────────────────────────────
            $flushBatch();
        }

        // ── Final persist + completion ────────────────────────────────────
        $flushBatch();
        $jobModel->markDone($jobId, $processed, $inserted, $updated, $skipped);
        $this->emit('done', compact('processed', 'inserted', 'updated', 'skipped'));
        exit();
    }

    // ─────────────────────────────────────────────────────────────────────
    //  IMPORT STATUS  (polling fallback / jobs page live updates)
    // ─────────────────────────────────────────────────────────────────────

    public function importStatus(): ResponseInterface
    {
        $jobId = (int) $this->request->getGet('job_id');
        if (! $jobId) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'job_id is required.']);
        }

        $jobModel = new EmployeeImportJobModel();
        $job      = $jobModel->getJobStatus($jobId);

        if (! $job) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Job not found.']);
        }

        return $this->response->setJSON([
            'id'        => $job['id'],
            'status'    => $job['status'],
            'processed' => (int) $job['processed'],
            'total'     => (int) $job['total'],
            'inserted'  => (int) $job['inserted'],
            'updated'   => (int) $job['updated'],
            'skipped'   => (int) $job['skipped'],
            'message'   => $job['message'],
            'logs'      => $job['logs'],
        ]);
    }

    // ── Export CSV ───────────────────────────────────────────────────────
    public function export(): void
    {
        $search   = $this->request->getGet('search')           ?? '';
        $orderCol = (int) ($this->request->getGet('order_col') ?? 2);
        $orderDir = $this->request->getGet('order_dir')        ?? 'asc';

        $filters = [
            'department'        => $this->request->getGet('department')        ?? '',
            'division'          => $this->request->getGet('division')          ?? '',
            'employment_status' => $this->request->getGet('employment_status') ?? '',
            'employee_status'   => $this->request->getGet('employee_status')   ?? '',
        ];

        $orderColumn = $this->model->columnMap[$orderCol] ?? 'e.name';
        $rows        = $this->model->getFiltered($search, $filters, 0, PHP_INT_MAX, $orderColumn, $orderDir);

        $filename = 'karyawan_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out    = fopen('php://output', 'w');
        $asText = fn($v) => $v ? "\t" . $v : '';
        fwrite($out, "\xEF\xBB\xBF");

        fputcsv($out, [
            'NIK', 'BIS ID', 'Nama', 'Work User', 'Gender', 'Department', 'Division',
            'Job Position', 'Site', 'Status Karyawan', 'Status Kepegawaian',
            'PKWT Date', 'Cutoff Date', 'Tenure', 'No. KTP', 'No. HP',
            'Tempat Lahir', 'Tanggal Lahir', 'Umur', 'Pendidikan Terakhir',
            'Alamat', 'Agama', 'Dibuat',
        ]);

        foreach ($rows as $r) {
            fputcsv($out, [
                $r['nik']               ?? '',
                $r['bis_id']            ?? '',
                $r['name']              ?? '',
                $r['work_user']         ?? '',
                $r['gender']            ?? '',
                $r['department']        ?? '',
                $r['division']          ?? '',
                $r['job_position']      ?? '',
                $r['site']              ?? '',
                $r['employee_status']   ?? '',
                $r['employment_status'] ?? '',
                $r['pkwt_date']         ?? '',
                $r['cutoff_date']       ?? '',
                $r['tenure']            ?? '',
                $asText($r['national_id']),
                $asText($r['phone_number']),
                $r['place_of_birth']    ?? '',
                $r['date_of_birth']     ?? '',
                $r['age'] !== null ? $r['age'] . ' thn' : '',
                $r['last_education']    ?? '',
                $r['address']           ?? '',
                $r['religion']          ?? '',
                $r['created_at']        ?? '',
            ]);
        }

        fclose($out);
        exit;
    }

    // ── Create Login ─────────────────────────────────────────────────────
    public function createLogin(): ResponseInterface
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Forbidden']);
        }

        $json       = $this->request->getJSON(true);
        $employeeId = trim((string) ($json['employee_id'] ?? 0));
        $username   = trim((string) ($json['username']    ?? ''));

        if (! $employeeId || $username === '') {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Data tidak lengkap.']);
        }

        $employeeModel = new \App\Models\EmployeeModel();
        $employee      = $employeeModel->find($employeeId);

        if (! $employee) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Karyawan tidak ditemukan.']);
        }
        if ($employee['nik'] !== $username) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'NIK tidak sesuai dengan data karyawan.']);
        }

        $userModel    = new \App\Models\UserModel();
        $existingUser = $userModel->where('username', $username)->first();
        $userData     = [
            'username'    => $username,
            'email'       => $username,
            'password'    => 'Password.1',
            'employee_id' => $employeeId,
            'role_id'     => 3,
            'is_active'   => 1,
        ];

        try {
            if ($existingUser) {
                $userModel->update($existingUser['id'], ['password' => 'Password.1', 'is_active' => 1]);
                $message = 'Akun sudah ada. Password direset ke default.';
            } else {
                $userModel->insert($userData);
                $message = 'Akun login berhasil dibuat.';
            }

            return $this->response->setJSON(['success' => true, 'message' => $message, 'is_new' => ! $existingUser]);
        } catch (\Throwable $e) {
            log_message('error', '[createLogin] ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ── Private helpers ──────────────────────────────────────────────────

    private function emit(string $event, array $data): void
    {
        echo "event: {$event}\n";
        echo 'data: ' . json_encode($data) . "\n\n";
        if (ob_get_level()) {
            ob_flush();
        }
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
            'last_education_id', 'religion_id', 'address', 'work_user',
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
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }

        if (is_numeric($value) && $value > 0 && $value < 100000) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
            } catch (\Exception) {}
        }

        $date = \DateTime::createFromFormat($format, $value);
        if ($date && $date->format($format) === $value) {
            return $date->format('Y-m-d');
        }

        foreach ([
            'Y-m-d', 'Y-n-j', 'd/m/Y', 'j/n/Y', 'm/d/Y', 'n/j/Y',
            'd.m.Y', 'j.n.Y', 'd-M-Y', 'j-M-Y',
        ] as $fmt) {
            $date = \DateTime::createFromFormat($fmt, $value);
            if ($date && $date->format($fmt) === $value) {
                return $date->format('Y-m-d');
            }
        }

        $ts = strtotime($value);
        return $ts !== false ? date('Y-m-d', $ts) : null;
    }
}