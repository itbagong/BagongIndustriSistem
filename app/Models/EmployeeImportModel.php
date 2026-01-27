<?php

namespace App\Models;

use CodeIgniter\Model;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Employee Import Model
 * Handles bulk import from Excel with optimizations
 */
class EmployeeImportModel extends Model
{
    protected $table = 'employees_recruitment';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    private const DEFAULT_SHEET = 'Employees';
    private const LOG_EVERY_ROWS = 50;

    // Reference tables
    private const REF_TABLES = [
        'sites' => 'sites',
        'genders' => 'genders',
        'departments' => 'departments',
        'religions' => 'religions',
        'divisions' => 'divisions',
        'employment_statuses' => 'employment_statuses',
        'groups' => 'groups',
        'emergency_contact_relations' => 'emergency_contact_relations',
        'blood_types' => 'blood_types',
        'marital_statuses' => 'marital_statuses',
        'last_educations' => 'last_educations',
        'employee_statuses' => 'employee_statuses',
        'sections' => 'sections',
    ];

    private $refs = [];
    private $negCache = [];

    /**
     * Main import function
     */
    public function importFromExcel(string $filePath, bool $overwrite = false, bool $verbose = false): array
    {
        $start = microtime(true);
        log_message('info', "[IMPORT] Start file={$filePath} overwrite={$overwrite}");

        $spreadsheet = IOFactory::load($filePath);
        $sheetName = $this->resolveSheetName($spreadsheet);
        log_message('info', "[IMPORT] Resolved sheet={$sheetName}");

        $db = $this->db;
        $db->transStart();

        try {
            if ($overwrite) {
                log_message('info', "[IMPORT] TRUNCATE {$this->table}");
                $db->query("TRUNCATE TABLE {$this->table}");
            }

            // Preload all reference data
            $this->preloadAllRefs();

            $worksheet = $spreadsheet->getSheetByName($sheetName);
            $rows = $worksheet->toArray(null, true, true, true);

            $result = [
                'inserted' => 0,
                'updated' => 0,
                'skipped' => 0
            ];

            $headerIndex = [];
            $rowNum = 0;

            foreach ($rows as $row) {
                $rowNum++;

                // Parse header
                if ($rowNum === 1) {
                    $headerIndex = $this->buildHeaderIndex(array_values($row));
                    log_message('info', "[IMPORT] Header cols=" . count($headerIndex));
                    continue;
                }

                $employee = $this->parseEmployeeRow($headerIndex, array_values($row));

                if ($this->isEmptyRow($employee)) {
                    $result['skipped']++;
                    if ($verbose || ($rowNum % self::LOG_EVERY_ROWS === 0)) {
                        log_message('info', "[ROW {$rowNum}] Skipped (empty keys)");
                    }
                    continue;
                }

                // Find existing record
                [$existingId, $found] = $this->findExistingEmployee($employee);
                if ($found) {
                    $employee['id'] = $existingId;
                } elseif (empty($employee['id'])) {
                    $employee['id'] = $this->generateUUID();
                }

                // Lookup all foreign keys
                $employee = $this->lookupForeignKeys($employee);

                // Upsert record
                $isInsert = $this->upsertEmployee($employee);

                if ($isInsert) {
                    $result['inserted']++;
                } else {
                    $result['updated']++;
                }

                if ($verbose || ($rowNum % self::LOG_EVERY_ROWS === 0)) {
                    $action = $isInsert ? 'inserted' : 'updated';
                    log_message('info', "[ROW {$rowNum}] {$action} (id={$employee['id']}, nik={$employee['employee_number']})");
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaction failed');
            }

            $elapsed = round(microtime(true) - $start, 2);
            log_message('info', "[RESULT] inserted={$result['inserted']} updated={$result['updated']} skipped={$result['skipped']} elapsed={$elapsed}s");

            return $result;

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', "[IMPORT] ROLLBACK: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Preload all reference tables into memory
     */
    private function preloadAllRefs(): void
    {
        foreach (self::REF_TABLES as $key => $table) {
            $this->refs[$key] = $this->preloadRefTable($table);
        }
    }

    /**
     * Preload single reference table
     */
    private function preloadRefTable(string $table): array
    {
        $query = $this->db->table($table)->select('id, name')->get();
        
        $index = [
            'exact' => [],
            'list' => []
        ];

        foreach ($query->getResult() as $row) {
            $id = trim($row->id);
            $name = trim($row->name);
            $nameLower = mb_strtolower($name);

            if ($nameLower !== '') {
                $index['exact'][$nameLower] = $id;
                $index['list'][] = ['name' => $nameLower, 'id' => $id];
            }
        }

        return $index;
    }

    /**
     * Lookup foreign key by name (exact then contains match)
     */
    private function lookupRef(string $refKey, ?string $input): ?string
    {
        $s = trim($input ?? '');
        if ($s === '') {
            return null;
        }

        $si = mb_strtolower($s);
        $cacheKey = "{$refKey}|{$si}";

        // Check negative cache
        if (isset($this->negCache[$cacheKey])) {
            return null;
        }

        if (!isset($this->refs[$refKey])) {
            return null;
        }

        $ref = $this->refs[$refKey];

        // Exact match
        if (isset($ref['exact'][$si])) {
            return $ref['exact'][$si];
        }

        // Contains match (like ILIKE '%x%')
        foreach ($ref['list'] as $item) {
            if (strpos($item['name'], $si) !== false) {
                return $item['id'];
            }
        }

        // Cache miss
        $this->negCache[$cacheKey] = true;
        return null;
    }

    /**
     * Lookup all foreign keys for employee
     */
    private function lookupForeignKeys(array $employee): array
    {
        $employee['site_name'] = $this->lookupRef('sites', $employee['site_name']);
        $employee['gender'] = $this->lookupRef('genders', $employee['gender']);
        $employee['department'] = $this->lookupRef('departments', $employee['department']);
        $employee['religion'] = $this->lookupRef('religions', $employee['religion']);
        $employee['division'] = $this->lookupRef('divisions', $employee['division']);
        $employee['employment_status'] = $this->lookupRef('employment_statuses', $employee['employment_status']);
        $employee['group_name'] = $this->lookupRef('groups', $employee['group_name']);
        $employee['emergency_contact_relationship'] = $this->lookupRef('emergency_contact_relations', $employee['emergency_contact_relationship']);
        $employee['blood_type'] = $this->lookupRef('blood_types', $employee['blood_type']);
        $employee['marital_status'] = $this->lookupRef('marital_statuses', $employee['marital_status']);
        $employee['last_education'] = $this->lookupRef('last_educations', $employee['last_education']);
        $employee['employee_status'] = $this->lookupRef('employee_statuses', $employee['employee_status']);
        $employee['section'] = $this->lookupRef('sections', $employee['section']);

        return $employee;
    }

    /**
     * Find existing employee by unique fields
     */
    private function findExistingEmployee(array $employee): array
    {
        $empNum = $employee['employee_number'] ?? '';
        $natId = $employee['employee_national_id'] ?? '';
        $bisId = $employee['bis_id'] ?? '';

        // Try active records first (is_deleted = 0)
        $query = $this->db->table($this->table)
            ->select('id')
            ->where('is_deleted', 0)
            ->groupStart()
                ->where('employee_number', $empNum)
                ->orWhere('employee_national_id', $natId)
                ->orWhere('bis_id', $bisId)
            ->groupEnd()
            ->limit(1)
            ->get();

        $row = $query->getRow();
        if ($row) {
            return [trim($row->id), true];
        }

        // Fallback: any record
        $query = $this->db->table($this->table)
            ->select('id')
            ->groupStart()
                ->where('employee_number', $empNum)
                ->orWhere('employee_national_id', $natId)
                ->orWhere('bis_id', $bisId)
            ->groupEnd()
            ->limit(1)
            ->get();

        $row = $query->getRow();
        if ($row) {
            return [trim($row->id), true];
        }

        return [null, false];
    }

    /**
     * Upsert employee record
     */
    private function upsertEmployee(array $employee): bool
    {
        $now = date('Y-m-d H:i:s');
        
        $data = [
            'id' => $employee['id'],
            'bis_id' => $employee['bis_id'],
            'address' => $employee['address'],
            'birth_date' => $employee['birth_date'],
            'client' => $employee['client'],
            'contract_type' => $employee['contract_type'],
            'department' => $employee['department'],
            'emergency_number' => $employee['emergency_number'],
            'employee_name' => $employee['employee_name'],
            'employee_national_id' => $employee['employee_national_id'],
            'employee_number' => $employee['employee_number'],
            'employment_status' => $employee['employment_status'],
            'gender' => $employee['gender'],
            'job_level' => $employee['job_level'],
            'join_date' => $employee['join_date'],
            'last_education' => $employee['last_education'],
            'group_name' => $employee['group_name'],
            'group_level' => $employee['group_level'],
            'phone_number' => $employee['phone_number'],
            'place_of_birth' => $employee['place_of_birth'],
            'place_of_hire' => $employee['place_of_hire'],
            'section' => $employee['section'],
            'religion' => $employee['religion'],
            'site_name' => $employee['site_name'],
            'sub_job_level' => $employee['sub_job_level'] ?? null,
            'employment_status_remark' => $employee['employment_status_remark'] ?? '',
            'employee_status' => $employee['employee_status'],
            'division' => $employee['division'],
            'emergency_contact_name' => $employee['emergency_contact_name'],
            'blood_type' => $employee['blood_type'],
            'marital_status' => $employee['marital_status'],
            'emergency_contact_relationship' => $employee['emergency_contact_relationship'],
            'created_at' => $now,
            'updated_at' => $now,
            'is_deleted' => 0,
        ];

        // Build upsert query
        $builder = $this->db->table($this->table);
        
        // Check if exists
        $exists = $builder->where('id', $employee['id'])->countAllResults(false) > 0;

        if ($exists) {
            unset($data['created_at']); // Don't update created_at
            $builder->where('id', $employee['id'])->update($data);
            return false; // Update
        } else {
            $builder->insert($data);
            return true; // Insert
        }
    }

    /**
     * Parse employee row from Excel
     */
    private function parseEmployeeRow(array $headerIndex, array $row): array
    {
        $get = function($col) use ($headerIndex, $row) {
            if (!isset($headerIndex[$col])) {
                return '';
            }
            $i = $headerIndex[$col];
            return isset($row[$i]) ? $this->normalizeText($row[$i]) : '';
        };

        $employee = [
            'id' => '',
            'bis_id' => $get('BIS ID'),
            'address' => $get('Address'),
            'birth_date' => null,
            'client' => $get('Place of Hire'),
            'contract_type' => $get('Employment Status'),
            'department' => $get('Department'),
            'emergency_number' => $get('Emergency Number'),
            'employee_name' => $get('Nama'),
            'employee_national_id' => $get('National ID'),
            'employee_number' => $get('NIK'),
            'employment_status' => $get('Employment Status'),
            'gender' => $get('Gender'),
            'job_level' => $get('Golongan'),
            'join_date' => null,
            'last_education' => $get('Last Education'),
            'group_name' => $get('Golongan'),
            'group_level' => $this->parseInt16($get('Level Golongan')),
            'phone_number' => $get('Phone Number'),
            'place_of_birth' => $get('Place of Birth'),
            'place_of_hire' => $get('Place of Hire'),
            'section' => $get('Section'),
            'religion' => $get('Religion'),
            'site_name' => $get('Site Name'),
            'sub_job_level' => null,
            'employment_status_remark' => '',
            'employee_status' => $get('Employee Status'),
            'division' => $get('Division'),
            'emergency_contact_name' => $get('Emergency Contact Name'),
            'blood_type' => $get('Blood Type'),
            'marital_status' => $get('Marital Status'),
            'emergency_contact_relationship' => $get('Emergency Contact Relationship'),
        ];

        // Parse UUID if exists
        $idStr = $get('ID');
        if ($this->isValidUUID($idStr)) {
            $employee['id'] = trim($idStr);
        }

        // Parse dates
        $birthDate = $get('Birth Date');
        if ($birthDate) {
            $employee['birth_date'] = $this->parseExcelDate($birthDate);
        }

        $joinDate = $get('Join Date');
        if ($joinDate) {
            $employee['join_date'] = $this->parseExcelDate($joinDate);
        }

        return $employee;
    }

    /**
     * Resolve sheet name from workbook
     */
    private function resolveSheetName($spreadsheet): string
    {
        $sheets = $spreadsheet->getSheetNames();
        
        if (empty($sheets)) {
            throw new \RuntimeException('Workbook has no sheets');
        }

        foreach ($sheets as $sheet) {
            if (strcasecmp($sheet, self::DEFAULT_SHEET) === 0) {
                return $sheet;
            }
        }

        return $sheets[0];
    }

    /**
     * Build header column index
     */
    private function buildHeaderIndex(array $header): array
    {
        $index = [];
        foreach ($header as $i => $h) {
            $h = trim($h);
            if ($h !== '' && strpos($h, 'Unnamed') !== 0) {
                $index[$h] = $i;
            }
        }
        return $index;
    }

    /**
     * Normalize text from Excel cell
     */
    private function normalizeText(?string $s): string
    {
        $s = trim($s ?? '');
        if ($s === '') {
            return $s;
        }
        
        $s = str_replace(' ', ' ', $s); // Non-breaking space
        $s = preg_replace('/\s+/', ' ', $s);
        return $s;
    }

    /**
     * Check if employee row is empty
     */
    private function isEmptyRow(array $e): bool
    {
        return trim($e['employee_number'] ?? '') === '' &&
               trim($e['employee_national_id'] ?? '') === '' &&
               trim($e['employee_name'] ?? '') === '' &&
               trim($e['bis_id'] ?? '') === '';
    }

    /**
     * Parse integer from string
     */
    private function parseInt16(?string $s): ?int
    {
        $s = trim($s ?? '');
        if ($s === '') {
            return null;
        }
        
        $n = filter_var($s, FILTER_VALIDATE_INT);
        return $n !== false ? $n : null;
    }

    /**
     * Parse Excel date to MySQL date format
     */
    private function parseExcelDate(?string $v): ?string
    {
        $v = trim($v ?? '');
        if ($v === '') {
            return null;
        }

        // Excel serial number
        if (is_numeric($v)) {
            $unixDate = ($v - 25569) * 86400;
            return date('Y-m-d', $unixDate);
        }

        // Try various formats
        $formats = ['d/m/Y', 'm/d/Y', 'Y-m-d', 'd-m-Y', 'm-d-Y', 'd/m/y', 'm/d/y'];
        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $v);
            if ($date !== false) {
                return $date->format('Y-m-d');
            }
        }

        return null;
    }

    /**
     * Generate UUID v4
     */
    private function generateUUID(): string
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * Validate UUID format
     */
    private function isValidUUID(?string $uuid): bool
    {
        if ($uuid === null) {
            return false;
        }
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', trim($uuid)) === 1;
    }
}