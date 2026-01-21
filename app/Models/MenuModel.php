<?php

namespace App\Models;

use CodeIgniter\Model;

class MenuModel extends Model
{
    protected $table            = 'menus';
    protected $primaryKey       = 'id';
    
    // --- TAMBAHAN PENTING 1: Allowed Fields ---
    // Wajib ada agar fungsi insert() dan update() di Controller bisa jalan
    protected $allowedFields    = [
        'name', 
        'icon', 
        'route', 
        'permission', 
        'parent_id', 
        'sort_order', 
        'is_active'
    ];

    // Opsional: Jika tabel Anda punya created_at & updated_at
    // protected $useTimestamps = true; 

    /**
     * Ambil menu berdasarkan permission user (Untuk Sidebar)
     */
    public function getMenuByPermissions(array $permissions): array
    {
        $builder = $this->db->table($this->table)
            ->where('is_active', 1)
            ->orderBy('sort_order', 'ASC') // Urutkan sort_order dulu
            ->orderBy('parent_id', 'ASC');

        // Logika Filter:
        // Ambil menu yang (Permission-nya NULL/Public) ATAU (Permission-nya dimiliki User)
        $builder->groupStart()
            ->where('permission', NULL)
            ->orWhere('permission', ''); // Handle string kosong juga sebagai public
        
        if (! empty($permissions)) {
            $builder->orWhereIn('permission', $permissions);
        }
        $builder->groupEnd();

        $menus = $builder->get()->getResultArray();

        return $this->buildTree($menus);
    }

    /**
     * --- TAMBAHAN PENTING 2: Get Parents ---
     * Dipakai di Controller untuk mengisi dropdown "Induk Menu"
     */
    public function getParents()
    {
        // Ambil menu yang parent_id-nya 0 atau NULL (Menu Level Utama)
        return $this->where('parent_id', 0)
                    ->orWhere('parent_id', NULL)
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();
    }

    /**
     * Helper: Build menu tree (parent-child)
     */
    private function buildTree(array $menus, $parentId = null): array
    {
        $branch = [];

        foreach ($menus as $menu) {
            // Normalisasi: Database kadang simpan NULL, kadang 0. Anggap sama.
            $menuParentId = $menu['parent_id'] ?? 0;
            $currentParentId = $parentId ?? 0;

            if ($menuParentId == $currentParentId) {
                $children = $this->buildTree($menus, $menu['id']);
                
                // Jika punya anak, masukkan ke key 'children'
                // Jika tidak, set array kosong (opsional, biar view gak error cek count)
                if ($children) {
                    $menu['children'] = $children;
                } else {
                    $menu['children'] = [];
                }
                
                $branch[] = $menu;
            }
        }

        return $branch;
    }
}