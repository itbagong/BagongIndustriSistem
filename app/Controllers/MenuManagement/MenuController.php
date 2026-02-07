<?php

namespace App\Controllers\MenuManagement;

use App\Controllers\BaseController;
use App\Models\MenuModel;
use App\Models\PermissionModel;

class MenuController extends BaseController
{
    protected $menuModel;
    protected $permissionModel;

    public function __construct()
    {
        $this->menuModel = new MenuModel();
        $this->permissionModel = new PermissionModel();
    }

    public function index()
    {
        $permissions = session()->get('permissions') ?? [];
        if (!in_array('settings.view', $permissions) && !in_array('settings.edit', $permissions)) {
            return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki akses ke halaman ini');
        }

        $this->data['title'] = 'Manajemen Menu';

        // ✅ Pakai key berbeda untuk data tabel di halaman ini
        // $this->data['menus'] tetap dari BaseController untuk sidebar
        $this->data['all_menus'] = $this->menuModel
            ->orderBy('parent_id', 'ASC')
            ->orderBy('sort_order', 'ASC')
            ->findAll();

        return view('menus/index', $this->data);
    }

    public function new()
    {
        // Cek permission
        $permissions = session()->get('permissions') ?? [];
        if (!in_array('settings.edit', $permissions)) {
            return redirect()->to('/menus')->with('error', 'Anda tidak memiliki akses untuk membuat menu');
        }

        $this->data['title'] = 'Tambah Menu Baru';
        $this->data['parents'] = $this->menuModel->getParents();
        $this->data['permissions'] = $this->permissionModel->orderBy('name', 'ASC')->findAll();
        $this->data['menu'] = null;

        return view('menus/form', $this->data);
    }

    public function create()
    {
        // Cek permission
        $permissions = session()->get('permissions') ?? [];
        if (!in_array('settings.edit', $permissions)) {
            return redirect()->to('/menus')->with('error', 'Anda tidak memiliki akses');
        }

        // Validation rules
        $rules = [
            'name' => 'required|min_length[2]|max_length[100]',
            'sort_order' => 'required|numeric'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $data = [
            'parent_id' => $this->request->getPost('parent_id') ?: null,
            'name' => trim($this->request->getPost('name')),
            'icon' => trim($this->request->getPost('icon')) ?: null,
            'route' => trim($this->request->getPost('route')) ?: null,
            'permission' => trim($this->request->getPost('permission')) ?: null,
            'sort_order' => (int) $this->request->getPost('sort_order'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0
        ];

        try {
            if ($this->menuModel->insert($data)) {
                return redirect()->to('/menus')->with('success', 'Menu berhasil ditambahkan');
            }
            
            return redirect()->back()->withInput()->with('error', 'Gagal menambah menu');
        } catch (\Exception $e) {
            log_message('error', 'Create menu error: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        // Cek permission
        $permissions = session()->get('permissions') ?? [];
        if (!in_array('settings.edit', $permissions)) {
            return redirect()->to('/menus')->with('error', 'Anda tidak memiliki akses');
        }

        $menu = $this->menuModel->find($id);
        
        if (!$menu) {
            return redirect()->to('/menus')->with('error', 'Menu tidak ditemukan');
        }

        $this->data['title'] = 'Edit Menu';
        $this->data['menu'] = $menu;
        // Exclude menu itu sendiri dari parent options (mencegah circular reference)
        $this->data['parents'] = $this->menuModel
            ->where('id !=', $id)
            ->where('parent_id', null)
            ->orderBy('sort_order', 'ASC')
            ->findAll();
        $this->data['permissions'] = $this->permissionModel->orderBy('name', 'ASC')->findAll();

        return view('menus/form', $this->data);
    }

    public function update($id)
    {
        // Cek permission
        $permissions = session()->get('permissions') ?? [];
        if (!in_array('settings.edit', $permissions)) {
            return redirect()->to('/menus')->with('error', 'Anda tidak memiliki akses');
        }

        $menu = $this->menuModel->find($id);
        
        if (!$menu) {
            return redirect()->to('/menus')->with('error', 'Menu tidak ditemukan');
        }

        // Validation rules
        $rules = [
            'name' => 'required|min_length[2]|max_length[100]',
            'sort_order' => 'required|numeric'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // Cegah circular reference: menu tidak bisa jadi parent dari dirinya sendiri
        $parentId = $this->request->getPost('parent_id');
        if ($parentId == $id) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Menu tidak bisa menjadi parent dari dirinya sendiri');
        }

        $data = [
            'parent_id' => $parentId ?: null,
            'name' => trim($this->request->getPost('name')),
            'icon' => trim($this->request->getPost('icon')) ?: null,
            'route' => trim($this->request->getPost('route')) ?: null,
            'permission' => trim($this->request->getPost('permission')) ?: null,
            'sort_order' => (int) $this->request->getPost('sort_order'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0
        ];

        try {
            if ($this->menuModel->update($id, $data)) {
                return redirect()->to('/menus')->with('success', 'Menu berhasil diperbarui');
            }

            return redirect()->back()->withInput()->with('error', 'Gagal update menu');
        } catch (\Exception $e) {
            log_message('error', 'Update menu error: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        // Cek permission
        $permissions = session()->get('permissions') ?? [];
        if (!in_array('settings.edit', $permissions)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Anda tidak memiliki akses'
            ])->setStatusCode(403);
        }

        try {
            $menu = $this->menuModel->find($id);
            
            if (!$menu) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Menu tidak ditemukan'
                ])->setStatusCode(404);
            }

            // Cek apakah punya anak (submenu)
            $childCount = $this->menuModel->where('parent_id', $id)->countAllResults();
            
            if ($childCount > 0) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Menu memiliki ' . $childCount . ' submenu. Hapus submenu terlebih dahulu.'
                ])->setStatusCode(400);
            }

            if ($this->menuModel->delete($id)) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Menu "' . $menu['name'] . '" berhasil dihapus'
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal menghapus menu'
            ])->setStatusCode(500);

        } catch (\Exception $e) {
            log_message('error', 'Delete menu error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
}