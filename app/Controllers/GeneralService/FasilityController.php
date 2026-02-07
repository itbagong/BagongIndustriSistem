<?php

namespace App\Controllers\GeneralService;
use App\Controllers\BaseController;
use App\Models\FasilityModel;
use App\Models\EmployeeRecruitmentModel;
use App\Controllers\BaseApiController;
use App\Models\MessModel;
use App\Models\WorkshopModel;
use App\Models\DivisionModel;
use App\Models\SiteModel;

class FasilityController extends BaseApiController {
    protected $data = [];
    protected $messModel;
    protected $workshopModel;
    protected $divisiModel;

    public function __construct()
    {
        $this->messModel     = new MessModel();
        $this->workshopModel = new WorkshopModel();
        $this->divisiModel   = new DivisionModel();
    }

    public function index()
    {
        // ================= DIVISI =================
        $this->data['divisi_list'] = $this->divisiModel->findAll();

        // ================= MESS =================
        $messBuilder = $this->messModel
            ->select('mess_data.*, divisions.name AS divisi_name')
            ->join('divisions', 'divisions.id = mess_data.divisi_id', 'left')
            ->where('mess_data.is_deleted', false);

        if ($this->request->getGet('mess_divisi')) {
            $messBuilder->where('mess_data.divisi_id', $this->request->getGet('mess_divisi'));
        }

        if ($this->request->getGet('mess_job_site')) {
            $messBuilder->where('mess_data.site_id', $this->request->getGet('mess_job_site'));
        }

        if ($this->request->getGet('mess_status')) {
            $messBuilder->where('mess_data.status_kepemilikan', $this->request->getGet('mess_status'));
        }

        if ($this->request->getGet('mess_search')) {
            $search = $this->request->getGet('mess_search');
            $messBuilder->groupStart()
                ->like('mess_data.nama_karyawan', $search)
                ->orLike('mess_data.nik', $search)
                ->groupEnd();
        }

        // Total DULU (pakai countAllResults sebelum paginate)
        $this->data['total_mess'] = $messBuilder->countAllResults(false); // false = jangan reset builder

        // Baru paginate
        $this->data['mess_data']  = $messBuilder->paginate(10, 'mess');
        $this->data['pager_mess'] = $this->messModel->pager;


        // ================= WORKSHOP =================
        $workshopBuilder = $this->workshopModel
            ->select('workshop.*, divisions.name AS divisi_name')
            ->join('divisions', 'divisions.id = workshop.divisi_id', 'left')
            ->where('workshop.is_deleted', false);

        if ($this->request->getGet('workshop_divisi')) {
            $workshopBuilder->where('workshop.divisi_id', $this->request->getGet('workshop_divisi'));
        }

        if ($this->request->getGet('workshop_job_site')) {
            $workshopBuilder->where('workshop.site_id', $this->request->getGet('workshop_job_site'));
        }

        if ($this->request->getGet('workshop_status')) {
            $workshopBuilder->where('workshop.status_workshop', $this->request->getGet('workshop_status'));
        }

        if ($this->request->getGet('workshop_search')) {
            $search = $this->request->getGet('workshop_search');
            $workshopBuilder->groupStart()
                ->like('workshop.name_karyawan', $search)
                ->orLike('workshop.nik', $search)
                ->groupEnd();
        }

        // Total DULU
        $this->data['total_workshop'] = $workshopBuilder->countAllResults(false);

        // Baru paginate
        $this->data['workshop_data']  = $workshopBuilder->paginate(10, 'workshop');
        $this->data['pager_workshop'] = $this->workshopModel->pager;

        return view('general_service/index', $this->data);
    }

    public function mess() {
        $this->data['divisi_list'] = $this->divisiModel->findAll();
        return view('general_service/mess/index', $this->data);
    }

    public function workshop() {
        $this->data['divisi_list'] = $this->divisiModel->findAll();
        return view('general_service/workshop/index', $this->data);
    }
    
    public function getSiteByDivisiCode() {
        if ($this->request->isAJAX()) {
            $divisiId = $this->request->getPost('divisi_id');
            $siteModel = new SiteModel();
            
            $sites = $siteModel->where('business_unit_id', $divisiId)
                               ->where('is_active', true)
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