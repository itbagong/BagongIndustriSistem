<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployeeModel extends Model
{
    protected $DBGroup = 'pg';
    protected $table = 'employees_recruitment';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'bis_id',
        'employee_number',
        'employee_name',
        'employee_national_id',
        'gender',
        'department',
        'division',
        'job_level',
        'sub_job_level',
        'group_level',
        'employment_status',
        'employee_status',
        'join_date',
        'site_name',
        'place_of_hire',
        'phone_number',
        'place_of_birth',
        'birth_date',
        'address',
        'employment_status_remark',
        'emergency_contact_name',
        'emergency_number',
        'blood_type',
        'marital_status',
        'religion'
    ];

    /* =====================================================
     * LIST + FILTER + PAGINATION
     * ===================================================== */
    public function getFiltered(array $filters = []): array
    {
        $builder = $this->db->table('employees_recruitment e')
            ->select([
                'e.id',
                'e.employee_number',
                'e.employee_name',
                'g.name AS gender',
                'd.name AS department',
                'dv.name AS division',
                'e.job_level',
                'e.group_level',
                'es.name AS employment_status',
                'est.name AS employee_status',
                "to_char(e.join_date,'YYYY-MM-DD') AS join_date",
                'e.site_name'
            ])
            ->join('genders g', 'g.id = e.gender', 'left')
            ->join('departments d', 'd.id = e.department', 'left')
            ->join('divisions dv', 'dv.id = e.division', 'left')
            ->join('employment_statuses es', 'es.id = e.employment_status', 'left')
            ->join('employee_statuses est', 'est.id = e.employee_status', 'left');

        /* ---------- SEARCH ---------- */
        if (!empty($filters['search'])) {
            $q = strtolower(trim($filters['search']));
            $builder->groupStart()
                ->like('LOWER(e.employee_name)', $q)
                ->orLike('LOWER(e.employee_number)', $q)
                ->orLike('LOWER(e.bis_id)', $q)
                ->groupEnd();
        }

        /* ---------- FILTER ---------- */
        if (!empty($filters['department'])) {
            $builder->where('e.department', $filters['department']);
        }

        if (!empty($filters['employment_status'])) {
            $builder->where('e.employment_status', $filters['employment_status']);
        }

        if (!empty($filters['employee_status'])) {
            $builder->where('e.employee_status', $filters['employee_status']);
        }

        /* ---------- COUNT ---------- */
        $total = $builder->countAllResults(false);

        /* ---------- PAGINATION ---------- */
        $page    = (int)($filters['page'] ?? 1);
        $perPage = (int)($filters['per_page'] ?? 10);
        $offset  = ($page - 1) * $perPage;

        $data = $builder
            ->orderBy('e.employee_name', 'ASC')
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        return [
            'data' => $data,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => (int) ceil($total / max(1, $perPage))
            ]
        ];
    }

    /* =====================================================
     * DETAIL
     * ===================================================== */
    public function getDetail(string $id): ?array
    {
        return $this->db->table('employees_recruitment e')
            ->select([
                'e.*',
                'd.name AS department',
                'dv.name AS division',
                'es.name AS employment_status',
                'est.name AS employee_status'
            ])
            ->join('departments d', 'd.id = e.department', 'left')
            ->join('divisions dv', 'dv.id = e.division', 'left')
            ->join('employment_statuses es', 'es.id = e.employment_status', 'left')
            ->join('employee_statuses est', 'est.id = e.employee_status', 'left')
            ->where('e.id', $id)
            ->get()
            ->getRowArray();
    }

    /* =====================================================
     * STATISTICS
     * ===================================================== */
    public function getStatistics(): array
    {
        $total = (int) $this->countAllResults();

        $active = (int) ($this->db->query("
            SELECT COUNT(*) cnt
            FROM employees_recruitment e
            JOIN employee_statuses es ON es.id = e.employee_status
            WHERE UPPER(es.name) LIKE '%ACTIVE%'
        ")->getRow()->cnt ?? 0);

        $newThisMonth = (int) ($this->db->query("
            SELECT COUNT(*) cnt
            FROM employees_recruitment
            WHERE to_char(join_date,'YYYY-MM') = to_char(NOW(),'YYYY-MM')
        ")->getRow()->cnt ?? 0);

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => max(0, $total - $active),
            'new_this_month' => $newThisMonth
        ];
    }
}
