<?php

namespace App\Models;

use CodeIgniter\Model;

class UserSessionModel extends Model
{
    protected $table = 'user_sessions';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'user_id',
        'session_token',
        'ip_address',
        'user_agent',
        'last_activity',
        'created_at'
    ];

    protected $useTimestamps = false;   // ⛔ matikan auto timestamps
}