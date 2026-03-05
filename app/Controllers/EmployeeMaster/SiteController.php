<?php

namespace App\Controllers\EmployeeMaster;

use App\Controllers\BaseController;
use App\Models\EmployeeMaster\SiteModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class SiteController extends BaseController
{
    protected SiteModel $model;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->model = new SiteModel();
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
                ->orWhere("address ILIKE '%{$search}%'", null, false)
                ->orWhere("description ILIKE '%{$search}%'", null, false)
                ->groupEnd();
        }

        $sites = $this->model->paginate($perPage);

        // Decode Postgres text[] → PHP array for each row
        $sites = array_map(function ($row) {
            $row['aliases'] = is_string($row['aliases'])
                ? $this->model->fromPostgresArray($row['aliases'])
                : ($row['aliases'] ?? []);
            return $row;
        }, $sites);

        $data = [
            'title'   => 'Manajemen Site',
            'sites'   => $sites,
            'pager'   => $this->model->pager,
            'search'  => $search,
            'perPage' => $perPage,
            'menus'   => $this->data['menus'] ?? [],
        ];

        return view('employees/site/index', $data);
    }

    // -------------------------------------------------------
    // STORE (Add)
    // -------------------------------------------------------
    public function store()
    {
        $rules = [
            'id'          => 'required|regex_match[/^SITE.+/]',
            'name'        => 'required|max_length[100]',
            'description' => 'permit_empty|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                            ->with('errors', $this->validator->getErrors());
        }

        $aliases = json_decode($this->request->getPost('aliases') ?? '[]', true);
        if (!is_array($aliases)) $aliases = [];

        $this->model->insertSite([
            'id'          => $this->request->getPost('id'),
            'name'        => $this->request->getPost('name'),
            'address'     => $this->request->getPost('address') ?? '',
            'description' => $this->request->getPost('description') ?? '',
            'aliases'     => $aliases,
        ]);

        return redirect()->to(base_url('employees/site'))
                        ->with('success', 'Site berhasil ditambahkan.');
    }

    // -------------------------------------------------------
    // UPDATE (Edit)
    // -------------------------------------------------------
    public function update(string $oldId)
    {
        $rules = [
            'id'          => 'required|regex_match[/^SITE.+/]',
            'name'        => 'required|max_length[100]',
            'description' => 'permit_empty|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                            ->with('errors', $this->validator->getErrors());
        }

        $aliases = json_decode($this->request->getPost('aliases') ?? '[]', true);
        if (!is_array($aliases)) $aliases = [];

        try {
            $this->model->updateSite($oldId, [
                'id'          => $this->request->getPost('id'),
                'name'        => $this->request->getPost('name'),
                'address'     => $this->request->getPost('address') ?? '',
                'description' => $this->request->getPost('description') ?? '',
                'aliases'     => $aliases,
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[SiteController::update] ' . $e->getMessage());
            return redirect()->back()->withInput()
                            ->with('error', 'Gagal memperbarui site: ' . $e->getMessage());
        }

        return redirect()->to(base_url('employees/site'))
                        ->with('success', 'Site berhasil diperbarui.');
    }

    // -------------------------------------------------------
    // TOGGLE STATUS
    // -------------------------------------------------------
    public function toggleStatus(string $id)
    {
        $this->model->toggleStatus($id);

        return redirect()->to(base_url('employees/site'))->with('success', 'Status Site berhasil diperbarui.');
    }
}