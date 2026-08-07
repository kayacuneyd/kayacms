<?php
namespace User\Config;

$routes = service('routes');

// API Routes - JWT Authentication
$routes->group('api/auth', ['namespace' => 'User\Controllers\Api'], static function ($routes) {
    $routes->post('login', 'AuthController::login');
    $routes->post('register', 'AuthController::register');
    $routes->get('me', 'AuthController::me', ['filter' => 'apiAuth']);
    $routes->post('refresh', 'AuthController::refresh', ['filter' => 'apiAuth']);
});

// Admin Routes - Session Authentication
$routes->group('admin/auth', ['namespace' => 'User\Controllers\Api'], static function ($routes) {
    $routes->post('login', 'AuthController::adminLogin');
    $routes->post('logout', 'AuthController::adminLogout');
});
