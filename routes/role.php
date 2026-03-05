<?php
// ── Routes Roles — tambahkan di app/Config/Routes.php ────────

$routes->get( 'roles',                       'Role\RolesController::index');
$routes->post('roles/store',                 'Role\RolesController::store');
$routes->get( 'roles/edit/(:num)',           'Role\RolesController::edit/$1');
$routes->post('roles/update/(:num)',         'Role\RolesController::update/$1');
$routes->post('roles/delete/(:num)',         'Role\RolesController::delete/$1');
$routes->post('roles/toggle-active/(:num)', 'Role\RolesController::toggleActive/$1');