<?php
namespace Setting\Config;

$routes = service('routes');

$routes->group('api/settings', ['namespace' => 'Setting\Controllers\Api'], static function ($routes) {
    $routes->get('/', 'SettingController::index');
    $routes->get('(:segment)', 'SettingController::show/$1');
    $routes->put('(:segment)', 'SettingController::update/$1', ['filter' => 'apiAuth']);
});
