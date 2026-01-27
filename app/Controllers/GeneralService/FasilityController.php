<?php

namespace App\Controllers\GeneralService;
use App\Controllers\BaseController;
use App\Models\FasilityModel;
use App\Models\EmployeeRecruitmentModel;
use App\Models\SiteModel;

class FasilityController extends BaseController {

    public function index() {
        return view('general_service/mess/index', $this->data);
    }

    public function workshop() {
        return view('general_service/workshop/index', $this->data);
    }

    public function getSiteByDivisiCode() {
        if ($this->request->isAJAX()) {
            $divisiId = $this->request->getPost('divisi_id');
            $siteModel = new SiteModel();
            
            $sites = $siteModel->where('business_unit_id', $divisiId)
                               ->where('is_active', 1)
                               ->orderBy('name', 'ASC')
                               ->findAll();

            return $this->response->setJSON($sites);
        }
    }
    public function searchEmployees() {
    $search = strtolower(trim(
        $this->request->getGet('search') ?? 
        $this->request->getPost('search') ?? 
        ''
    ));

    if (strlen($search) < 2) {
        return $this->response->setJSON([]);
    }

    try {
        // ✅ Langsung pakai query builder
        $db = \Config\Database::connect('pg');
        
        $builder = $db->table('employees_recruitment');
        $builder->select('employee_name, employee_number');
        $builder->groupStart()
            ->like('LOWER(employee_name)', $search)
            ->orLike('LOWER(employee_number)', $search)
        ->groupEnd();
        $builder->limit(20);
        
        $results = $builder->get()->getResultArray();

        return $this->response->setJSON($results);

    } catch (\Throwable $e) {
        log_message('error', 'Employee Search Error: ' . $e->getMessage());
        return $this->response->setJSON([
            'error' => true,
            'message' => $e->getMessage()
        ]);
    }
}
}