<?php

namespace App\Controllers\GeneralService;
use App\Controllers\BaseController;
use App\Models\FasilityModel;
use Faker\Provider\Base;

class WorkshopController extends BaseController{

    public function index(){
        
        return view('general_service/workshop/index', $this->data);
    }

}