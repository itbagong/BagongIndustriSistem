<?php

namespace App\Models;

use CodeIgniter\Model;
use DateTime;

class EmployeeModel extends Model
{
    protected $DBGroup          = 'pg';
    protected $table            = 'employees';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = false;  // UUID primary key
    protected $useSoftDeletes   = false;  // handled manually via is_deleted

    protected $allowedFields = [
        'id', 'nik', 'bis_id', 'name',
        'gender_id', 'department_id', 'division_id', 'job_position_id',
        'work_user', 'site_id', 'employee_status_id', 'employment_status_id',
        'pkwt_date', 'cutoff_date',
        'national_id', 'phone_number', 'place_of_birth', 'date_of_birth',
        'last_education_id', 'religion_id', 'address',
        'is_deleted',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // ── Column map: DataTables index → ORDER BY expression ───────
    // Matches the exact <th> order in karyawan/index.php
    public array $columnMap = [
        1  => 'e.nik',
        2  => 'e.name',
        3  => 'g.name',    // gender
        4  => 'd.name',    // department
        5  => 'dv.name',   // division
        6  => 'e.work_user',
        7  => 'gp.name',   // job_position  (groups table)
        8  => 'e.pkwt_date',
        9  => 'e.pkwt_date', // tenure is computed; sort by pkwt_date as proxy
        10 => 'es.name',   // employee_status
        11 => 'ems.name',  // employment_status
        12 => 'e.cutoff_date',
        13 => 'e.national_id',
        14 => 'e.phone_number',
        15 => 'e.place_of_birth',
        16 => 'e.date_of_birth', // age computed; sort by DOB as proxy
        17 => 'le.name',   // last_education
        18 => 's.name',    // site
        19 => 'e.address',
        20 => 'r.name',    // religion
    ];

    // Columns used in global LIKE search
    private array $searchable = [
        'e.nik', 'e.name', 'e.bis_id', 'e.national_id',
        'e.phone_number', 'e.place_of_birth', 'e.address',
        'g.name', 'd.name', 'dv.name', 'gp.name',
        's.name', 'es.name', 'ems.name', 'le.name', 'r.name',
        'e.work_user',
    ];

    // ── Base builder: employees + all LEFT JOINs ─────────────────
    private function baseQuery(): \CodeIgniter\Database\BaseBuilder
    {
        return $this->db->table('employees e')
            ->select([
                'e.id',
                'e.nik',
                'e.bis_id',
                'e.name',
                'e.work_user',
                'e.pkwt_date',
                'e.cutoff_date',
                'e.national_id',
                'e.phone_number',
                'e.place_of_birth',
                'e.date_of_birth',
                'e.address',
                'e.created_at',
                // FK resolved labels
                'g.name   AS gender',
                'd.name   AS department',
                'dv.name  AS division',
                'gp.name  AS job_position',
                's.name   AS site',
                'es.name  AS employee_status',
                'ems.name AS employment_status',
                'le.name  AS last_education',
                'r.name   AS religion',
            ])
            ->join('genders g',               'g.id   = e.gender_id',              'left')
            ->join('departments d',           'd.id   = e.department_id',          'left')
            ->join('divisions dv',            'dv.id  = e.division_id',            'left')
            ->join('groups gp',               'gp.id  = e.job_position_id',        'left')
            ->join('sites s',                 's.id   = e.site_id',                'left')
            ->join('employee_statuses es',    'es.id  = e.employee_status_id',     'left')
            ->join('employment_statuses ems', 'ems.id = e.employment_status_id',   'left')
            ->join('last_educations le',      'le.id  = e.last_education_id',      'left')
            ->join('religions r',             'r.id   = e.religion_id',            'left')
            ->where('e.is_deleted', false);
    }

    // ── Apply search + filters on top of base query ───────────────
    private function applyFilters(
        \CodeIgniter\Database\BaseBuilder $builder,
        string $search,
        array  $filters
    ): \CodeIgniter\Database\BaseBuilder {

        // Dropdown exact-match filters (matched against resolved label columns)
        if (! empty($filters['department']))        $builder->where('d.name',   $filters['department']);
        if (! empty($filters['division']))          $builder->where('dv.name',  $filters['division']);
        if (! empty($filters['employment_status'])) $builder->where('ems.name', $filters['employment_status']);
        if (! empty($filters['employee_status']))   $builder->where('es.name',  $filters['employee_status']);

        // Global search (ILIKE)
        if ($search !== '') {
            $search = strtolower($search);
            $builder->groupStart();
            foreach ($this->searchable as $i => $col) {
                if ($i === 0) {
                    $builder->where("LOWER($col) LIKE", "%$search%");
                } else {
                    $builder->orWhere("LOWER($col) LIKE", "%$search%");
                }
            }
            $builder->groupEnd();
        }

        return $builder;
    }

    // ── Public API ───────────────────────────────────────────────

    public function countAll(): int
    {
        return $this->db->table('employees')
                        ->where('is_deleted', false)
                        ->countAllResults();
    }

    public function countFiltered(string $search, array $filters): int
    {
        $builder = $this->applyFilters($this->baseQuery(), $search, $filters);
        return $builder->countAllResults(false);
    }

    public function getFiltered(
        string $search,
        array  $filters,
        int    $start,
        int    $length,
        string $orderColumn = 'e.name',
        string $orderDir    = 'asc'
    ): array {
        $allowed     = array_values($this->columnMap);
        $orderColumn = in_array($orderColumn, $allowed) ? $orderColumn : 'e.name';
        $orderDir    = strtolower($orderDir) === 'desc' ? 'DESC' : 'ASC';

        $builder = $this->applyFilters($this->baseQuery(), $search, $filters);

        $rows = $builder
            ->orderBy($orderColumn, $orderDir)
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        return array_map([$this, 'enrichRow'], $rows);
    }

    /** Single record by UUID, with all joins applied. */
    public function findEmployee(string $id): ?array
    {
        $row = $this->baseQuery()
                    ->where('e.id', $id)
                    ->get()
                    ->getRowArray();

        return $row ? $this->enrichRow($row) : null;
    }

    /** Soft-delete: flip is_deleted flag instead of removing the row. */
    public function softDelete(string $id): bool
    {
        return $this->db->table('employees')
                        ->where('id', $id)
                        ->update([
                            'is_deleted' => true,
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
    }

    /** Generate a v4 UUID for new records. */
    public function generateUuid(): string
    {
        // Fallback: RFC-4122 v4 UUID
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    // ── Stats cards ──────────────────────────────────────────────

    public function getStats(): array
    {
        $db = $this->db;

        $total = $db->table('employees')
                    ->where('is_deleted', false)
                    ->countAllResults();

        $aktif = $db->table('employees e')
                    ->join('employment_statuses ems', 'ems.id = e.employment_status_id', 'left')
                    ->where('e.is_deleted', false)
                    ->where('ems.name', 'Aktif')
                    ->countAllResults();

        $nonaktif = $total - $aktif;

        $bulanIni = $db->table('employees')
                       ->where('is_deleted', false)
                       ->where('created_at >=', date('Y-m-01 00:00:00'))
                       ->where('created_at <=', date('Y-m-t 23:59:59'))
                       ->countAllResults();

        return compact('total', 'aktif', 'nonaktif', 'bulanIni');
    }

    // ── Dropdown helpers ─────────────────────────────────────────

    /**
     * Get distinct label values from a lookup table for filter <select> options.
     *
     * @param string $table      e.g. 'departments', 'divisions', 'sites'
     * @param string $labelCol   column holding the display name (default 'name')
     */
    public function getDistinct(string $table, string $labelCol = 'name'): array
    {
        $allowed = [
            'departments', 'divisions', 'genders', 'sites',
            'employee_statuses', 'employment_statuses',
            'last_educations', 'religions', 'groups',
        ];

        if (! in_array($table, $allowed)) return [];

        return array_column(
            $this->db->table($table)
                     ->select($labelCol)
                     ->orderBy($labelCol, 'ASC')
                     ->get()
                     ->getResultArray(),
            $labelCol
        );
    }

    // ── Computed / virtual fields ────────────────────────────────

    /**
     * Tenure: pkwt_date → cutoff_date (or today).
     * Returns e.g. "3 thn 2 bln 5 hr" or "-".
     */
    public function computeTenure(?string $pkwtDate, ?string $cutoffDate, ?string $employmentStatus = null): string
    {
        if (empty($pkwtDate)) return '-';

        // If no cutoff date, only compute using today for active employees
        // Withdrawn/inactive without a cutoff date → show '-'
        if (empty($cutoffDate)) {
            $active = ['aktif', 'active', 'pkwt', 'pkwtt', 'pks'];
            $status = strtolower(trim($employmentStatus ?? ''));
            $isActive = array_filter($active, fn($s) => str_contains($status, $s));

            if (!$isActive) return '-';
        }

        try {
            $start = new DateTime($pkwtDate);
            $end   = !empty($cutoffDate) ? new DateTime($cutoffDate) : new DateTime();
            if ($end < $start) return '-';

            $diff  = $start->diff($end);
            $parts = [];
            if ($diff->y > 0) $parts[] = $diff->y . ' thn';
            if ($diff->m > 0) $parts[] = $diff->m . ' bln';
            if ($diff->d > 0) $parts[] = $diff->d . ' hr';

            return $parts ? implode(' ', $parts) : '< 1 hr';
        } catch (\Exception) {
            return '-';
        }
    }

    /**
     * Age in full years from date_of_birth to today.
     */
    public function computeAge(?string $dateOfBirth): ?int
    {
        if (empty($dateOfBirth)) return null;
        try {
            return (new DateTime($dateOfBirth))->diff(new DateTime())->y;
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Attach all computed fields to a single DB row.
     * Add any future virtual columns here.
     */
    public function enrichRow(array $row): array
    {
        $row['tenure'] = $this->computeTenure(
            $row['pkwt_date']        ?? null,
            $row['cutoff_date']      ?? null,
            $row['employment_status'] ?? null   // ← pass status
        );
        $row['age'] = $this->computeAge($row['date_of_birth'] ?? null);
        return $row;
    }
}