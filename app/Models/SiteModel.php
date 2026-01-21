<?php

namespace App\Models;

use CodeIgniter\Model;

class SiteModel extends Model
{
    protected $table = 'sites';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['code', 'name', 'business_unit_id', 'address', 'city', 'province', 'postal_code', 'phone', 'manager_id', 'is_active'];
    protected $useTimestamps = true;
}