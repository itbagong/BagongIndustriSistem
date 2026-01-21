<?php

namespace App\Controllers\Users;

use App\Controllers\BaseApiController;
use App\Models\AuditLogModel;
use CodeIgniter\HTTP\ResponseInterface;

class UsersController extends BaseApiController
{
    protected $auditLogModel;

    public function __construct()
    {
        helper('cookie');

        $this->auditLogModel = new AuditLogModel();
    }

    /**
     * Page View
     */
    public function index()
    {
        if (! session()->get('logged_in')) {
            return redirect()->to('/login')
                ->with('error', 'Silakan login terlebih dahulu');
        }

        if (! $this->hasPermission('user.view')) {
            return redirect()->to('/dashboard')
                ->with('error', 'Anda tidak memiliki akses ke halaman ini');
        }

        return view('users/index', [
            'title' => 'Data Karyawan'
        ]);
    }

    /**
     * Load users (AJAX)
     * Calls API /users?page=&limit=&search=
     */
    public function getData(): ResponseInterface
    {
        if (! session()->get('logged_in')) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Authentication required'
            ])->setStatusCode(401);
        }

        if (! $this->hasPermission('user.view')) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Unauthorized access'
            ])->setStatusCode(403);
        }

        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = (int) ($this->request->getGet('per_page') ?? 10);
        $search  = $this->request->getGet('search') ?? '';
        $department = $this->request->getGet('department') ?? '';
        $status  = $this->request->getGet('user_status') ?? '';

        try {

            $response = $this->api->get('users', [
                'query' => [
                    'page'   => $page,
                    'limit'  => $perPage,
                    'search' => $search,
                    'department' => $department,
                    'status' => $status
                ]
            ]);

            $data = json_decode($response->getBody(), true);

            $users = $data['data'] ?? [];

            return $this->response->setJSON([
                'status'     => 'success',
                'data'       => $users,
                'pagination' => $data['pagination'] ?? [
                    'page'        => $page,
                    'per_page'    => $perPage,
                    'total'       => count($users),
                    'total_pages' => 1
                ]
            ]);

        } catch (\Throwable $e) {

            log_message('error', 'user API ERROR: ' . $e->getMessage());

            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Gagal mengambil data dari API'
            ])->setStatusCode(500);
        }
    }

    /**
     * user detail
     * API: /users/{id}
     */
    public function view($id = null): ResponseInterface
    {
        if (! $id) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'user ID is required'
            ])->setStatusCode(400);
        }

        try {

            $response = $this->api->get('users/' . $id);

            $data = json_decode($response->getBody(), true);

            return $this->response->setJSON([
                'status'  => 'success',
                'data'    => $data['data'] ?? $data
            ]);

        } catch (\Throwable $e) {

            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'user not found'
            ])->setStatusCode(404);
        }
    }

    /**
     * Check permission
     */
    private function hasPermission(string $permission): bool
    {
        return in_array($permission, session()->get('permissions') ?? []);
    }
}
