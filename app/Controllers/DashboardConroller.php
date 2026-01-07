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
        $data = [
            'title' => 'Dashboard',
            'user_name' => session()->get('user_name'),
            'user_email' => session()->get('user_email'),
            'user_role' => session()->get('user_role'),
        ];

        return view('dashboard/index', $data);
    }
}