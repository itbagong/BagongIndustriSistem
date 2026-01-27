<?php

$routes->group('general-service', function ($routes) {

    // ===== VIEW =====
    $routes->get('mess', 'GeneralService\FasilityController::index', [
        'filter' => 'permission:general.service'
    ]);

    $routes->get('workshop', 'GeneralService\FasilityController::workshop', [
        'filter' => 'permission:general.service'
    ]);

    // ===== SAVE DATA =====

    $routes->post('mess/save', 'GeneralService\FasilityController::saveMess', [
        'filter' => 'permission:general.service'
    ]);

    $routes->post('workshop/save', 'GeneralService\WorkshopController::save', [
        'filter' => 'permission:general.service'
    ]);

    // ===== AJAX =====
    $routes->post('get-site-by-divisi-code', 'GeneralService\FasilityController::getSiteByDivisiCode');
    $routes->get('search-employees', 'GeneralService\FasilityController::searchEmployees');
});
