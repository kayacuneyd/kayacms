<?php
namespace Content\Config;

$routes = service('routes');

// API Routes
$routes->group('api/content', ['namespace' => 'Content\Controllers\Api'], static function ($routes) {
    $routes->get('/', 'ContentController::index');
    $routes->get('(:segment)', 'ContentController::show/$1');
    $routes->post('/', 'ContentController::create', ['filter' => 'apiAuth']);
    $routes->put('(:num)', 'ContentController::update/$1', ['filter' => 'apiAuth']);
    $routes->delete('(:num)', 'ContentController::delete/$1', ['filter' => 'apiAuth']);
});

// Admin Routes
$routes->group('admin/content', ['namespace' => 'Content\Controllers\Admin'], static function ($routes) {
    $routes->get('/', 'ContentAdminController::index', ['filter' => 'sessionAuth']);
    $routes->get('create', 'ContentAdminController::create', ['filter' => 'sessionAuth']);
    $routes->get('edit/(:num)', 'ContentAdminController::edit/$1', ['filter' => 'sessionAuth']);
});
