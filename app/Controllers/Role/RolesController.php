<?php

namespace App\Controllers\Role;

use App\Controllers\BaseApiController;
use App\Models\AuditLogModel;
use App\Models\RoleModel;
use CodeIgniter\HTTP\ResponseInterface;

class RolesController extends BaseApiController
{
    protected $roleModel;
    protected $auditLogModel;

    public function __construct()
    {
        helper(['cookie', 'form', 'url']);
        $this->roleModel     = new RoleModel();
        $this->auditLogModel = new AuditLogModel();
    }

    // =========================================================
    // PAGE VIEW
    // =========================================================

    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu');
        }
        if (!$this->hasPermission('role.view')) {
            return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki akses ke halaman ini');
        }

        $perPage = (int)($this->request->getGet('per_page') ?? 10);
        $page    = (int)($this->request->getGet('page')     ?? 1);

        $filters = [
            'search'    => $this->request->getGet('search')    ?? '',
            'level'     => $this->request->getGet('level')     ?? '',
            'is_active' => $this->request->getGet('is_active') ?? '',
        ];

        $roles = $this->roleModel->getListWithUserCount($filters, $perPage, $page);
        $total = $this->roleModel->countList($filters);

        $pager = \Config\Services::pager();
        $pager->makeLinks($page, $perPage, $total);

        return view('role/index', [
            'title'       => 'Manajemen Role',
            'roles'       => $roles,
            'total'       => $total,
            'perPage'     => $perPage,
            'page'        => $page,
            'pager'       => $pager,
            'filters'     => $filters,
            'levelLabels' => $this->levelLabels(),
        ]);
    }

    // =========================================================
    // STORE — AJAX POST
    // =========================================================

    public function store(): ResponseInterface
    {
        if (!$this->request->isAJAX()) return $this->jsonError('Invalid request.', 400);
        if (!$this->hasPermission('role.create')) return $this->jsonError('Unauthorized.', 403);

        $rules = [
            'name'         => 'required|min_length[3]|max_length[50]|alpha_dash|is_unique[roles.name]',
            'display_name' => 'required|min_length[3]|max_length[100]',
            'level'        => 'required|integer|greater_than[0]',
        ];
        $messages = [
            'name' => [
                'alpha_dash' => 'Nama role hanya boleh huruf, angka, underscore, dan dash.',
                'is_unique'  => 'Nama role sudah digunakan.',
            ],
        ];

        if (!$this->validate($rules, $messages)) {
            return $this->jsonError(implode('<br>', $this->validator->getErrors()), 422);
        }

        $newId = $this->roleModel->insert([
            'name'         => strtolower($this->request->getPost('name')),
            'display_name' => $this->request->getPost('display_name'),
            'description'  => $this->request->getPost('description') ?? null,
            'level'        => $this->request->getPost('level'),
            'is_active'    => $this->request->getPost('is_active') ?? 1,
        ]);

        $this->log('CREATE', $newId, 'Tambah role: ' . $this->request->getPost('name'));

        return $this->response->setJSON([
            'status'    => 'success',
            'message'   => 'Role berhasil ditambahkan.',
            'csrf_hash' => csrf_hash(),
        ]);
    }

    // =========================================================
    // EDIT — AJAX GET
    // =========================================================

    public function edit($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) return $this->jsonError('Invalid request.', 400);
        if (!$this->hasPermission('role.edit')) return $this->jsonError('Unauthorized.', 403);

        $role = $this->roleModel->find($id);
        if (!$role) return $this->jsonError('Role tidak ditemukan.', 404);

        return $this->response->setJSON(['status' => 'success', 'data' => $role]);
    }

    // =========================================================
    // UPDATE — AJAX POST
    // =========================================================

    public function update($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) return $this->jsonError('Invalid request.', 400);
        if (!$this->hasPermission('role.edit')) return $this->jsonError('Unauthorized.', 403);

        $role = $this->roleModel->find($id);
        if (!$role) return $this->jsonError('Role tidak ditemukan.', 404);

        $rules = [
            'name'         => "required|min_length[3]|max_length[50]|alpha_dash|is_unique[roles.name,id,{$id}]",
            'display_name' => 'required|min_length[3]|max_length[100]',
            'level'        => 'required|integer|greater_than[0]',
        ];
        $messages = [
            'name' => [
                'alpha_dash' => 'Nama role hanya boleh huruf, angka, underscore, dan dash.',
                'is_unique'  => 'Nama role sudah digunakan.',
            ],
        ];

        if (!$this->validate($rules, $messages)) {
            return $this->jsonError(implode('<br>', $this->validator->getErrors()), 422);
        }

        $this->roleModel->update($id, [
            'name'         => strtolower($this->request->getPost('name')),
            'display_name' => $this->request->getPost('display_name'),
            'description'  => $this->request->getPost('description') ?? null,
            'level'        => $this->request->getPost('level'),
            'is_active'    => $this->request->getPost('is_active'),
        ]);

        $this->log('UPDATE', $id, 'Update role: ' . $this->request->getPost('name'));

        return $this->response->setJSON([
            'status'    => 'success',
            'message'   => 'Role berhasil diupdate.',
            'csrf_hash' => csrf_hash(),
        ]);
    }

    // =========================================================
    // DELETE — AJAX POST
    // =========================================================

    public function delete($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) return $this->jsonError('Invalid request.', 400);
        if (!$this->hasPermission('role.delete')) return $this->jsonError('Unauthorized.', 403);

        $role = $this->roleModel->find($id);
        if (!$role) return $this->jsonError('Role tidak ditemukan.', 404);

        // Proteksi: jangan hapus jika masih ada user yang memakai role ini
        if ($this->roleModel->isUsed($id)) {
            $userCount = \Config\Database::connect()->table('users')->where('role_id', $id)->countAllResults();
            return $this->jsonError(
                "Role \"{$role['display_name']}\" masih digunakan oleh " .
                $userCount .
                " user. Pindahkan user terlebih dahulu.",
                422
            );
        }

        $this->roleModel->delete($id);
        $this->log('DELETE', $id, 'Hapus role: ' . $role['name']);

        return $this->response->setJSON([
            'status'    => 'success',
            'message'   => 'Role berhasil dihapus.',
            'csrf_hash' => csrf_hash(),
        ]);
    }

    // =========================================================
    // TOGGLE AKTIF — AJAX POST
    // =========================================================

    public function toggleActive($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) return $this->jsonError('Invalid request.', 400);
        if (!$this->hasPermission('role.edit')) return $this->jsonError('Unauthorized.', 403);

        $role = $this->roleModel->find($id);
        if (!$role) return $this->jsonError('Role tidak ditemukan.', 404);

        $newStatus = $role['is_active'] ? 0 : 1;
        $this->roleModel->update($id, ['is_active' => $newStatus]);
        $this->log($newStatus ? 'ACTIVATE' : 'DEACTIVATE', $id, ($newStatus ? 'Aktifkan' : 'Nonaktifkan') . ' role: ' . $role['name']);

        return $this->response->setJSON([
            'status'    => 'success',
            'is_active' => $newStatus,
            'message'   => $newStatus ? 'Role diaktifkan.' : 'Role dinonaktifkan.',
            'csrf_hash' => csrf_hash(),
        ]);
    }

    // =========================================================
    // HELPERS PRIVATE
    // =========================================================

    private function hasPermission(string $permission): bool
    {
        return in_array($permission, session()->get('permissions') ?? []);
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
                'module'     => 'roles',
                'record_id'  => $recordId,
                'notes'      => $notes,
                'ip_address' => $this->request->getIPAddress(),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Audit log failed: ' . $e->getMessage());
        }
    }

    private function levelLabels(): array
    {
        return [
            1 => 'Admin',
            2 => 'Manager',
            3 => 'Supervisor',
            4 => 'Staff',
        ];
    }
}