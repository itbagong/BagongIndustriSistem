<?php

// Tambahkan routes ini ke file app/Config/Routes.php

// User Permission Management Routes
$routes->group('user-permissions', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'UserPermission\UserPermissionController::index');
    $routes->get('edit/(:num)', 'UserPermission\UserPermissionController::edit/$1');
    $routes->post('update/(:num)', 'UserPermission\UserPermissionController::update/$1');
    $routes->delete('remove/(:num)/(:num)', 'UserPermission\UserPermissionController::remove/$1/$2');
});