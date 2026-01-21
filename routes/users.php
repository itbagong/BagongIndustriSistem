<?php

$routes->group('settings/privileges', ['filter' => 'auth'], function($routes) {
    // View halaman
    $routes->get('', 'Settings\PrivilegeController::index');
    
    // AJAX endpoints
    $routes->get('data', 'Settings\PrivilegeController::getData');
    $routes->get('statistics', 'Settings\PrivilegeController::getStatistics');
    $routes->get('view/(:segment)', 'Settings\PrivilegeController::view/$1');
    
    // CRUD
    $routes->post('store', 'Settings\PrivilegeController::store');
    $routes->post('update/(:segment)', 'Settings\PrivilegeController::update/$1');
    $routes->delete('delete/(:segment)', 'Settings\PrivilegeController::delete/$1');
    
    // User assignments
    $routes->get('user-privileges', 'Settings\PrivilegeController::getUserPrivileges');
    $routes->post('assign', 'Settings\PrivilegeController::assign');
    $routes->get('users', 'Settings\PrivilegeController::getUsers');
});