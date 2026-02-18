<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\BusinessUnitModel;
use App\Models\SiteModel;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    protected $request;
    protected $helpers = ['url', 'form', 'text', 'api_response']; // Pastikan helper url dimuat
    
    // Variabel global agar bisa diakses di semua view
    protected $data = []; 

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // Load Session
        $session = \Config\Services::session();
        $this->data['session'] = $session;

        // Jika user sudah login, generate menu
        if ($session->get('logged_in')) {
            $this->data['menus'] = $this->generateSidebarMenu();

            // divisi (bussiness unit)
            $businessUnitModel = new BusinessUnitModel();
            $this->data['divisi_list'] = $businessUnitModel->where('is_deleted', 0)->orderBy('name', 'ASC')->findAll();

            // job site (sites)
            $sitesModel = new SiteModel();
            $this->data['job_site_list'] = $sitesModel->where('is_active', 1)->orderBy('name', 'ASC')->findAll();
        } else {
            $this->data['menus'] = [];
        }
    }

    /**
     * Fungsi Utama generate Menu berdasarkan Permission
     */
    private function generateSidebarMenu()
    {
        $db = \Config\Database::connect();
        $session = session();
        
        // 1. Ambil permission user saat ini (dari session login)
        $userPermissions = $session->get('permissions') ?? [];

        // 2. Ambil semua menu AKTIF dari database, urutkan berdasarkan sort_order
        $rawMenus = $db->table('menus')
                       ->where('is_active', 1)
                       ->orderBy('sort_order', 'ASC')
                       ->get()
                       ->getResultArray();

        $menuTree = [];
        $references = [];

        // 3. Proses Filtering & Nesting
        foreach ($rawMenus as $menu) {
            
            // --- LOGIKA CEK PERMISSION ---
            // Jika kolom permission di DB tidak kosong (NOT NULL/Empty), cek apakah user punya
            if (!empty($menu['permission']) && !in_array($menu['permission'], $userPermissions)) {
                // Skip jika user tidak punya izin
                continue; 
            }

            // Siapkan container untuk children
            $menu['children'] = [];

            // Simpan referensi array berdasarkan ID untuk mapping parent-child
            // Kita pakai referensi (&) agar array asli berubah saat child ditambahkan
            $references[$menu['id']] = &$menu; // Warning: Hati-hati dengan references di loop, tapi aman di sini utk logic ini

            // Jika dia Parent (parent_id NULL atau 0)
            if (empty($menu['parent_id'])) {
                $menuTree[$menu['id']] = &$menu; // Masukkan ke root
            } 
            // Jika dia Child
            else {
                // Cek apakah Bapaknya ada di list yang sudah di-filter?
                // (Kalau bapaknya kena filter permission, anaknya otomatis hilang)
                if (isset($references[$menu['parent_id']])) {
                    $references[$menu['parent_id']]['children'][] = &$menu;
                }
            }
            
            // Unset reference temp untuk iterasi ini (safety practice)
            unset($menu);
        }

        return $menuTree;
    }
}