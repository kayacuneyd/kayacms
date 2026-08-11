<?php

namespace Content\Config;

$routes = service('routes');

// API Routes
$routes->group('api/content', ['namespace' => 'Content\\Controllers\\Api'], static function ($routes) {
    $routes->get('/', 'ContentController::index');
    $routes->get('search', 'ContentController::search');
    $routes->get('(:segment)', 'ContentController::show/$1');
    $routes->post('/', 'ContentController::create', ['filter' => 'apiAuth']);
    $routes->put('(:num)', 'ContentController::update/$1', ['filter' => 'apiAuth']);
    $routes->delete('(:num)', 'ContentController::delete/$1', ['filter' => 'apiAuth']);
});

// Frontend comment routes
$routes->post('comments/store', '\\Content\\Controllers\\CommentController::store');

// Admin Routes
$routes->group('admin/content', ['namespace' => 'Content\\Controllers\\Admin'], static function ($routes) {
    $routes->get('/', 'ContentAdminController::index', ['filter' => 'sessionAuth']);
    $routes->get('create', 'ContentAdminController::create', ['filter' => 'sessionAuth']);
    $routes->post('store', 'ContentAdminController::store', ['filter' => 'sessionAuth']);
    $routes->post('preview', 'ContentAdminController::preview', ['filter' => 'sessionAuth']);
    $routes->get('edit/(:num)', 'ContentAdminController::edit/$1', ['filter' => 'sessionAuth']);
    $routes->post('update/(:num)', 'ContentAdminController::update/$1', ['filter' => 'sessionAuth']);
    $routes->post('delete/(:num)', 'ContentAdminController::delete/$1', ['filter' => 'sessionAuth']);
    $routes->post('featured/(:num)', 'ContentAdminController::featured/$1', ['filter' => 'sessionAuth']);
    $routes->get('revisions/(:num)', 'ContentAdminController::revisions/$1', ['filter' => 'sessionAuth']);
    $routes->post('restore/(:num)/(:num)', 'ContentAdminController::restore/$1/$2', ['filter' => 'sessionAuth']);
    $routes->get('translations/(:num)', 'ContentAdminController::translations/$1', ['filter' => 'sessionAuth']);
    $routes->get('create-translation/(:num)', 'ContentAdminController::createTranslation/$1', ['filter' => 'sessionAuth']);
    $routes->get('export-translation/(:num)/(:any)', 'ContentAdminController::exportTranslation/$1/$2', ['filter' => 'sessionAuth']);
    $routes->post('import-translation', 'ContentAdminController::importTranslation', ['filter' => 'sessionAuth']);
    $routes->get('schemas', 'SchemaAdminController::index', ['filter' => 'sessionAuth']);
    $routes->get('schemas/edit/(:any)', 'SchemaAdminController::edit/$1', ['filter' => 'sessionAuth']);
    $routes->post('schemas/store/(:any)', 'SchemaAdminController::store/$1', ['filter' => 'sessionAuth']);
    $routes->post('schemas/delete/(:any)', 'SchemaAdminController::delete/$1', ['filter' => 'sessionAuth']);
});

$routes->group('admin/comments', ['namespace' => 'Content\\Controllers\\Admin'], static function ($routes) {
    $routes->get('/', 'CommentAdminController::index', ['filter' => 'sessionAuth']);
    $routes->post('approve/(:num)', 'CommentAdminController::approve/$1', ['filter' => 'sessionAuth']);
    $routes->post('spam/(:num)', 'CommentAdminController::spam/$1', ['filter' => 'sessionAuth']);
    $routes->post('delete/(:num)', 'CommentAdminController::delete/$1', ['filter' => 'sessionAuth']);
});

$routes->group('admin/collections', ['namespace' => 'Content\\Controllers\\Admin', 'filter' => 'sessionAuth'], static function ($routes) {
    $routes->get('/', 'CollectionController::index');
    $routes->post('store', 'CollectionController::store');
    $routes->post('delete/(:num)', 'CollectionController::delete/$1');
    $routes->get('edit/(:num)', 'CollectionController::edit/$1');
    $routes->post('update/(:num)', 'CollectionController::update/$1');
    $routes->post('attach/(:num)', 'CollectionController::attach/$1');
    $routes->post('detach/(:num)', 'CollectionController::detach/$1');
});

$routes->group('admin/virtual-pages', ['namespace' => 'Content\\Controllers\\Admin', 'filter' => 'sessionAuth'], static function ($routes) {
    $routes->get('/', 'VirtualPageAdminController::index');
    $routes->get('create', 'VirtualPageAdminController::create');
    $routes->post('store', 'VirtualPageAdminController::store');
    $routes->get('edit/(:num)', 'VirtualPageAdminController::edit/$1');
    $routes->post('update/(:num)', 'VirtualPageAdminController::update/$1');
    $routes->post('delete/(:num)', 'VirtualPageAdminController::delete/$1');
});
