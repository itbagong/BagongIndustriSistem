<?php

$routes->group('employees', function ($routes) {

    // ===== VIEW =====
    $routes->get('/', 'Employee\EmployeeController::index', [
        'filter' => 'permission:employee.view'
    ]);
    $routes->get('data', 'Employee\EmployeeController::getData', [
        'filter' => 'permission:employee.view'
    ]);
    $routes->get('statistics', 'Employee\EmployeeController::getStatistics', [
        'filter' => 'permission:employee.view'
    ]);
    $routes->get('view/(:num)', 'Employee\EmployeeController::view/$1', [
        'filter' => 'permission:employee.view'
    ]);

    // ===== CREATE =====
    $routes->get('create', 'Employee\EmployeeController::create', [
        'filter' => 'permission:employee.create'
    ]);
    $routes->post('store', 'Employee\EmployeeController::store', [
        'filter' => 'permission:employee.create'
    ]);

    // ===== UPDATE =====
    $routes->get('edit/(:num)', 'Employee\EmployeeController::edit/$1', [
        'filter' => 'permission:employee.update'
    ]);
    $routes->post('update/(:num)', 'Employee\EmployeeController::update/$1', [
        'filter' => 'permission:employee.update'
    ]);

    // ===== DELETE =====
    $routes->delete('delete/(:num)', 'Employee\EmployeeController::delete/$1', [
        'filter' => 'permission:employee.delete'
    ]);

    $routes->get('test-db', 'Employee\EmployeeController::testDb', [
        'filter' => 'permission:employee.view'
    ]);

    $routes->post('api/import-employee', 'EmployeeImportController::import', [
            'filter' => 'permission:employee.view'
        ]
    );
});
