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
        'filter' => 'permission:employee.view'
    ]);
    $routes->post('store', 'Employee\EmployeeController::store', [
        'filter' => 'permission:employee.view'
    ]);
    $routes->post('upload', 'Employee\EmployeeController::upload', [
        'filter' => 'permission:employee.view'
    ]);
    $routes->post('process', 'Employee\EmployeeController::process', [
        'filter' => 'permission:employee.view'
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

    $routes->post(
        'api/import-employee',
        'EmployeeImportController::import',
        [
            'filter' => 'permission:employee.view'
        ]
    );


    $routes->group('blood-type', static function ($routes) {
        $routes->get('/',               'EmployeeMaster\BloodTypeController::index');
        $routes->get('create',          'EmployeeMaster\BloodTypeController::create');
        $routes->post('store',          'EmployeeMaster\BloodTypeController::store');
        $routes->get('edit/(:segment)', 'EmployeeMaster\BloodTypeController::edit/$1');
        $routes->post('update/(:segment)', 'EmployeeMaster\BloodTypeController::update/$1');
        $routes->post('toggle/(:segment)', 'EmployeeMaster\BloodTypeController::toggleStatus/$1');
    });

    $routes->group('department', static function ($routes) {
        $routes->get('/',                    'EmployeeMaster\DepartmentController::index');
        $routes->post('store',               'EmployeeMaster\DepartmentController::store');
        $routes->post('update/(:segment)',   'EmployeeMaster\DepartmentController::update/$1');
        $routes->post('toggle/(:segment)',   'EmployeeMaster\DepartmentController::toggleStatus/$1');
    });

    $routes->group('division', static function ($routes) {
        $routes->get('/',                    'EmployeeMaster\DivisionController::index');
        $routes->post('store',               'EmployeeMaster\DivisionController::store');
        $routes->post('update/(:segment)',   'EmployeeMaster\DivisionController::update/$1');
        $routes->post('toggle/(:segment)',   'EmployeeMaster\DivisionController::toggleStatus/$1');
    });

    $routes->group('employee-status', static function ($routes) {
        $routes->get('/',                    'EmployeeMaster\EmployeeStatusController::index');
        $routes->post('store',               'EmployeeMaster\EmployeeStatusController::store');
        $routes->post('update/(:segment)',   'EmployeeMaster\EmployeeStatusController::update/$1');
        $routes->post('toggle/(:segment)',   'EmployeeMaster\EmployeeStatusController::toggleStatus/$1');
    });

    $routes->group('employment-status', static function ($routes) {
        $routes->get('/',                    'EmployeeMaster\EmploymentStatusController::index');
        $routes->post('store',               'EmployeeMaster\EmploymentStatusController::store');
        $routes->post('update/(:segment)',   'EmployeeMaster\EmploymentStatusController::update/$1');
        $routes->post('toggle/(:segment)',   'EmployeeMaster\EmploymentStatusController::toggleStatus/$1');
    });

    $routes->group('emergency-contact-relation', static function ($routes) {
        $routes->get('/',                    'EmployeeMaster\EmergencyContactRelationController::index');
        $routes->post('store',               'EmployeeMaster\EmergencyContactRelationController::store');
        $routes->post('update/(:segment)',   'EmployeeMaster\EmergencyContactRelationController::update/$1');
        $routes->post('toggle/(:segment)',   'EmployeeMaster\EmergencyContactRelationController::toggleStatus/$1');
    });

    $routes->group('group', static function ($routes) {
        $routes->get('/',                    'EmployeeMaster\GroupController::index');
        $routes->post('store',               'EmployeeMaster\GroupController::store');
        $routes->post('update/(:segment)',   'EmployeeMaster\GroupController::update/$1');
        $routes->post('toggle/(:segment)',   'EmployeeMaster\GroupController::toggleStatus/$1');
    });

    $routes->group('gender', static function ($routes) {
        $routes->get('/',                    'EmployeeMaster\GenderController::index');
        $routes->post('store',               'EmployeeMaster\GenderController::store');
        $routes->post('update/(:segment)',   'EmployeeMaster\GenderController::update/$1');
        $routes->post('toggle/(:segment)',   'EmployeeMaster\GenderController::toggleStatus/$1');
    });

    $routes->group('religion', static function ($routes) {
        $routes->get('/',                    'EmployeeMaster\ReligionController::index');
        $routes->post('store',               'EmployeeMaster\ReligionController::store');
        $routes->post('update/(:segment)',   'EmployeeMaster\ReligionController::update/$1');
        $routes->post('toggle/(:segment)',   'EmployeeMaster\ReligionController::toggleStatus/$1');
    });

    $routes->group('last-education', static function ($routes) {
        $routes->get('/',                    'EmployeeMaster\LastEducationController::index');
        $routes->post('store',               'EmployeeMaster\LastEducationController::store');
        $routes->post('update/(:segment)',   'EmployeeMaster\LastEducationController::update/$1');
        $routes->post('toggle/(:segment)',   'EmployeeMaster\LastEducationController::toggleStatus/$1');
    });

    $routes->group('site', static function ($routes) {
        $routes->get('/',                    'EmployeeMaster\SiteController::index');
        $routes->post('store',               'EmployeeMaster\SiteController::store');
        $routes->post('update/(:segment)',   'EmployeeMaster\SiteController::update/$1');
        $routes->post('toggle/(:segment)',   'EmployeeMaster\SiteController::toggleStatus/$1');
    });
});
