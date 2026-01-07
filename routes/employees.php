<?php

$routes->group('employees', ['filter' => 'auth'], function($routes){

    $routes->get('', 'Employee\EmployeeController::index');
    $routes->get('/', 'Employee\EmployeeController::index');
    $routes->get('data', 'Employee\EmployeeController::getData');
    $routes->get('statistics', 'Employee\EmployeeController::getStatistics');
    
    $routes->get('create', 'Employee\EmployeeController::create');
    $routes->post('store', 'Employee\EmployeeController::store');

    $routes->get('edit/(:num)', 'Employee\EmployeeController::edit/$1');
    $routes->post('update/(:num)', 'Employee\EmployeeController::update/$1');

    $routes->get('view/(:num)', 'Employee\EmployeeController::view/$1');
    $routes->delete('delete/(:num)', 'Employee\EmployeeController::delete/$1');
});
