<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployeeRecruitmentModel extends Model
{
    protected $table = 'employees_recruitment';
    protected $primaryKey = 'id';
    protected $allowedFields = ['employee_name', 'employee_number'];
    
    // ✅ Set koneksi ke PostgreSQL
    protected $DBGroup = 'pg';
    
    public function searchEmployee($keyword)
    {
        return $this->select('employee_name, employee_number')
                    ->groupStart()
                        ->like('LOWER(employee_name)', strtolower($keyword))
                        ->orLike('LOWER(employee_number)', strtolower($keyword))
                    ->groupEnd()
                    ->limit(20)
                    ->findAll();
    }
}