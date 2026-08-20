<?php

namespace Rss\Config;

$routes = service('routes');

$routes->group('admin/rss', ['namespace' => 'Rss\Controllers\Admin', 'filter' => 'sessionAuth'], static function ($routes) {
    $routes->get('/', 'RssAdminController::inbox');
    $routes->get('inbox', 'RssAdminController::inbox');
    $routes->get('sources', 'RssAdminController::sources');
    $routes->post('sources/toggle/(:num)', 'RssAdminController::toggleSource/$1');
    $routes->post('items/status/(:num)', 'RssAdminController::status/$1');
    $routes->post('items/delete/(:num)', 'RssAdminController::deleteItem/$1');
    $routes->post('items/bulk', 'RssAdminController::bulkItems');
    $routes->post('items/purge-old', 'RssAdminController::purgeOld');
    $routes->post('items/suggest/(:num)', 'RssAdminController::suggest/$1');
    $routes->post('items/draft/(:num)', 'RssAdminController::draft/$1');
});
