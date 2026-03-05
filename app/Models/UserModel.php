<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'username',
        'email',
        'password',
        'role_id',
        'employee_id',
        'is_active',
        'last_login',
        'login_attempts',
        'locked_until',
        'remember_token',
        'email_verified_at',
        'created_at',
        'updated_at',
        'deleted_at',
        'api_user_id',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    // ── Callback: hash password ───────────────────────────────
    protected function hashPassword(array $data): array
    {
        if (!empty($data['data']['password'])) {
            $data['data']['password'] = password_hash(
                $data['data']['password'],
                PASSWORD_DEFAULT
            );
        } else {
            // Jika password kosong saat update, jangan overwrite
            unset($data['data']['password']);
        }
        return $data;
    }

    // ── Existing methods (tidak diubah) ───────────────────────

    public function getUserByUsernameOrEmail(string $username)
    {
        return $this->where('username', $username)
                    ->orWhere('email', $username)
                    ->first();
    }

    public function getUserRoleAndPermissions(int $userId)
    {
        $db = \Config\Database::connect();

        $role = $db->table('users')
            ->select('roles.id as role_id, roles.name as role_name, roles.level as role_level')
            ->join('roles', 'roles.id = users.role_id')
            ->where('users.id', $userId)
            ->get()
            ->getRowArray();

        if (!$role) return null;

        $permissions = $db->table('role_permissions')
            ->select('permissions.name')
            ->join('permissions', 'permissions.id = role_permissions.permission_id')
            ->where('role_permissions.role_id', $role['role_id'])
            ->get()
            ->getResultArray();

        return [
            'role_name'   => $role['role_name'],
            'role_level'  => $role['role_level'],
            'permissions' => array_column($permissions, 'name'),
        ];
    }

    public function getEmployeeData(int $employeeId)
    {
        $db = \Config\Database::connect();

        return $db->table('employees')
            ->select('employees.*, departments.name as department_name, positions.name as position_name')
            ->join('departments', 'departments.id = employees.department_id', 'left')
            ->join('positions',   'positions.id = employees.position_id',     'left')
            ->where('employees.id', $employeeId)
            ->get()
            ->getRowArray();
    }

    // ── NEW: List user untuk manajemen user ───────────────────

    /**
     * Ambil list user + nama role + nama karyawan, dengan filter
     */
    public function getList(array $filters = [], int $perPage = 10, int $page = 1): array
    {
        return $this->buildListQuery($filters)
            ->orderBy('u.created_at', 'DESC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()
            ->getResultArray();
    }

    /**
     * Hitung total user sesuai filter
     */
    public function countList(array $filters = []): int
    {
        return $this->buildListQuery($filters)->countAllResults();
    }

    /**
     * Builder query list (dipakai getList & countList)
     */
    private function buildListQuery(array $filters): \CodeIgniter\Database\BaseBuilder
    {
        $builder = $this->db->table('users u')
            ->select('u.*, r.name AS role_name, e.nama AS employee_name')
            ->join('roles r',     'r.id = u.role_id',     'left')
            ->join('employees e', 'e.id = u.employee_id', 'left')
            ->where('u.deleted_at IS NULL');

        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('u.username', $filters['search'])
                ->orLike('u.email',  $filters['search'])
            ->groupEnd();
        }

        if (!empty($filters['role_id'])) {
            $builder->where('u.role_id', $filters['role_id']);
        }

        if ($filters['is_active'] !== null && $filters['is_active'] !== '') {
            $builder->where('u.is_active', $filters['is_active']);
        }

        return $builder;
    }
}