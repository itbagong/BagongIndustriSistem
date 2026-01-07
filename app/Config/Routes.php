<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Load custom route files in /routes folder
$routeFiles = glob(ROOTPATH . 'routes/*.php');

if ($routeFiles) {
    foreach ($routeFiles as $file) {
        require $file;
    }
}
