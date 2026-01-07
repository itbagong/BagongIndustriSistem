<?php

namespace App\Controllers\Employee;

use App\Controllers\BaseApiController;
use App\Models\AuditLogModel;
use CodeIgniter\HTTP\ResponseInterface;

class EmployeeController extends BaseApiController
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

        if (! $this->hasPermission('employee.view')) {
            return redirect()->to('/dashboard')
                ->with('error', 'Anda tidak memiliki akses ke halaman ini');
        }

        return view('employees/index', [
            'title' => 'Data Karyawan'
        ]);
    }

    /**
     * Load employees (AJAX)
     * Calls API /employees?page=&limit=&search=
     */
    public function getData(): ResponseInterface
    {
        if (! session()->get('logged_in')) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Authentication required'
            ])->setStatusCode(401);
        }

        if (! $this->hasPermission('employee.view')) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Unauthorized access'
            ])->setStatusCode(403);
        }

        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = (int) ($this->request->getGet('per_page') ?? 10);
        $search  = $this->request->getGet('search') ?? '';
        $department = $this->request->getGet('department') ?? '';
        $status  = $this->request->getGet('employee_status') ?? '';

        try {

            $response = $this->api->get('employees', [
                'query' => [
                    'page'   => $page,
                    'limit'  => $perPage,
                    'search' => $search,
                    'department' => $department,
                    'status' => $status
                ]
            ]);

            $data = json_decode($response->getBody(), true);

            $employees = $data['data'] ?? [];

            return $this->response->setJSON([
                'status'     => 'success',
                'data'       => $employees,
                'pagination' => $data['pagination'] ?? [
                    'page'        => $page,
                    'per_page'    => $perPage,
                    'total'       => count($employees),
                    'total_pages' => 1
                ]
            ]);

        } catch (\Throwable $e) {

            log_message('error', 'EMPLOYEE API ERROR: ' . $e->getMessage());

            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Gagal mengambil data dari API'
            ])->setStatusCode(500);
        }
    }

    /**
     * Employee detail
     * API: /employees/{id}
     */
    public function view($id = null): ResponseInterface
    {
        if (! $id) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Employee ID is required'
            ])->setStatusCode(400);
        }

        try {

            $response = $this->api->get('employees/' . $id);

            $data = json_decode($response->getBody(), true);

            return $this->response->setJSON([
                'status'  => 'success',
                'data'    => $data['data'] ?? $data
            ]);

        } catch (\Throwable $e) {

            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Employee not found'
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
