<?php

namespace App\Controllers\Employee;

use App\Controllers\BaseController;
use App\Models\EmployeeModel;
use CodeIgniter\HTTP\ResponseInterface;

class EmployeeController extends BaseController
{
    protected EmployeeModel $employeeModel;

    public function __construct()
    {
        $this->employeeModel = new EmployeeModel();
    }

    /* =====================================================
     * VIEW
     * ===================================================== */

    public function index()
    {
        return view('employees/index', [
            'title' => 'Manajemen Karyawan',
            'menus' => $this->data['menus'] ?? [] // aman untuk sidebar
        ]);
    }

    /* =====================================================
     * API DATA TABLE
     * ROUTE: GET /employees/data
     * ===================================================== */
    public function getData(): ResponseInterface
    {
        $filters = [
            'page'              => $this->request->getGet('page') ?? 1,
            'per_page'          => $this->request->getGet('per_page') ?? 10,
            'search'            => $this->request->getGet('search'),
            'department'        => $this->request->getGet('department'),
            'employment_status' => $this->request->getGet('employment_status'),
            'employee_status'   => $this->request->getGet('employee_status'),
        ];

        $result = $this->employeeModel->getFiltered($filters);

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $result['data'],
            'pagination' => $result['pagination']
        ]);
    }

    /* =====================================================
     * API STATISTICS
     * ROUTE: GET /employees/statistics
     * ===================================================== */
    public function getStatistics(): ResponseInterface
    {
        return $this->response->setJSON([
            'status' => 'success',
            'data' => $this->employeeModel->getStatistics()
        ]);
    }

    /* =====================================================
     * DETAIL VIEW (MODAL)
     * ROUTE: GET /employees/view/{id}
     * ===================================================== */
    public function view($id): ResponseInterface
    {
        $data = $this->employeeModel->getDetail($id);

        if (!$data) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Data karyawan tidak ditemukan'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $data
        ]);
    }

    /* =====================================================
     * FORM CREATE
     * ===================================================== */
    public function create()
    {
        return view('employees/create', [
            'title' => 'Tambah Karyawan',
            'menus' => $this->data['menus'] ?? []
        ]);
    }

    public function store()
    {
        $data = $this->request->getPost();
        $this->employeeModel->insert($data);

        return redirect()->to('/employees')->with('success', 'Data berhasil ditambahkan');
    }

    /* =====================================================
     * FORM UPDATE
     * ===================================================== */
    public function edit($id)
    {
        $employee = $this->employeeModel->find($id);

        if (!$employee) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Data tidak ditemukan');
        }

        return view('employees/edit', [
            'title' => 'Edit Karyawan',
            'employee' => $employee,
            'menus' => $this->data['menus'] ?? []
        ]);
    }

    public function update($id)
    {
        $data = $this->request->getPost();
        $this->employeeModel->update($id, $data);

        return redirect()->to('/employees')->with('success', 'Data berhasil diperbarui');
    }

    /* =====================================================
     * DELETE
     * ===================================================== */
    public function delete($id)
    {
        $this->employeeModel->delete($id);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Data berhasil dihapus'
        ]);
    }

    /* =====================================================
     * TEST DB
     * ===================================================== */
    public function testDb()
    {
        try {
            $db = \Config\Database::connect('pg');
            $db->query('SELECT 1');
            return '✅ PostgreSQL connected';
        } catch (\Throwable $e) {
            return '❌ DB error: ' . $e->getMessage();
        }
    }
}
