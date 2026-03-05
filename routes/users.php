<?php
// ── Routes User Management ────────────────────────────────────
// Tambahkan di app/Config/Routes.php

$routes->get( 'user',                       'Users\UsersController::index');
$routes->post('users/store',                 'Users\UsersController::store');
$routes->get( 'users/edit/(:num)',           'Users\UsersController::edit/$1');
$routes->post('users/update/(:num)',         'Users\UsersController::update/$1');
$routes->post('users/delete/(:num)',         'Users\UsersController::delete/$1');
$routes->post('users/toggle-active/(:num)', 'Users\UsersController::toggleActive/$1');
$routes->post('users/reset-password/(:num)','Users\UsersController::resetPassword/$1');