<?php

namespace App\Models;

use CodeIgniter\Model;

class FasilityModel extends Model
{
    protected $table = 'fasilities';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name'];
}