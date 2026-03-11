<?php

namespace App\Models;

use CodeIgniter\Model;

class PermissionModel extends Model
{
    protected $table      = 'permissions';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'name', 'display_name', 'description', 'module', 'created_at', 'updated_at',
    ];
    protected $useTimestamps = false;

    // =========================================================
    // EXISTING METHODS
    // =========================================================

    public function getPermissionsByUserId(int $userId): array
    {
        $db = \Config\Database::connect();

        // 1. Permissions dari Role
        $fromRole = $db->table('users u')
            ->select('p.name')
            ->join('roles r',             'r.id = u.role_id')
            ->join('role_permissions rp', 'rp.role_id = r.id')
            ->join('permissions p',       'p.id = rp.permission_id')
            ->where('u.id', $userId)
            ->where('u.is_active', 1)
            ->where('r.is_active', 1)
            ->get()->getResultArray();

        // 2. Permissions khusus dari user_permissions
        $fromUser = $db->table('user_permissions up')
            ->select('p.name')
            ->join('permissions p', 'p.id = up.permission_id')
            ->where('up.user_id', $userId)
            ->get()->getResultArray();

        // 3. Gabungkan & hapus duplikat
        $all = array_merge(
            array_column($fromRole, 'name'),
            array_column($fromUser, 'name')
        );

        return array_values(array_unique($all));
    }

    public function getPermissionsByRoleId(int $roleId): array
    {
        $db = \Config\Database::connect();
        $rows = $db->table('role_permissions rp')
            ->select('p.name')
            ->join('permissions p', 'p.id = rp.permission_id')
            ->where('rp.role_id', $roleId)
            ->get()->getResultArray();

        return array_values(array_unique(array_map(fn($row) => $row['name'], $rows)));
    }

    // =========================================================
    // CRUD METHODS
    // =========================================================

    public function getList(array $filters = [], int $perPage = 10, int $page = 1): array
    {
        $builder = $this->builder();

        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('name',           $filters['search'])
                ->orLike('display_name', $filters['search'])
                ->orLike('module',       $filters['search'])
                ->groupEnd();
        }

        if (!empty($filters['module'])) {
            $builder->where('module', $filters['module']);
        }

        $offset = ($page - 1) * $perPage;

        return $builder->orderBy('module', 'ASC')
                       ->orderBy('name',   'ASC')
                       ->limit($perPage, $offset)
                       ->get()->getResultArray();
    }

    public function countList(array $filters = []): int
    {
        $builder = $this->builder();

        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('name',           $filters['search'])
                ->orLike('display_name', $filters['search'])
                ->orLike('module',       $filters['search'])
                ->groupEnd();
        }

        if (!empty($filters['module'])) {
            $builder->where('module', $filters['module']);
        }

        return $builder->countAllResults();
    }

    public function getModules(): array
    {
        return $this->db->table('permissions')
                        ->select('module')
                        ->distinct()
                        ->orderBy('module', 'ASC')
                        ->get()->getResultArray();
    }
}