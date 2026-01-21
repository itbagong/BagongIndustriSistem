<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    public function __construct()
    {
        // Manual auth check
        if (!session()->get('logged_in')) {
            header('Location: /login');
            exit;
        }
    }

    public function index()
    {
        // PERBAIKAN DISINI:
        // Jangan timpa $this->data, tapi gabungkan (merge)
        
        $newData = [
            'title'     => 'Dashboard',
            'user_name' => session()->get('user_name'),
            'user_email'=> session()->get('user_email'),
            'user_role' => session()->get('user_role'),
        ];

        // Gabungkan data lama (menus) dengan data baru
        $this->data = array_merge($this->data, $newData);

        return view('dashboard/index', $this->data);
    }
}