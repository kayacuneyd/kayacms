<?php

namespace Menu\Config;

$routes = service('routes');

// API Routes
$routes->group('api/menus', ['namespace' => 'Menu\Controllers\Api'], static function ($routes) {
    $routes->get('/', 'MenuController::index');
    $routes->get('(:num)', 'MenuController::show/$1');
    $routes->post('/', 'MenuController::create', ['filter' => 'apiAuth']);
    $routes->put('(:num)', 'MenuController::update/$1', ['filter' => 'apiAuth']);
    $routes->delete('(:num)', 'MenuController::delete/$1', ['filter' => 'apiAuth']);
});

// Admin Routes
$routes->group('admin/menus', ['namespace' => 'Menu\Controllers\Admin'], static function ($routes) {
    $routes->get('/', 'MenuAdminController::index', ['filter' => 'sessionAuth']);
    $routes->get('create', 'MenuAdminController::create', ['filter' => 'sessionAuth']);
    $routes->post('store', 'MenuAdminController::store', ['filter' => 'sessionAuth']);
    $routes->get('edit/(:num)', 'MenuAdminController::edit/$1', ['filter' => 'sessionAuth']);
    $routes->post('update/(:num)', 'MenuAdminController::update/$1', ['filter' => 'sessionAuth']);
    $routes->post('delete/(:num)', 'MenuAdminController::delete/$1', ['filter' => 'sessionAuth']);

    // Menu items
    $routes->post('items/store/(:num)', 'MenuAdminController::storeItem/$1', ['filter' => 'sessionAuth']);
    $routes->post('items/update/(:num)', 'MenuAdminController::updateItem/$1', ['filter' => 'sessionAuth']);
    $routes->post('items/delete/(:num)', 'MenuAdminController::deleteItem/$1', ['filter' => 'sessionAuth']);
});
