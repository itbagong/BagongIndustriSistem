<?php

namespace App\Models;

use CodeIgniter\Model;

class UserPermissionModel extends Model
{
    protected $table = 'user_permissions';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'permission_id', 'created_at'];
    protected $useTimestamps = false;

    /**
     * Get permissions khusus untuk user tertentu
     */
    public function getPermissionsByUserId(int $userId): array
    {
        return $this->db->table($this->table . ' up')
            ->select('p.id, p.name, p.description')
            ->join('permissions p', 'up.permission_id = p.id')
            ->where('up.user_id', $userId)
            ->get()
            ->getResultArray();
    }

    /**
     * Check apakah user punya permission khusus tertentu
     */
    public function hasPermission(int $userId, string $permissionName): bool
    {
        $result = $this->db->table($this->table . ' up')
            ->join('permissions p', 'up.permission_id = p.id')
            ->where('up.user_id', $userId)
            ->where('p.name', $permissionName)
            ->countAllResults();

        return $result > 0;
    }

    /**
     * Tambah permission khusus untuk user
     */
    public function addPermission(int $userId, int $permissionId): bool
    {
        // Cek apakah sudah ada
        $exists = $this->where('user_id', $userId)
            ->where('permission_id', $permissionId)
            ->first();

        if ($exists) {
            return true; // Sudah ada, anggap berhasil
        }

        return $this->insert([
            'user_id' => $userId,
            'permission_id' => $permissionId,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Hapus permission khusus user
     */
    public function removePermission(int $userId, int $permissionId): bool
    {
        return $this->where('user_id', $userId)
            ->where('permission_id', $permissionId)
            ->delete();
    }

    /**
     * Hapus semua permissions khusus user
     */
    public function clearUserPermissions(int $userId): bool
    {
        return $this->where('user_id', $userId)->delete();
    }
}