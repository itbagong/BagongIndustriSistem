<?php

namespace App\Controllers\GeneralService;
use App\Controllers\BaseController;
use App\Models\FasilityModel;
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
}