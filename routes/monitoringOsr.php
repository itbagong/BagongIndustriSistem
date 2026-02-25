<?php

$routes->group('general-service', ['filter' => 'permission:general.service'], function ($routes) {
    $routes->get('monitoring-osr', 'GeneralService\MonitoringOsr::index', ['as' => 'monitoring-osr']);
    $routes->get('monitoring-osr/ajax', 'GeneralService\MonitoringOsr::ajax', ['as' => 'monitoring-osr-ajax']);
});