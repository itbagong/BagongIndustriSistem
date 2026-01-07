<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'username', 'email', 'password', 'role_id', 'employee_id',
        'is_active', 'last_login', 'login_attempts', 'locked_until',
        'remember_token', 'email_verified_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    /**
     * Hash password before insert/update
     */
    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        }
        return $data;
    }

    /**
     * Get user by username or email
     */
    public function getUserByUsernameOrEmail(string $username)
    {
        return $this->where('username', $username)
                    ->orWhere('email', $username)
                    ->first();
    }

    /**
     * Get user with role and permissions
     */
    public function getUserRoleAndPermissions(int $userId)
    {
        $db = \Config\Database::connect();
        
        // Get role
        $role = $db->table('users')
            ->select('roles.id as role_id, roles.name as role_name, roles.level as role_level')
            ->join('roles', 'roles.id = users.role_id')
            ->where('users.id', $userId)
            ->get()
            ->getRowArray();

        if (!$role) {
            return null;
        }

        // Get permissions
        $permissions = $db->table('role_permissions')
            ->select('permissions.name')
            ->join('permissions', 'permissions.id = role_permissions.permission_id')
            ->where('role_permissions.role_id', $role['role_id'])
            ->get()
            ->getResultArray();

        return [
            'role_name' => $role['role_name'],
            'role_level' => $role['role_level'],
            'permissions' => array_column($permissions, 'name')
        ];
    }

    /**
     * Get employee data linked to user
     */
    public function getEmployeeData(int $employeeId)
    {
        $db = \Config\Database::connect();
        
        return $db->table('employees')
            ->select('employees.*, departments.name as department_name, positions.name as position_name')
            ->join('departments', 'departments.id = employees.department_id', 'left')
            ->join('positions', 'positions.id = employees.position_id', 'left')
            ->where('employees.id', $employeeId)
            ->get()
            ->getRowArray();
    }
}