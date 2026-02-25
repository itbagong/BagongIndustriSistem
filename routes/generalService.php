<?php

$routes->group('general-service', ['filter' => 'permission:general.service'], function ($routes) {

    // =========================
    // VIEW UMUM
    // =========================
    $routes->get('/', 'GeneralService\FasilityController::index');
    $routes->get('mess', 'GeneralService\FasilityController::mess');
    $routes->get('workshop', 'GeneralService\FasilityController::workshop');
    $routes->get('asset', 'GeneralService\FasilityController::asset');

    // =========================
    // AJAX
    // =========================
    $routes->group('ajax', function ($routes) {
        $routes->post('get-site-by-divisi-code', 'GeneralService\FasilityController::getSiteByDivisiCode');
        $routes->get('search-employees', 'GeneralService\FasilityController::searchEmployees');
    });

    // =========================
    // MESS
    // =========================
    $routes->group('mess', function ($routes) {
        $routes->post('save', 'GeneralService\MessController::save');
        $routes->get('detail/(:segment)', 'GeneralService\MessController::detail/$1');
        $routes->get('repair-detail/(:segment)', 'GeneralService\WorkshopController::getRepairDetail/$1');
        $routes->get('repair-list/(:segment)', 'GeneralService\MessController::getRepairList/$1');
        $routes->get('edit/(:segment)', 'GeneralService\MessController::edit/$1');
        $routes->put('update/(:segment)', 'GeneralService\MessController::update/$1');
        $routes->post('delete/(:segment)', 'GeneralService\MessController::delete/$1');
    });

    // =========================
    // WORKSHOP
    // =========================
    $routes->group('workshop', function ($routes) {
        $routes->post('save', 'GeneralService\WorkshopController::save');
        $routes->get('detail/(:segment)', 'GeneralService\WorkshopController::detail/$1');
        $routes->get('repair-detail/(:segment)', 'GeneralService\WorkshopController::getRepairDetail/$1');
        $routes->get('edit/(:segment)', 'GeneralService\WorkshopController::edit/$1');
        $routes->post('update/(:segment)', 'GeneralService\WorkshopController::update/$1');
        $routes->post('delete/(:segment)', 'GeneralService\WorkshopController::delete/$1');
    });

    // =========================
    // REPAIR REQUEST - LENGKAP
    // =========================
    $routes->group('repair-request', function ($routes) {
        // List & Filter
        $routes->get('/', 'GeneralService\RepairRequestController::index');
        $routes->get('list', 'GeneralService\RepairRequestController::index'); // Alias
        
        // Create
        $routes->get('create', 'GeneralService\RepairRequestController::create');
        $routes->post('store', 'GeneralService\RepairRequestController::store');
        
        // Detail
        $routes->get('detail/(:segment)', 'GeneralService\RepairRequestController::detail/$1');
        
        // Edit
        $routes->get('edit/(:segment)', 'GeneralService\RepairRequestController::edit/$1');
        $routes->post('update/(:segment)', 'GeneralService\RepairRequestController::update/$1');
        $routes->put('update/(:segment)', 'GeneralService\RepairRequestController::update/$1');
        
        // Delete (Soft Delete)
        $routes->delete('delete/(:segment)', 'GeneralService\RepairRequestController::delete/$1');
        $routes->post('delete/(:segment)', 'GeneralService\RepairRequestController::delete/$1');
        
        // Status Management
        $routes->post('update-status/(:segment)', 'GeneralService\RepairRequestController::updateStatus/$1');
        $routes->post('approve/(:segment)', 'GeneralService\RepairRequestController::approve/$1');
        $routes->post('reject/(:segment)', 'GeneralService\RepairRequestController::reject/$1');
        $routes->post('cancel/(:segment)', 'GeneralService\RepairRequestController::cancel/$1');
        $routes->post('start/(:segment)', 'GeneralService\RepairRequestController::start/$1');
        $routes->post('complete/(:segment)', 'GeneralService\RepairRequestController::complete/$1');
        
        // Progress Update
        $routes->post('update-progress/(:segment)', 'GeneralService\RepairRequestController::updateProgress/$1');
        
        // File Upload
        $routes->post('upload-progress-photo/(:segment)', 'GeneralService\RepairRequestController::uploadProgressPhoto/$1');
        $routes->post('upload-completion-photo/(:segment)', 'GeneralService\RepairRequestController::uploadCompletionPhoto/$1');
        
        // Rating & Feedback
        $routes->post('rate/(:segment)', 'GeneralService\RepairRequestController::rate/$1');
        
        // Reports & Statistics
        $routes->get('statistics', 'GeneralService\RepairRequestController::statistics');
        $routes->get('report', 'GeneralService\RepairRequestController::report');
        $routes->get('export', 'GeneralService\RepairRequestController::export');
        
        // Bulk Actions
        $routes->post('bulk-update-status', 'GeneralService\RepairRequestController::bulkUpdateStatus');
        $routes->post('bulk-delete', 'GeneralService\RepairRequestController::bulkDelete');
        
        // Get by Aset (untuk ditampilkan di halaman detail Mess/Workshop)
        $routes->get('by-aset/(:segment)/(:segment)', 'GeneralService\RepairRequestController::getByAset/$1/$2');
        // Contoh: repair-request/by-aset/Mess/123
        
        // Urgent & Overdue
        $routes->get('urgent', 'GeneralService\RepairRequestController::urgent');
        $routes->get('overdue', 'GeneralService\RepairRequestController::overdue');
        
        // AJAX Endpoints
        $routes->group('ajax', function ($routes) {
            $routes->post('get-aset-details', 'GeneralService\RepairRequestController::getAsetDetails');
            $routes->get('search', 'GeneralService\RepairRequestController::search');
        });

        $routes->post('approve/(:segment)', 'GeneralService\RepairRequestController::approve/$1');
        $routes->post('reject/(:segment)', 'GeneralService\RepairRequestController::reject/$1');
        $routes->post('start/(:segment)', 'GeneralService\RepairRequestController::start/$1');
        $routes->post('complete/(:segment)', 'GeneralService\RepairRequestController::complete/$1');
        $routes->post('cancel/(:segment)', 'GeneralService\RepairRequestController::cancel/$1');
        $routes->post('update-progress/(:segment)', 'GeneralService\RepairRequestController::updateProgress/$1');

        $routes->get('repair-list/(:alpha)/(:segment)', 'GeneralService\RepairRequestController::repairList/$1/$2');
    });
});