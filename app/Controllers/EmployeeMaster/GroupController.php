<?php

namespace App\Controllers\EmployeeMaster;

use App\Controllers\BaseController;
use App\Models\EmployeeMaster\GroupModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class GroupController extends BaseController
{
    protected GroupModel $model;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->model = new GroupModel();
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

        $groups = $this->model->paginate($perPage);

        // Decode Postgres text[] → PHP array for each row
        $groups = array_map(function ($row) {
            $row['aliases'] = is_string($row['aliases'])
                ? $this->model->fromPostgresArray($row['aliases'])
                : ($row['aliases'] ?? []);
            return $row;
        }, $groups);

        $data = [
            'title'   => 'Manajemen Group',
            'groups' => $groups,
            'pager'   => $this->model->pager,
            'search'  => $search,
            'perPage' => $perPage,
            'menus' => $this->data['menus'] ?? []
        ];

        return view('employees/group/index', $data);
    }

    // -------------------------------------------------------
    // STORE (Add)
    // -------------------------------------------------------
    public function store()
    {
        $rules = [
            'id'          => 'required|regex_match[/^GRO-BDM-.+/]',
            'name'        => 'required|max_length[100]',
            'description' => 'permit_empty|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $aliases = json_decode($this->request->getPost('aliases') ?? '[]', true);
        if (!is_array($aliases)) $aliases = [];

        $this->model->insertGroup([
            'id'          => $this->request->getPost('id'),
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('description') ?? '',
            'aliases'     => $aliases,
        ]);

        return redirect()->to(base_url('employees/group'))->with('success', 'Group berhasil ditambahkan.');
    }

    // -------------------------------------------------------
    // UPDATE (Edit)
    // -------------------------------------------------------
    public function update(string $id)
    {
        $rules = [
            'id'          => 'required|regex_match[/^GRO-BDM-.+/]',
            'name'        => 'required|max_length[100]',
            'description' => 'permit_empty|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $aliases = json_decode($this->request->getPost('aliases') ?? '[]', true);
        if (!is_array($aliases)) $aliases = [];

        try{
            $this->model->updateGroup($id, [
                'id'          => $this->request->getPost('id'),
                'name'        => $this->request->getPost('name'),
                'description' => $this->request->getPost('description') ?? '',
                'aliases'     => $aliases,
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[GroupController::update] ' . $e->getMessage());
            return redirect()->back()->withInput()
                            ->with('error', 'Gagal memperbarui group: ' . $e->getMessage());
        }

        return redirect()->to(base_url('employees/group'))->with('success', 'Group berhasil diperbarui.');
    }

    // -------------------------------------------------------
    // TOGGLE STATUS
    // -------------------------------------------------------
    public function toggleStatus(string $id)
    {
        $this->model->toggleStatus($id);

        return redirect()->to(base_url('employees/group'))->with('success', 'Status Group berhasil diperbarui.');
    }
}