<?php

namespace Setting\Config;

$routes = service('routes');

$routes->group('api/settings', ['namespace' => 'Setting\Controllers\Api'], static function ($routes) {
    $routes->get('/', 'SettingController::index');
    $routes->get('(:segment)', 'SettingController::show/$1');
    $routes->put('(:segment)', 'SettingController::update/$1', ['filter' => 'apiAuth']);
});

// Admin Routes
$routes->group('admin/settings', ['namespace' => 'Setting\Controllers\Admin'], static function ($routes) {
    $routes->get('/', 'SettingAdminController::index', ['filter' => 'sessionAuth']);
    $routes->post('bulk-update', 'SettingAdminController::bulkUpdate', ['filter' => 'sessionAuth']);
    $routes->post('update/(:segment)', 'SettingAdminController::update/$1', ['filter' => 'sessionAuth']);
    $routes->post('test-email', 'SettingAdminController::testEmail', ['filter' => 'sessionAuth']);
});
