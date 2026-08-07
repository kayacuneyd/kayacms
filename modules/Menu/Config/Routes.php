<?php
namespace Menu\Config;

$routes = service('routes');

$routes->group('api/menus', ['namespace' => 'Menu\Controllers\Api'], static function ($routes) {
    $routes->get('/', 'MenuController::index');
    $routes->get('(:num)', 'MenuController::show/$1');
    $routes->post('/', 'MenuController::create', ['filter' => 'apiAuth']);
    $routes->put('(:num)', 'MenuController::update/$1', ['filter' => 'apiAuth']);
    $routes->delete('(:num)', 'MenuController::delete/$1', ['filter' => 'apiAuth']);
});
