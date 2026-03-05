<?php

namespace App\Controllers\EmployeeMaster;

use App\Controllers\BaseController;
use App\Models\EmployeeMaster\EmployeeStatusModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class EmployeeStatusController extends BaseController
{
    protected EmployeeStatusModel $model;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->model = new EmployeeStatusModel();
    }

    // -------------------------------------------------------
    // LIST
    // -------------------------------------------------------
    public function index(): string
    {
        $search  = $this->request->getGet('search') ?? '';
        $perPage = 10;

        $this->model->orderBy('id', 'ASC');
        if ($search) {
            $this->model->groupStart()
                ->where("name ILIKE '%{$search}%'", null, false)
                ->orWhere("description ILIKE '%{$search}%'", null, false)
                ->orWhere("id ILIKE '%{$search}%'", null, false)
                ->groupEnd();
        }

        $employeeStatuses = $this->model->paginate($perPage);

        // Decode Postgres text[] → PHP array for each row
        $employeeStatuses = array_map(function ($row) {
            $row['aliases'] = is_string($row['aliases'])
                ? $this->model->fromPostgresArray($row['aliases'])
                : ($row['aliases'] ?? []);
            return $row;
        }, $employeeStatuses);

        $data = [
            'title'             => 'Manajemen Employee Status',
            'employeeStatuses' => $employeeStatuses,
            'pager'  => $this->model->pager,                               // ← pager object
            'search'  => $search,
            'perPage' => $perPage,
            'menus' => $this->data['menus'] ?? []
        ];

        return view('employees/employee_status/index', $data);
    }

    // -------------------------------------------------------
    // STORE (Add)
    // -------------------------------------------------------
    public function store()
    {
        $rules = [
            'id'          => 'required|regex_match[/^EMST-BDM-.+/]',
            'name'        => 'required|max_length[100]',
            'description' => 'permit_empty|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $aliases = json_decode($this->request->getPost('aliases') ?? '[]', true);
        if (!is_array($aliases)) $aliases = [];

        $this->model->insertEs([
            'id'          => $this->request->getPost('id'),
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('description') ?? '',
            'aliases'     => $aliases,
        ]);

        return redirect()->to(base_url('employees/employee-status'))->with('success', 'Employee Status berhasil ditambahkan.');
    }

    // -------------------------------------------------------
    // UPDATE (Edit)
    // -------------------------------------------------------
    public function update(string $id)
    {
        $rules = [
            'id'          => 'required|regex_match[/^EMST-BDM-.+/]',
            'name'        => 'required|max_length[100]',
            'description' => 'permit_empty|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $aliases = json_decode($this->request->getPost('aliases') ?? '[]', true);
        if (!is_array($aliases)) $aliases = [];

        try{
            $this->model->updateEs($id, [
                'id'          => $this->request->getPost('id'),
                'name'        => $this->request->getPost('name'),
                'description' => $this->request->getPost('description') ?? '',
                'aliases'     => $aliases,
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[EmploymentStatusController::update] ' . $e->getMessage());
            return redirect()->back()->withInput()
                            ->with('error', 'Gagal memperbarui employee status: ' . $e->getMessage());
        }

        return redirect()->to(base_url('employees/employee-status'))->with('success', 'Employee Status berhasil diperbarui.');
    }

    // -------------------------------------------------------
    // TOGGLE STATUS
    // -------------------------------------------------------
    public function toggleStatus(string $id)
    {
        $this->model->toggleStatus($id);

        return redirect()->to(base_url('employees/employee-status'))->with('success', 'Status Employee Status berhasil diperbarui.');
    }
}