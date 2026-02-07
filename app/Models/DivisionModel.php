<?php

namespace App\Models;
use CodeIgniter\Model;

class DivisionModel extends Model
{
    protected $DBGroup = 'pg';
    protected $table = 'divisions';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['department_id', 'code', 'name', 'description', 'head_id', 'is_active'];
    protected $useTimestamps = true;
}