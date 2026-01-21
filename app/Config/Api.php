<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Api extends BaseConfig
{
    public string $baseUrl;

    public array $endpoints = [
        'login'      => '/auth/login',
        'users'    => '/user',
        'employees'  => '/employees',
    ];

    public function __construct()
    {
        parent::__construct();

        // ambil dari .env (lebih fleksibel)
        $this->baseUrl = rtrim(env('API_BASE_URL'), '/');
    }
}
