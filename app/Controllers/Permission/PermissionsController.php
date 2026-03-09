<?php

namespace App\Controllers\Permission;

use App\Controllers\BaseApiController;
use App\Models\AuditLogModel;
use App\Models\PermissionModel;
use CodeIgniter\HTTP\ResponseInterface;

class PermissionsController extends BaseApiController
{
    protected $permissionModel;
    protected $auditLogModel;

    public function __construct()
    {
        helper(['cookie', 'form', 'url']);
        $this->permissionModel = new PermissionModel();
        $this->auditLogModel   = new AuditLogModel();
    }

    // =========================================================
    // PAGE VIEW
    // =========================================================

    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu');
        }
        if (!$this->hasPermission('permission.view')) {
            return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki akses ke halaman ini');
        }

        $perPage = (int)($this->request->getGet('per_page') ?? 10);
        $page    = (int)($this->request->getGet('page')     ?? 1);

        $filters = [
            'search' => $this->request->getGet('search') ?? '',
            'module' => $this->request->getGet('module') ?? '',
        ];

        $permissions = $this->permissionModel->getList($filters, $perPage, $page);
        $total       = $this->permissionModel->countList($filters);
        $modules     = $this->permissionModel->getModules();

        $pager = \Config\Services::pager();
        $pager->makeLinks($page, $perPage, $total);

        return view('permission/index', [
            'title'       => 'Manajemen Permission',
            'permissions' => $permissions,
            'total'       => $total,
            'perPage'     => $perPage,
            'page'        => $page,
            'pager'       => $pager,
            'filters'     => $filters,
            'modules'     => array_column($modules, 'module'),
        ]);
    }

    // =========================================================
    // STORE
    // =========================================================

    public function store(): ResponseInterface
    {
        if (!$this->request->isAJAX()) return $this->jsonError('Invalid request.', 400);
        if (!$this->hasPermission('permission.create')) return $this->jsonError('Unauthorized.', 403);

        $rules = [
            'name'         => 'required|min_length[3]|max_length[100]|is_unique[permissions.name]',
            'display_name' => 'required|min_length[3]|max_length[100]',
            'module'       => 'required|min_length[2]|max_length[50]',
        ];
        $messages = [
            'name' => ['is_unique' => 'Nama permission sudah digunakan.'],
        ];

        if (!$this->validate($rules, $messages)) {
            return $this->jsonError(implode('<br>', $this->validator->getErrors()), 422);
        }

        $newId = $this->permissionModel->insert([
            'name'         => strtolower(trim($this->request->getPost('name'))),
            'display_name' => $this->request->getPost('display_name'),
            'description'  => $this->request->getPost('description') ?? null,
            'module'       => strtolower(trim($this->request->getPost('module'))),
        ]);

        $this->log('CREATE', $newId, 'Tambah permission: ' . $this->request->getPost('name'));

        return $this->response->setJSON([
            'status'    => 'success',
            'message'   => 'Permission berhasil ditambahkan.',
            'csrf_hash' => csrf_hash(),
        ]);
    }

    // =========================================================
    // EDIT (GET)
    // =========================================================

    public function edit($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) return $this->jsonError('Invalid request.', 400);
        if (!$this->hasPermission('permission.edit')) return $this->jsonError('Unauthorized.', 403);

        $perm = $this->permissionModel->find($id);
        if (!$perm) return $this->jsonError('Permission tidak ditemukan.', 404);

        return $this->response->setJSON(['status' => 'success', 'data' => $perm]);
    }

    // =========================================================
    // UPDATE
    // =========================================================

    public function update($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) return $this->jsonError('Invalid request.', 400);
        if (!$this->hasPermission('permission.edit')) return $this->jsonError('Unauthorized.', 403);

        $perm = $this->permissionModel->find($id);
        if (!$perm) return $this->jsonError('Permission tidak ditemukan.', 404);

        $rules = [
            'name'         => "required|min_length[3]|max_length[100]|is_unique[permissions.name,id,{$id}]",
            'display_name' => 'required|min_length[3]|max_length[100]',
            'module'       => 'required|min_length[2]|max_length[50]',
        ];
        $messages = [
            'name' => ['is_unique' => 'Nama permission sudah digunakan.'],
        ];

        if (!$this->validate($rules, $messages)) {
            return $this->jsonError(implode('<br>', $this->validator->getErrors()), 422);
        }

        $this->permissionModel->update($id, [
            'name'         => strtolower(trim($this->request->getPost('name'))),
            'display_name' => $this->request->getPost('display_name'),
            'description'  => $this->request->getPost('description') ?? null,
            'module'       => strtolower(trim($this->request->getPost('module'))),
        ]);

        $this->log('UPDATE', $id, 'Update permission: ' . $this->request->getPost('name'));

        return $this->response->setJSON([
            'status'    => 'success',
            'message'   => 'Permission berhasil diupdate.',
            'csrf_hash' => csrf_hash(),
        ]);
    }

    // =========================================================
    // DELETE
    // =========================================================

    public function delete($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) return $this->jsonError('Invalid request.', 400);
        if (!$this->hasPermission('permission.delete')) return $this->jsonError('Unauthorized.', 403);

        $perm = $this->permissionModel->find($id);
        if (!$perm) return $this->jsonError('Permission tidak ditemukan.', 404);

        // Cek apakah masih dipakai di role_permissions
        $used = $this->db->table('role_permissions')
                         ->where('permission_id', $id)
                         ->countAllResults();

        if ($used > 0) {
            return $this->jsonError(
                "Permission \"{$perm['display_name']}\" masih digunakan oleh {$used} role. Hapus assignment terlebih dahulu.",
                422
            );
        }

        $this->permissionModel->delete($id);
        $this->log('DELETE', $id, 'Hapus permission: ' . $perm['name']);

        return $this->response->setJSON([
            'status'    => 'success',
            'message'   => 'Permission berhasil dihapus.',
            'csrf_hash' => csrf_hash(),
        ]);
    }

    // =========================================================
    // HELPERS
    // =========================================================

    private function hasPermission(string $permission): bool
    {
        $permissions = session()->get('permissions') ?? [];
        if (in_array($permission, $permissions)) return true;
        // Fallback: role level 1 (admin) selalu boleh
        return (int)(session()->get('role_level') ?? 99) === 1;
    }

    private function jsonError(string $message, int $code = 400): ResponseInterface
    {
        return $this->response->setJSON([
            'status'    => 'error',
            'message'   => $message,
            'csrf_hash' => csrf_hash(),
        ])->setStatusCode($code);
    }

    private function log(string $action, int $recordId, string $notes): void
    {
        try {
            $this->auditLogModel->insert([
                'user_id'    => session()->get('user_id'),
                'action'     => $action,
                'module'     => 'permissions',
                'record_id'  => $recordId,
                'notes'      => $notes,
                'ip_address' => $this->request->getIPAddress(),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Audit log failed: ' . $e->getMessage());
        }
    }
}