<?php

namespace App\Controllers\EmployeeMaster;

use App\Controllers\BaseController;
use App\Models\EmployeeMaster\DivisionModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class DivisionController extends BaseController
{
    protected DivisionModel $model;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->model = new DivisionModel();
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
                ->orWhere("code ILIKE '%{$search}%'", null, false)
                ->orWhere("description ILIKE '%{$search}%'", null, false)
                ->orWhere("id ILIKE '%{$search}%'", null, false)
                ->groupEnd();
        }

        $divisions = $this->model->paginate($perPage);

        // Decode Postgres text[] → PHP array for each row
        $divisions = array_map(function ($row) {
            $row['aliases'] = is_string($row['aliases'])
                ? $this->model->fromPostgresArray($row['aliases'])
                : ($row['aliases'] ?? []);
            return $row;
        }, $divisions);

        $data = [
            'title'   => 'Manajemen Division',
            'divisions' => $divisions,
            'pager'   => $this->model->pager,
            'search'  => $search,
            'perPage' => $perPage,
            'menus' => $this->data['menus'] ?? []
        ];

        return view('employees/division/index', $data);
    }

    // -------------------------------------------------------
    // STORE (Add)
    // -------------------------------------------------------
    public function store()
    {
        $rules = [
            'id'          => 'required|regex_match[/^BU-BDM-.+/]',
            'name'        => 'required|max_length[100]',
            'code'        => 'required|max_length[20]',
            'description' => 'permit_empty|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $aliases = json_decode($this->request->getPost('aliases') ?? '[]', true);
        if (!is_array($aliases)) $aliases = [];

        $this->model->insertDiv([
            'id'          => $this->request->getPost('id'),
            'name'        => $this->request->getPost('name'),
            'code'        => strtoupper($this->request->getPost('code')),
            'description' => $this->request->getPost('description') ?? '',
            'aliases'     => $aliases,
        ]);

        return redirect()->to(base_url('employees/division'))->with('success', 'Division berhasil ditambahkan.');
    }

    // -------------------------------------------------------
    // UPDATE (Edit)
    // -------------------------------------------------------
    public function update(string $id)
    {
        $rules = [
            'id'          => 'required|regex_match[/^BU-BDM-.+/]',
            'name'        => 'required|max_length[100]',
            'code'        => 'required|max_length[20]',
            'description' => 'permit_empty|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $aliases = json_decode($this->request->getPost('aliases') ?? '[]', true);
        if (!is_array($aliases)) $aliases = [];

        try{
            $this->model->updateDiv($id, [
                'id'          => $this->request->getPost('id'),
                'name'        => $this->request->getPost('name'),
                'code'        => strtoupper($this->request->getPost('code')),
                'description' => $this->request->getPost('description') ?? '',
                'aliases'     => $aliases,
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[DivisionController::update] ' . $e->getMessage());
            return redirect()->back()->withInput()
                            ->with('error', 'Gagal memperbarui division: ' . $e->getMessage());
        }

        return redirect()->to(base_url('employees/division'))->with('success', 'Division berhasil diperbarui.');
    }

    // -------------------------------------------------------
    // TOGGLE STATUS
    // -------------------------------------------------------
    public function toggleStatus(string $id)
    {
        $this->model->toggleStatus($id);

        return redirect()->to(base_url('employees/division'))->with('success', 'Status Division berhasil diperbarui.');
    }
}