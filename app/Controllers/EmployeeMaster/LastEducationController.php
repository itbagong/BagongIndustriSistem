<?php

namespace App\Controllers\EmployeeMaster;

use App\Controllers\BaseController;
use App\Models\EmployeeMaster\LastEducationModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class LastEducationController extends BaseController
{
    protected LastEducationModel $model;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->model = new LastEducationModel();
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
                ->orWhere("description ILIKE '%{$search}%'", null, false)
                ->groupEnd();
        }

        $data = [
            'title'   => 'Manajemen Last Education',
            'lastEducations' => $this->model->paginate($perPage),
            'pager'   => $this->model->pager,
            'search'  => $search,
            'perPage' => $perPage,
            'menus' => $this->data['menus'] ?? []
        ];

        return view('employees/last_education/index', $data);
    }

    // -------------------------------------------------------
    // STORE (Add)
    // -------------------------------------------------------
    public function store()
    {
        $rules = [
            'name'        => 'required|max_length[100]',
            'description' => 'permit_empty|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->model->insert([
            'id'          => $this->model->generateId(),
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('description') ?? '',
            'is_deleted'  => false,
        ]);

        return redirect()->to(base_url('employees/last-education'))->with('success', 'Last Education berhasil ditambahkan.');
    }

    // -------------------------------------------------------
    // UPDATE (Edit)
    // -------------------------------------------------------
    public function update(string $id)
    {
        $rules = [
            'name'        => 'required|max_length[100]',
            'description' => 'permit_empty|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->model->update($id, [
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('description') ?? '',
        ]);

        return redirect()->to(base_url('employees/last-education'))->with('success', 'Last Education berhasil diperbarui.');
    }

    // -------------------------------------------------------
    // TOGGLE STATUS
    // -------------------------------------------------------
    public function toggleStatus(string $id)
    {
        $this->model->toggleStatus($id);

        return redirect()->to(base_url('employees/last-education'))->with('success', 'Status Last Education berhasil diperbarui.');
    }
}