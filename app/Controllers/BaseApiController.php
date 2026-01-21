<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Models\MenuModel;
use Config\Services;

class BaseApiController extends Controller
{
    protected $api;

    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger
    ) {
        parent::initController($request, $response, $logger);

        // API client (tetap)
        $this->api = service('apiClient');

        // 🔥 INJECT MENU SIDEBAR (INI KUNCINYA)
        if (session()->get('logged_in')) {
            $permissions = session()->get('permissions') ?? [];

            $menuModel = new MenuModel();
            $menus = $menuModel->getMenuByPermissions($permissions);

            // type-safe (tanpa error Intelephense)
            Services::renderer()->setVar('menus', $menus);
        }
    }
}
