<?php

namespace Theme\Config;

$routes = service('routes');

$routes->group('api/themes', ['namespace' => 'Theme\Controllers\Api'], static function ($routes) {
    $routes->get('/', 'ThemeController::index');
    $routes->get('(:num)', 'ThemeController::show/$1');
    $routes->post('activate/(:num)', 'ThemeController::activate/$1', ['filter' => 'apiAuth']);
});

// Admin Routes
$routes->group('admin/themes', ['namespace' => 'Theme\Controllers\Admin'], static function ($routes) {
    $routes->get('/', 'ThemeAdminController::index', ['filter' => 'sessionAuth']);
    $routes->post('activate/(:num)', 'ThemeAdminController::activate/$1', ['filter' => 'sessionAuth']);
    $routes->get('config/(:num)', 'ThemeAdminController::config/$1', ['filter' => 'sessionAuth']);
    $routes->post('config/(:num)', 'ThemeAdminController::saveConfig/$1', ['filter' => 'sessionAuth']);
});
