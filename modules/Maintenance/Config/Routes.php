<?php

namespace Maintenance\Config;

$routes = service('routes');

// Web-cron: token-protected HTTP endpoint for shared-hosting schedulers.
// Registered before any catch-all route so a two-segment path works reliably.
$routes->get('cron/run/(:segment)', 'WebCronController::run/$1', ['namespace' => 'Maintenance\\Controllers']);

$routes->group('admin/maintenance', ['namespace' => 'Maintenance\\Controllers\\Admin', 'filter' => 'sessionAuth'], static function ($routes) {
    $routes->get('/', 'MaintenanceController::index');
    $routes->post('backup', 'MaintenanceController::createBackup');
    $routes->post('delete/(:num)', 'MaintenanceController::delete/$1');
    $routes->get('download/(:num)', 'MaintenanceController::download/$1');
    $routes->post('toggle', 'MaintenanceController::toggleMaintenance');
    $routes->post('settings', 'MaintenanceController::updateSettings');
    $routes->post('cron', 'MaintenanceController::updateCron');
    $routes->post('cron/token', 'MaintenanceController::generateCronToken');
});