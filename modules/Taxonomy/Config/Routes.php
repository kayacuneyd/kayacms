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
