<?php

$routes->get('/', 'Auth\LoginController::index');
$routes->get('login', 'Auth\LoginController::index');
$routes->post('login', 'Auth\LoginController::process');
$routes->get('logout', 'Auth\LoginController::logout');

$routes->group('', ['filter' => 'auth'], function($routes) {

    $routes->get('dashboard', 'DashboardController::index');
});
