<?php
namespace Media\Config;

$routes = service('routes');

// API Routes
$routes->group('api/media', ['namespace' => 'Media\Controllers\Api'], static function ($routes) {
    $routes->get('/', 'MediaController::index');
    $routes->get('(:num)', 'MediaController::show/$1');
    $routes->post('upload', 'MediaController::upload', ['filter' => 'apiAuth']);
    $routes->put('(:num)', 'MediaController::update/$1', ['filter' => 'apiAuth']);
    $routes->delete('(:num)', 'MediaController::delete/$1', ['filter' => 'apiAuth']);
});
