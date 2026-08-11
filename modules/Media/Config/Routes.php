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

// Admin Routes
$routes->group('admin/media', ['namespace' => 'Media\Controllers\Admin'], static function ($routes) {
    $routes->get('/', 'MediaAdminController::index', ['filter' => 'sessionAuth']);
    $routes->get('upload', 'MediaAdminController::upload', ['filter' => 'sessionAuth']);
    $routes->post('store', 'MediaAdminController::store', ['filter' => 'sessionAuth']);
    $routes->get('edit/(:num)', 'MediaAdminController::edit/$1', ['filter' => 'sessionAuth']);
    $routes->post('update/(:num)', 'MediaAdminController::update/$1', ['filter' => 'sessionAuth']);
    $routes->post('resize/(:num)', 'MediaAdminController::resize/$1', ['filter' => 'sessionAuth']);
    $routes->post('rotate/(:num)', 'MediaAdminController::rotate/$1', ['filter' => 'sessionAuth']);
    $routes->post('crop/(:num)', 'MediaAdminController::crop/$1', ['filter' => 'sessionAuth']);
    $routes->delete('(:num)', 'MediaAdminController::delete/$1', ['filter' => 'sessionAuth']);
    $routes->post('delete/(:num)', 'MediaAdminController::delete/$1', ['filter' => 'sessionAuth']);
    $routes->get('picker', 'MediaAdminController::picker', ['filter' => 'sessionAuth']);
    $routes->get('queue', 'MediaAdminController::queue', ['filter' => 'sessionAuth']);
    $routes->post('queue/run', 'MediaAdminController::runQueue', ['filter' => 'sessionAuth']);
    $routes->post('queue/retry/(:num)', 'MediaAdminController::retryJob/$1', ['filter' => 'sessionAuth']);

    // Folder management
    $routes->get('folders/create', 'MediaAdminController::createFolder', ['filter' => 'sessionAuth']);
    $routes->post('folders/store', 'MediaAdminController::storeFolder', ['filter' => 'sessionAuth']);
    $routes->post('folders/delete/(:num)', 'MediaAdminController::deleteFolder/$1', ['filter' => 'sessionAuth']);
});