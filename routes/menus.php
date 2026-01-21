<?php

$routes->group('menus', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'MenuManagement\MenuController::index');
    $routes->get('new', 'MenuManagement\MenuController::new');
    $routes->post('create', 'MenuManagement\MenuController::create');
    $routes->get('edit/(:num)', 'MenuManagement\MenuController::edit/$1');
    $routes->post('update/(:num)', 'MenuManagement\MenuController::update/$1');
    $routes->delete('delete/(:num)', 'MenuManagement\MenuController::delete/$1');
});