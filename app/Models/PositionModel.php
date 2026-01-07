<?php

namespace App\Models;
use CodeIgniter\Model;

class PositionModel extends Model
{
    protected $table = 'positions';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['code', 'name', 'level', 'description', 'min_salary', 'max_salary', 'is_active'];
    protected $useTimestamps = true;
}