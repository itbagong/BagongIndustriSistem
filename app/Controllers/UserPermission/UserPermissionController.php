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
        $this->userModel = new UserModel();
        $this->permissionModel = new PermissionModel();
        $this->userPermissionModel = new UserPermissionModel();
    }

    /**
     * Halaman utama: List semua user
     */
    public function index()
    {
        // 1. Cek Permission (Gunakan nama yang sudah Anda insert di DB)
        // Pastikan di tabel permissions ada: 'userpermission.view'
        if (!in_array('userpermission.view', session()->get('permissions') ?? [], true)) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak: Anda tidak memiliki izin user_permission.view');
        }

        // 2. Gunakan $this->data (JANGAN membuat array baru $data = [])
        // Ini agar variable $menus dari BaseController tidak tertimpa
        $this->data['title'] = 'User Permission Management';
        
        $this->data['users'] = $this->userModel->select('users.*, roles.name as role_name')
            ->join('roles', 'roles.id = users.role_id', 'left')
            ->where('users.is_active', 1)
            ->findAll();

        return view('user_permission/index', $this->data);
    }

    /**
     * Halaman Edit: Form centang permission
     */
    public function edit($userId)
    {
        if (!in_array('userpermission.view', session()->get('permissions') ?? [], true)) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $user = $this->userModel->select('users.*, roles.name as role_name, roles.id as role_id')
            ->join('roles', 'roles.id = users.role_id', 'left')
            ->find($userId);

        if (!$user) {
            return redirect()->to('/user-permissions')->with('error', 'User tidak ditemukan');
        }

        // Ambil Data Permission
        $allPermissions  = $this->permissionModel->findAll();
        $rolePermissions = $this->permissionModel->getPermissionsByRoleId($user['role_id']);
        $userPermissions = $this->userPermissionModel->getPermissionsByUserId($userId);

        // Masukkan ke $this->data
        $this->data['title'] = 'Edit Permissions - ' . $user['username'];
        $this->data['user']  = $user;
        
        // Data Permission Lengkap
        $this->data['allPermissions'] = $allPermissions;
        
        // Kita hanya butuh nama permission-nya saja untuk pengecekan (in_array) di View
        $this->data['rolePermissions'] = array_column($rolePermissions, 'name'); // Contoh: ['user.view', 'dashboard.view']
        $this->data['userPermissions'] = array_column($userPermissions, 'name'); // Contoh: ['audit.view']

        return view('user_permission/edit', $this->data);
    }

    /**
     * Proses Update (POST)
     */
    public function update($userId)
    {
        // Cek permission
        if (!in_array('userpermission.view', session()->get('permissions') ?? [], true)) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Akses ditolak'
            ])->setStatusCode(403);
        }

        $user = $this->userModel->find($userId);
        if (!$user) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'User tidak ditemukan'
            ])->setStatusCode(404);
        }

        // Ambil data yang dicentang dari form
        $selectedPermissions = $this->request->getPost('permissions') ?? [];

        // Gunakan Database Transaction agar aman
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // 1. Hapus SEMUA permission khusus user ini (Reset)
            $this->userPermissionModel->where('user_id', $userId)->delete();

            // 2. Insert permission baru (jika ada yang dipilih)
            if (!empty($selectedPermissions)) {
                $insertData = [];
                $now = date('Y-m-d H:i:s');
                
                foreach ($selectedPermissions as $permissionId) {
                    $insertData[] = [
                        'user_id'       => $userId,
                        'permission_id' => $permissionId,
                        'created_at'    => $now
                    ];
                }
                $this->userPermissionModel->insertBatch($insertData);
            }

            $db->transComplete(); // Selesaikan transaksi

            if ($db->transStatus() === false) {
                throw new \Exception('Gagal menyimpan ke database.');
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Permissions berhasil diperbarui'
            ]);

        } catch (\Exception $e) {
            $db->transRollback(); // Batalkan perubahan jika error
            log_message('error', 'Update user permission error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Remove Single Permission (Optional API style)
     */
    public function remove($userId, $permissionId)
    {
        if (!in_array('userpermission.view', session()->get('permissions') ?? [], true)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Akses ditolak'])->setStatusCode(403);
        }

        try {
            $this->userPermissionModel
                ->where('user_id', $userId)
                ->where('permission_id', $permissionId)
                ->delete();

            return $this->response->setJSON(['success' => true, 'message' => 'Permission dihapus']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()])->setStatusCode(500);
        }
    }
}