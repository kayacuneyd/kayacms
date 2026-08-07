<?php
namespace Theme\Config;

$routes = service('routes');

$routes->group('api/themes', ['namespace' => 'Theme\Controllers\Api'], static function ($routes) {
    $routes->get('/', 'ThemeController::index');
    $routes->get('(:num)', 'ThemeController::show/$1');
    $routes->post('activate/(:num)', 'ThemeController::activate/$1', ['filter' => 'apiAuth']);
});
