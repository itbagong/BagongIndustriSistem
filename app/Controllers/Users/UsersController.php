<?php

namespace App\Controllers\Users;

use App\Controllers\BaseApiController;
use App\Models\AuditLogModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class UsersController extends BaseApiController
{
    protected $auditLogModel;
    protected $userModel;

    public function __construct()
    {
        helper('cookie');

        $this->auditLogModel = new AuditLogModel();
        $this->userModel     = new UserModel();
    }

    // =========================================================
    // PAGE VIEW
    // =========================================================

    public function index()
{
    if (!session()->get('logged_in')) {
        return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu');
    }
    if (!$this->hasPermission('user.view')) {
        return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki akses');
    }

    $db      = \Config\Database::connect();
    $perPage = (int)($this->request->getGet('per_page') ?? 10);
    $page    = (int)($this->request->getGet('page')     ?? 1);

    $filters = [
        'search'    => $this->request->getGet('search')    ?? '',
        'role_id'   => $this->request->getGet('role_id')   ?? '',
        'is_active' => $this->request->getGet('is_active') ?? '',
    ];

    $users = $this->userModel->getList($filters, $perPage, $page);
    $total = $this->userModel->countList($filters);

    $pager = \Config\Services::pager();
    $pager->makeLinks($page, $perPage, $total);

    return view('users/index', [
        'title'   => 'Manajemen User',
        'roles'   => $db->table('roles')->orderBy('name')->get()->getResultArray(),
        'users'   => $users,
        'total'   => $total,
        'perPage' => $perPage,
        'page'    => $page,
        'pager'   => $pager,
        'filters' => $filters,
    ]);
}

    // =========================================================
    // GET DATA — AJAX (via API atau langsung DB)
    // =========================================================

    public function getData(): ResponseInterface
    {
        if (!session()->get('logged_in')) {
            return $this->jsonError('Authentication required', 401);
        }

        if (!$this->hasPermission('user.view')) {
            return $this->jsonError('Unauthorized access', 403);
        }

        $page      = (int)($this->request->getGet('page')        ?? 1);
        $perPage   = (int)($this->request->getGet('per_page')    ?? 10);
        $search    = $this->request->getGet('search')            ?? '';
        $roleId    = $this->request->getGet('role_id')           ?? '';
        $isActive  = $this->request->getGet('is_active')         ?? '';

        try {
            // Coba via API dulu; jika tidak ada, fallback ke DB langsung
            $response = $this->api->get('users', [
                'query' => [
                    'page'      => $page,
                    'limit'     => $perPage,
                    'search'    => $search,
                    'role_id'   => $roleId,
                    'status'    => $isActive,
                ]
            ]);

            $data  = json_decode($response->getBody(), true);
            $users = $data['data'] ?? [];

            return $this->response->setJSON([
                'status'     => 'success',
                'data'       => $users,
                'pagination' => $data['pagination'] ?? [
                    'page'        => $page,
                    'per_page'    => $perPage,
                    'total'       => count($users),
                    'total_pages' => 1,
                ],
            ]);

        } catch (\Throwable $e) {
            // Fallback: ambil langsung dari DB
            log_message('warning', 'User API unavailable, fallback to DB: ' . $e->getMessage());

            $filters = [
                'search'    => $search,
                'role_id'   => $roleId,
                'is_active' => $isActive,
            ];

            $users = $this->userModel->getList($filters, $perPage, $page);
            $total = $this->userModel->countList($filters);

            return $this->response->setJSON([
                'status' => 'success',
                'data'   => $users,
                'pagination' => [
                    'page'        => $page,
                    'per_page'    => $perPage,
                    'total'       => $total,
                    'total_pages' => (int)ceil($total / $perPage),
                ],
            ]);
        }
    }

    // =========================================================
    // VIEW DETAIL — AJAX
    // =========================================================

    public function view($id = null): ResponseInterface
    {
        if (!$id) {
            return $this->jsonError('User ID is required', 400);
        }

        if (!$this->hasPermission('user.view')) {
            return $this->jsonError('Unauthorized access', 403);
        }

        try {
            $response = $this->api->get('users/' . $id);
            $data     = json_decode($response->getBody(), true);

            return $this->response->setJSON([
                'status' => 'success',
                'data'   => $data['data'] ?? $data,
            ]);

        } catch (\Throwable $e) {
            // Fallback DB
            $user = $this->userModel->find($id);
            if (!$user) return $this->jsonError('User tidak ditemukan', 404);

            unset($user['password'], $user['remember_token']);
            return $this->response->setJSON(['status' => 'success', 'data' => $user]);
        }
    }

    // =========================================================
    // STORE — AJAX POST
    // =========================================================

    public function store(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->jsonError('Invalid request', 400);
        }

        if (!$this->hasPermission('user.create')) {
            return $this->jsonError('Unauthorized access', 403);
        }

        $rules = [
            'username'         => 'required|min_length[3]|max_length[225]|is_unique[users.username]',
            'email'            => 'required|valid_email|max_length[255]|is_unique[users.email]',
            'password'         => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
            'role_id'          => 'required|integer',
        ];
        $messages = [
            'username'         => ['is_unique'  => 'Username sudah digunakan.'],
            'email'            => ['is_unique'  => 'Email sudah terdaftar.'],
            'password'         => ['min_length' => 'Password minimal 8 karakter.'],
            'password_confirm' => ['matches'    => 'Konfirmasi password tidak cocok.'],
        ];

        if (!$this->validate($rules, $messages)) {
            return $this->jsonError(
                implode('<br>', $this->validator->getErrors()),
                422
            );
        }

        $newId = $this->userModel->insert([
            'username'    => $this->request->getPost('username'),
            'email'       => $this->request->getPost('email'),
            'password'    => $this->request->getPost('password'),
            'role_id'     => $this->request->getPost('role_id'),
            'employee_id' => $this->request->getPost('employee_id') ?: null,
            'is_active'   => $this->request->getPost('is_active') ?? 1,
            'api_user_id' => bin2hex(random_bytes(16)),
        ]);

        $this->auditLogModel->insert([
            'user_id'    => session()->get('user_id'),
            'action'     => 'CREATE',
            'module'     => 'users',
            'record_id'  => $newId,
            'notes'      => 'Tambah user: ' . $this->request->getPost('username'),
            'ip_address' => $this->request->getIPAddress(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'status'    => 'success',
            'message'   => 'User berhasil ditambahkan.',
            'csrf_hash' => csrf_hash(),
        ]);
    }

    // =========================================================
    // EDIT — AJAX GET (kembalikan data JSON untuk isi form modal)
    // =========================================================

    public function edit($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->jsonError('Invalid request', 400);
        }

        if (!$this->hasPermission('user.edit')) {
            return $this->jsonError('Unauthorized access', 403);
        }

        $user = $this->userModel->find($id);
        if (!$user) return $this->jsonError('User tidak ditemukan', 404);

        unset($user['password'], $user['remember_token']);

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $user,
        ]);
    }

    // =========================================================
    // UPDATE — AJAX POST
    // =========================================================

    public function update($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->jsonError('Invalid request', 400);
        }

        if (!$this->hasPermission('user.edit')) {
            return $this->jsonError('Unauthorized access', 403);
        }

        $user = $this->userModel->find($id);
        if (!$user) return $this->jsonError('User tidak ditemukan', 404);

        $rules = [
            'username' => "required|min_length[3]|max_length[225]|is_unique[users.username,id,{$id}]",
            'email'    => "required|valid_email|max_length[255]|is_unique[users.email,id,{$id}]",
            'role_id'  => 'required|integer',
        ];
        $messages = [
            'username' => ['is_unique' => 'Username sudah digunakan.'],
            'email'    => ['is_unique' => 'Email sudah terdaftar.'],
        ];

        $password = $this->request->getPost('password');
        if (!empty($password)) {
            $rules['password']         = 'min_length[8]';
            $rules['password_confirm'] = 'matches[password]';
            $messages['password_confirm'] = ['matches' => 'Konfirmasi password tidak cocok.'];
        }

        if (!$this->validate($rules, $messages)) {
            return $this->jsonError(
                implode('<br>', $this->validator->getErrors()),
                422
            );
        }

        $updateData = [
            'username'    => $this->request->getPost('username'),
            'email'       => $this->request->getPost('email'),
            'role_id'     => $this->request->getPost('role_id'),
            'employee_id' => $this->request->getPost('employee_id') ?: null,
            'is_active'   => $this->request->getPost('is_active'),
        ];
        if (!empty($password)) $updateData['password'] = $password;

        $this->userModel->update($id, $updateData);

        $this->auditLogModel->insert([
            'user_id'    => session()->get('user_id'),
            'action'     => 'UPDATE',
            'module'     => 'users',
            'record_id'  => $id,
            'notes'      => 'Update user: ' . $this->request->getPost('username'),
            'ip_address' => $this->request->getIPAddress(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'status'    => 'success',
            'message'   => 'User berhasil diupdate.',
            'csrf_hash' => csrf_hash(),
        ]);
    }

    // =========================================================
    // DELETE — AJAX POST (soft delete)
    // =========================================================

    public function delete($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->jsonError('Invalid request', 400);
        }

        if (!$this->hasPermission('user.delete')) {
            return $this->jsonError('Unauthorized access', 403);
        }

        // Proteksi: jangan hapus diri sendiri
        if ((int)session()->get('user_id') === (int)$id) {
            return $this->jsonError('Tidak bisa menghapus akun sendiri.', 422);
        }

        $user = $this->userModel->find($id);
        if (!$user) return $this->jsonError('User tidak ditemukan', 404);

        $this->userModel->delete($id);

        $this->auditLogModel->insert([
            'user_id'    => session()->get('user_id'),
            'action'     => 'DELETE',
            'module'     => 'users',
            'record_id'  => $id,
            'notes'      => 'Hapus user: ' . $user['username'],
            'ip_address' => $this->request->getIPAddress(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'status'    => 'success',
            'message'   => 'User berhasil dihapus.',
            'csrf_hash' => csrf_hash(),
        ]);
    }

    // =========================================================
    // TOGGLE AKTIF — AJAX POST
    // =========================================================

    public function toggleActive($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->jsonError('Invalid request', 400);
        }

        if (!$this->hasPermission('user.edit')) {
            return $this->jsonError('Unauthorized access', 403);
        }

        $user = $this->userModel->find($id);
        if (!$user) return $this->jsonError('User tidak ditemukan', 404);

        $newStatus = $user['is_active'] ? 0 : 1;
        $this->userModel->update($id, ['is_active' => $newStatus]);

        $this->auditLogModel->insert([
            'user_id'    => session()->get('user_id'),
            'action'     => $newStatus ? 'ACTIVATE' : 'DEACTIVATE',
            'module'     => 'users',
            'record_id'  => $id,
            'notes'      => ($newStatus ? 'Aktifkan' : 'Nonaktifkan') . ' user: ' . $user['username'],
            'ip_address' => $this->request->getIPAddress(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'status'    => 'success',
            'is_active' => $newStatus,
            'message'   => $newStatus ? 'User diaktifkan.' : 'User dinonaktifkan.',
            'csrf_hash' => csrf_hash(),
        ]);
    }

    // =========================================================
    // RESET PASSWORD — AJAX POST
    // =========================================================

    public function resetPassword($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->jsonError('Invalid request', 400);
        }

        if (!$this->hasPermission('user.edit')) {
            return $this->jsonError('Unauthorized access', 403);
        }

        $password = $this->request->getPost('new_password');
        if (empty($password) || strlen($password) < 8) {
            return $this->jsonError('Password minimal 8 karakter.', 422);
        }

        $user = $this->userModel->find($id);
        if (!$user) return $this->jsonError('User tidak ditemukan', 404);

        $this->userModel->update($id, ['password' => $password]);

        $this->auditLogModel->insert([
            'user_id'    => session()->get('user_id'),
            'action'     => 'RESET_PASSWORD',
            'module'     => 'users',
            'record_id'  => $id,
            'notes'      => 'Reset password user: ' . $user['username'],
            'ip_address' => $this->request->getIPAddress(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'status'    => 'success',
            'message'   => 'Password berhasil direset.',
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
}