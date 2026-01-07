<?php

namespace Config\Api;

use CodeIgniter\Config\BaseConfig;

class Api extends BaseConfig
{
    public string $baseUrl;

    public function __construct()
    {
        parent::__construct();

        $this->baseUrl = rtrim(env('API_BASE_URL'), '/');
    }
}
