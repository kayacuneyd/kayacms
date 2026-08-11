<?php

namespace Taxonomy\Config;

$routes = service('routes');

// API Routes
$routes->group('api/terms', ['namespace' => 'Taxonomy\Controllers\Api'], static function ($routes) {
    $routes->get('/', 'TermController::index');
    $routes->get('(:num)', 'TermController::show/$1');
    $routes->post('/', 'TermController::create', ['filter' => 'apiAuth']);
    $routes->put('(:num)', 'TermController::update/$1', ['filter' => 'apiAuth']);
    $routes->delete('(:num)', 'TermController::delete/$1', ['filter' => 'apiAuth']);

    // Content-term relationships
    $routes->post('attach/(:num)', 'TermController::attachToContent/$1', ['filter' => 'apiAuth']);
    $routes->get('content/(:num)', 'TermController::contentTerms/$1');
});

// Admin Routes
$routes->group('admin/taxonomy', ['namespace' => 'Taxonomy\Controllers\Admin'], static function ($routes) {
    $routes->get('/', 'TermAdminController::index', ['filter' => 'sessionAuth']);
    $routes->get('create', 'TermAdminController::create', ['filter' => 'sessionAuth']);
    $routes->post('store', 'TermAdminController::store', ['filter' => 'sessionAuth']);
    $routes->get('edit/(:num)', 'TermAdminController::edit/$1', ['filter' => 'sessionAuth']);
    $routes->post('update/(:num)', 'TermAdminController::update/$1', ['filter' => 'sessionAuth']);
    $routes->post('delete/(:num)', 'TermAdminController::delete/$1', ['filter' => 'sessionAuth']);
});
