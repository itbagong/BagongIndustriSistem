<?php

$routes->group('general-service', function ($routes) {

    // ===== VIEW =====
    $routes->get('mess', 'GeneralService\FasilityController::index', [
        'filter' => 'permission:general.service'
    ]);

    $routes->get('workshop', 'GeneralService\FasilityController::workshop', [
        'filter' => 'permission:general.service'
    ]);

    // ===== AJAX =====
    $routes->post('get-site-by-divisi-code', 'GeneralService\FasilityController::getSiteByDivisiCode');
    $routes->post('search-employees', 'GeneralService\ApiGetEmployeeController::searchEmployees');
});
