<?php

$routes->group('general-service', function ($routes) {

    // ===== VIEW =====
     $routes->get('/', 'GeneralService\FasilityController::index', [
        'filter' => 'permission:general.service'
    ]);

    $routes->get('mess', 'GeneralService\FasilityController::mess', [
        'filter' => 'permission:general.service'
    ]);

    $routes->get('workshop', 'GeneralService\FasilityController::workshop', [
        'filter' => 'permission:general.service'
    ]);

    // ===== SAVE DATA =====

    $routes->post('mess/save', 'GeneralService\MessController::save', [
        'filter' => 'permission:general.service'
    ]);

    $routes->post('workshop/save', 'GeneralService\WorkshopController::save', [
        'filter' => 'permission:general.service'
    ]);

    // ===== AJAX =====
    $routes->post('get-site-by-divisi-code', 'GeneralService\FasilityController::getSiteByDivisiCode');
    $routes->get('search-employees', 'GeneralService\FasilityController::searchEmployees');

    // detail mess data
    $routes->get('mess/detail/(:segment)', 'GeneralService\MessController::detail/$1', [
        'filter' => 'permission:general.service'
    ]);

    // detail workshop data
    $routes->get('workshop/detail/(:segment)', 'GeneralService\WorkshopController::detail/$1', [
        'filter' => 'permission:general.service'
    ]);

    // delete mess data
    $routes->post('mess/delete/(:segment)', 'GeneralService\MessController::delete/$1', [
        'filter' => 'permission:general.service'
    ]);

    // delete workshop data
    $routes->post('workshop/delete/(:segment)', 'GeneralService\WorkshopController::delete/$1', [
        'filter' => 'permission:general.service'
    ]);

    $routes->get('mess/edit/(:segment)', 'GeneralService\MessController::edit/$1', [
        'filter' => 'permission:general.service'
    ]);

    $routes->get('workshop/edit/(:segment)', 'GeneralService\WorkshopController::edit/$1', [
        'filter' => 'permission:general.service'
    ]);

    // ===== UPDATE (POST - proses form submit) =====
    $routes->post('mess/update/(:segment)', 'GeneralService\MessController::update/$1', [
        'filter' => 'permission:general.service'
    ]);

    $routes->post('workshop/update/(:segment)', 'GeneralService\WorkshopController::update/$1', [
        'filter' => 'permission:general.service'
    ]);
});
