<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class BaseApiController extends Controller
{
    protected $api;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->api = service('apiClient'); // gunakan service yang dibuat
    }
}
