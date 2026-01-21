<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table      = 'roles';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'name',
        'display_name',
        'description',
        'level',
        'is_active'
    ];

    /**
     * Ambil role user (join user_roles)
     */
    public function getUserRole($userId)
    {
        if (!$userId) {
            return null;
        }

        return $this->db->table('user_roles ur')
            ->select('r.id, r.name, r.display_name, r.level')
            ->join('roles r', 'r.id = ur.role_id', 'left')
            ->where('ur.user_id', $userId)
            ->where('r.is_active', 1)
            ->get()
            ->getRowArray();
    }
}
