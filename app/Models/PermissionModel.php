<?php

namespace App\Models;

use CodeIgniter\Model;

class PermissionModel extends Model
{
    protected $table = 'permissions';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'name',
        'display_name',
        'description',
        'module',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps = false;

    /**
     * Ambil permission berdasarkan user_id (lokal)
     * Return: array of string permission name
     */
    public function getPermissionsByUserId(int $userId): array
    {
        $db = \Config\Database::connect();

        $rows = $db->table('users u')
            ->select('p.name')
            ->join('roles r', 'r.id = u.role_id')
            ->join('role_permissions rp', 'rp.role_id = r.id')
            ->join('permissions p', 'p.id = rp.permission_id')
            ->where('u.id', $userId)
            ->where('u.is_active', 1)
            ->where('r.is_active', 1)
            ->get()
            ->getResultArray();

        return array_values(array_unique(array_map(
            fn ($row) => $row['name'],
            $rows
        )));
    }

    /**
     * Ambil permission berdasarkan role_id (opsional)
     */
    public function getPermissionsByRoleId(int $roleId): array
    {
        $db = \Config\Database::connect();

        $rows = $db->table('role_permissions rp')
            ->select('p.name')
            ->join('permissions p', 'p.id = rp.permission_id')
            ->where('rp.role_id', $roleId)
            ->get()
            ->getResultArray();

        return array_values(array_unique(array_map(
            fn ($row) => $row['name'],
            $rows
        )));
    }
}
