<?php

namespace App\Controllers\EmployeeMaster;

use App\Controllers\BaseController;
use App\Models\EmployeeMaster\BloodTypeModel;

class BloodTypeController extends BaseController
{
    protected BloodTypeModel $model;

    public function __construct()
    {
        $this->model = new BloodTypeModel();
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
            'title'   => 'Blood Type Management',
            'bloodTypes' => $this->model->paginate($perPage),
            'pager'   => $this->model->pager,
            'search'  => $search,
            'perPage' => $perPage,
            'menus' => $this->data['menus'] ?? []
        ];

        return view('employees/blood_type/index', $data);
    }

    // -------------------------------------------------------
    // CREATE
    // -------------------------------------------------------
    public function create(): string
    {
        $data = [
            'title'      => 'Add Blood Type',
            'bloodType'  => null,
            'action'     => base_url('employees/blood-type/store'),
        ];

        return view('employees/blood_type/form', $data);
    }

    public function store()
    {
        $rules = ['name' => 'required|max_length[100]'];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->model->insert([
            'id'         => $this->model->generateId(),
            'name'       => $this->request->getPost('name'),
            'is_deleted' => false,
        ]);

        return redirect()->to(base_url('employees/blood-type'))->with('success', 'Blood type added successfully.');
    }

    // -------------------------------------------------------
    // EDIT
    // -------------------------------------------------------
    public function edit(string $id): string
    {
        $bloodType = $this->model->find($id);

        if (!$bloodType) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title'      => 'Edit Blood Type',
            'bloodType'  => $bloodType,
            'action'     => base_url("employees/blood-type/update/{$id}"),
        ];

        return view('employees/blood_type/form', $data);
    }

    public function update(string $id)
    {
        $rules = ['name' => 'required|max_length[100]'];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->model->update($id, [
            'name' => $this->request->getPost('name'),
        ]);

        return redirect()->to(base_url('employees/blood-type'))->with('success', 'Blood type updated successfully.');
    }

    // -------------------------------------------------------
    // TOGGLE STATUS (Enable / Disable)
    // -------------------------------------------------------
    public function toggleStatus(string $id)
    {
        $this->model->toggleStatus($id);

        return redirect()->to(base_url('employees/blood-type'))->with('success', 'Status updated successfully.');
    }
}