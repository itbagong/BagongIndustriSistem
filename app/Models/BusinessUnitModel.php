<?php

Namespace App\Models;
use CodeIgniter\Model;

class BusinessUnitModel extends Model{
    protected $table = 'business_units';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id', 'name', 'code', 'description', 'created_at', 'updated_at', 'is_deleted'];
}