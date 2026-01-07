<?php

namespace App\Models;

use CodeIgniter\Model;

class EmploymentTypeModel extends Model
{
    protected $table = 'employment_types';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['code', 'name', 'description', 'contract_duration_months', 'is_active'];
    protected $useTimestamps = true;
}