<?php

namespace App\Controllers\EmployeeMaster;

use App\Controllers\BaseController;
use App\Models\EmployeeMaster\EmploymentStatusModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class EmploymentStatusController extends BaseController
{
    protected EmploymentStatusModel $model;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->model = new EmploymentStatusModel();
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
                ->orWhere("id ILIKE '%{$search}%'", null, false)
                ->groupEnd();
        }

        $data = [
            'title'             => 'Manajemen Employment Status',
            'employmentStatuses' => $this->model->orderBy('id', 'ASC')->findAll(),
            'items'  => $this->model->orderBy('id', 'ASC')->paginate(10), // ← 10 per page
            'pager'  => $this->model->pager,                               // ← pager object
            'search'  => $search,
            'perPage' => $perPage,
            'menus' => $this->data['menus'] ?? []
        ];

        return view('employees/employment_status/index', $data);
    }

    // -------------------------------------------------------
    // STORE (Add)
    // -------------------------------------------------------
    public function store()
    {
        $rules = [
            'name'        => 'required|max_length[100]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->model->insert([
            'id'          => $this->model->generateId(),
            'name'        => $this->request->getPost('name'),
            'is_deleted'  => false,
        ]);

        return redirect()->to(base_url('employees/employment-status'))->with('success', 'Employment Status berhasil ditambahkan.');
    }

    // -------------------------------------------------------
    // UPDATE (Edit)
    // -------------------------------------------------------
    public function update(string $id)
    {
        $rules = [
            'name'        => 'required|max_length[100]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->model->update($id, [
            'name'        => $this->request->getPost('name'),
        ]);

        return redirect()->to(base_url('employees/employment-status'))->with('success', 'Employment Status berhasil diperbarui.');
    }

    // -------------------------------------------------------
    // TOGGLE STATUS
    // -------------------------------------------------------
    public function toggleStatus(string $id)
    {
        $this->model->toggleStatus($id);

        return redirect()->to(base_url('employees/employment-status'))->with('success', 'Status Employment Status berhasil diperbarui.');
    }
}