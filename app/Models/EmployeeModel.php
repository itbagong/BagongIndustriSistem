<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployeeModel extends Model
{
    protected $table = 'employees';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'nik', 'nama', 'gender', 'department_id', 'division_id', 'position_id',
        'golongan', 'employment_type_id', 'employee_status', 'tanggal_pkwt',
        'tanggal_resign', 'tanggal_join', 'tanggal_permanent', 'national_id',
        'phone_number', 'email_personal', 'place_of_birth', 'birth_date',
        'religion', 'marital_status', 'blood_type', 'last_education',
        'education_major', 'address_ktp', 'address_domicile', 'city',
        'province', 'postal_code', 'site_id', 'place_of_hire',
        'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relation',
        'bank_name', 'bank_account_number', 'bank_account_name',
        'bpjs_kesehatan', 'bpjs_ketenagakerjaan', 'npwp', 'photo_url',
        'notes', 'created_by', 'updated_by'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    /**
     * Get filtered employees with joins
     */
    public function getFilteredWithJoins($filters = [])
    {
        $builder = $this->db->table($this->table)
            ->select('employees.*, 
                     departments.name as department_name,
                     divisions.name as division_name,
                     positions.name as position_name,
                     employment_types.name as employment_type_name,
                     sites.name as site_name')
            ->join('departments', 'departments.id = employees.department_id', 'left')
            ->join('divisions', 'divisions.id = employees.division_id', 'left')
            ->join('positions', 'positions.id = employees.position_id', 'left')
            ->join('employment_types', 'employment_types.id = employees.employment_type_id', 'left')
            ->join('sites', 'sites.id = employees.site_id', 'left')
            ->where('employees.deleted_at', null);

        // Search
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $builder->groupStart()
                    ->like('employees.nik', $search)
                    ->orLike('employees.nama', $search)
                    ->orLike('positions.name', $search)
                    ->orLike('departments.name', $search)
                    ->groupEnd();
        }

        // Filters
        if (!empty($filters['department'])) {
            $builder->where('employees.department_id', $filters['department']);
        }

        if (!empty($filters['employment_status'])) {
            $builder->where('employees.employment_type_id', $filters['employment_status']);
        }

        if (!empty($filters['employee_status'])) {
            $builder->where('employees.employee_status', $filters['employee_status']);
        }

        $total = $builder->countAllResults(false);

        // Pagination
        $perPage = $filters['per_page'] ?? 10;
        $page = $filters['page'] ?? 1;
        $offset = ($page - 1) * $perPage;

        $data = $builder->limit($perPage, $offset)->get()->getResultArray();

        return [
            'data' => $data,
            'total' => $total
        ];
    }

    /**
     * Get employee detail with all relations
     */
    public function getEmployeeDetailWithRelations($id)
    {
        return $this->db->table($this->table)
            ->select('employees.*, 
                     departments.name as department_name,
                     divisions.name as division_name,
                     positions.name as position_name, positions.level as position_level,
                     employment_types.name as employment_type_name,
                     sites.name as site_name, sites.type as site_type,
                     creator.username as created_by_name,
                     updater.username as updated_by_name')
            ->join('departments', 'departments.id = employees.department_id', 'left')
            ->join('divisions', 'divisions.id = employees.division_id', 'left')
            ->join('positions', 'positions.id = employees.position_id', 'left')
            ->join('employment_types', 'employment_types.id = employees.employment_type_id', 'left')
            ->join('sites', 'sites.id = employees.site_id', 'left')
            ->join('users as creator', 'creator.id = employees.created_by', 'left')
            ->join('users as updater', 'updater.id = employees.updated_by', 'left')
            ->where('employees.id', $id)
            ->where('employees.deleted_at', null)
            ->get()
            ->getRowArray();
    }

    /**
     * Get statistics
     */
    public function getStatistics()
    {
        $total = $this->countAllResults(false);
        $active = $this->where('employee_status', 'Active')->countAllResults(false);
        $inactive = $this->where('employee_status', 'Inactive')->countAllResults(false);
        
        $firstDayOfMonth = date('Y-m-01');
        $newThisMonth = $this->where('tanggal_join >=', $firstDayOfMonth)
                             ->countAllResults();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'new_this_month' => $newThisMonth
        ];
    }

    /**
     * Export data with filters
     */
    public function exportData($filters = [])
    {
        $builder = $this->db->table($this->table)
            ->select('employees.*, 
                     departments.name as department_name,
                     divisions.name as division_name,
                     positions.name as position_name,
                     employment_types.name as employment_type_name,
                     sites.name as site_name')
            ->join('departments', 'departments.id = employees.department_id', 'left')
            ->join('divisions', 'divisions.id = employees.division_id', 'left')
            ->join('positions', 'positions.id = employees.position_id', 'left')
            ->join('employment_types', 'employment_types.id = employees.employment_type_id', 'left')
            ->join('sites', 'sites.id = employees.site_id', 'left')
            ->where('employees.deleted_at', null);

        if (!empty($filters['search'])) {
            $builder->like('employees.nama', $filters['search']);
        }

        if (!empty($filters['department'])) {
            $builder->where('employees.department_id', $filters['department']);
        }

        if (!empty($filters['employee_status'])) {
            $builder->where('employees.employee_status', $filters['employee_status']);
        }

        return $builder->get()->getResultArray();
    }
}