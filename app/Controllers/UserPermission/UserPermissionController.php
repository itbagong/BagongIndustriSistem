<?php

namespace App\Controllers\UserPermission;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\PermissionModel;
use App\Models\UserPermissionModel;

class UserPermissionController extends BaseController
{
    protected $userModel;
    protected $permissionModel;
    protected $userPermissionModel;

    public function __construct()
    {
        $this->userModel           = new UserModel();
        $this->permissionModel     = new PermissionModel();
        $this->userPermissionModel = new UserPermissionModel();
    }

    // =========================================================
    // INDEX — List semua user
    // =========================================================
    public function index()
    {
        $this->data['title'] = 'User Permission Management';
        $this->data['users'] = $this->userModel
            ->select('users.*, roles.name as role_name')
            ->join('roles', 'roles.id = users.role_id', 'left')
            ->where('users.is_active', 1)
            ->findAll();

        return view('user_permission/index', $this->data);
    }

    // =========================================================
    // EDIT — Form centang permission per user
    // =========================================================
    public function edit($userId)
    {
        $user = $this->userModel
            ->select('users.*, roles.name as role_name, roles.id as role_id')
            ->join('roles', 'roles.id = users.role_id', 'left')
            ->find($userId);

        if (!$user) {
            return redirect()->to('/user-permissions')->with('error', 'User tidak ditemukan');
        }

        $allPermissions  = $this->permissionModel->findAll();
        $rolePermissions = $this->permissionModel->getPermissionsByRoleId($user['role_id']);
        $userPermissions = $this->userPermissionModel->getPermissionsByUserId($userId);

        $this->data['title']           = 'Edit Permissions - ' . $user['username'];
        $this->data['user']            = $user;
        $this->data['allPermissions']  = $allPermissions;
        $this->data['rolePermissions'] = array_column($rolePermissions, 'name');
        $this->data['userPermissions'] = array_column($userPermissions, 'name');

        return view('user_permission/edit', $this->data);
    }

    // =========================================================
    // UPDATE — Proses simpan permission (POST)
    // =========================================================
    public function update($userId)
    {

        $user = $this->userModel->find($userId);
        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User tidak ditemukan'
            ])->setStatusCode(404);
        }

        $selectedPermissions = $this->request->getPost('permissions') ?? [];

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // 1. Hapus semua custom permission user ini
            $this->userPermissionModel->where('user_id', $userId)->delete();

            // 2. Insert permission baru
            if (!empty($selectedPermissions)) {
                $now        = date('Y-m-d H:i:s');
                $insertData = [];

                foreach ($selectedPermissions as $permissionId) {
                    $insertData[] = [
                        'user_id'       => $userId,
                        'permission_id' => $permissionId,
                        'created_at'    => $now,
                    ];
                }
                $this->userPermissionModel->insertBatch($insertData);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Gagal menyimpan ke database.');
            }

            // [FIX #1] Refresh session jika user yang diedit sedang login sekarang
            if ((int) session()->get('user_id') === (int) $userId) {
                $newPermissions = $this->permissionModel->getPermissionsByUserId($userId);
                if (!in_array('Public', $newPermissions, true)) {
                    $newPermissions[] = 'Public';
                }
                session()->set('permissions', $newPermissions);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Permissions berhasil diperbarui'
            ]);

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Update user permission error: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    // =========================================================
    // REMOVE — Hapus satu permission (API style)
    // =========================================================
    public function remove($userId, $permissionId)
    {

        try {
            $this->userPermissionModel
                ->where('user_id', $userId)
                ->where('permission_id', $permissionId)
                ->delete();

            // [FIX #1] Refresh session jika user yang diedit sedang login
            if ((int) session()->get('user_id') === (int) $userId) {
                $newPermissions = $this->permissionModel->getPermissionsByUserId($userId);
                if (!in_array('Public', $newPermissions, true)) {
                    $newPermissions[] = 'Public';
                }
                session()->set('permissions', $newPermissions);
            }

            return $this->response->setJSON(['success' => true, 'message' => 'Permission dihapus']);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
}