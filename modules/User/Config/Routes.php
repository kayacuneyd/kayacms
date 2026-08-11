<?php

namespace User\Config;

$routes = service('routes');

// API Auth Routes
$routes->group('api/auth', ['namespace' => 'User\Controllers\Api'], static function ($routes) {
    $routes->post('login', 'AuthController::login', ['filter' => 'apiRateLimit:20,3600']);
    $routes->post('register', 'AuthController::register', ['filter' => 'apiRateLimit:10,3600']);
    $routes->post('refresh', 'AuthController::refresh', ['filter' => 'apiAuth']);
    $routes->get('me', 'AuthController::me', ['filter' => ['apiAuth', 'apiRateLimit:5,3600']]);
    $routes->post('logout', 'AuthController::logout', ['filter' => 'apiAuth']);

    // Password reset
    $routes->post('forgot-password', 'PasswordResetController::request', ['filter' => 'apiRateLimit:5,3600']);
    $routes->get('reset-password/(:segment)', 'PasswordResetController::validateToken/$1');
    $routes->post('reset-password/(:segment)', 'PasswordResetController::confirm/$1');

    // Admin session-based auth endpoints
    $routes->post('admin/login', 'AuthController::adminLogin', ['filter' => 'apiRateLimit:20,3600']);
    $routes->post('admin/logout', 'AuthController::adminLogout');
});

// API documentation (OpenAPI 3.x JSON)
$routes->get('api/openapi', '\User\Controllers\Api\DocumentationController::openapi');
$routes->get('api/docs', '\User\Controllers\Api\DocumentationController::docs');

// Personal access tokens
$routes->group('api/tokens', ['namespace' => 'User\Controllers\Api', 'filter' => 'apiAuth'], static function ($routes) {
    $routes->get('/', 'TokenController::index');
    $routes->post('/', 'TokenController::store');
    $routes->delete('(:num)', 'TokenController::revoke/$1');
});

// Admin: API tokens & webhooks management
$routes->group('admin/api-tokens', ['namespace' => 'User\Controllers\Admin', 'filter' => 'sessionAuth'], static function ($routes) {
    $routes->get('/', 'ApiTokenAdminController::index');
    $routes->post('store', 'ApiTokenAdminController::store');
    $routes->post('revoke/(:num)', 'ApiTokenAdminController::revoke/$1');
});

$routes->group('admin/webhooks', ['namespace' => 'User\Controllers\Admin', 'filter' => 'sessionAuth'], static function ($routes) {
    $routes->get('/', 'WebhookAdminController::index');
    $routes->post('store', 'WebhookAdminController::store');
    $routes->post('toggle/(:num)', 'WebhookAdminController::toggle/$1');
    $routes->post('delete/(:num)', 'WebhookAdminController::delete/$1');
    $routes->get('deliveries', 'WebhookAdminController::deliveries');
});

// Frontend password reset pages (server-rendered)
$routes->get('forgot-password', '\User\Controllers\Api\AuthController::forgot');
$routes->get('reset-password/(:segment)', '\User\Controllers\Api\AuthController::resetForm/$1');

// Admin Routes
$routes->group('admin/reset-password', ['namespace' => 'User\Controllers\Admin'], static function ($routes) {
    $routes->get('(:segment)', 'PasswordResetAdminController::show/$1');
    $routes->post('(:segment)', 'PasswordResetAdminController::reset/$1');
});
$routes->group('admin/notifications', ['namespace' => 'User\Controllers\Admin', 'filter' => 'sessionAuth'], static function ($routes) {
    $routes->get('/', 'NotificationAdminController::index');
    $routes->post('mark-read/(:num)', 'NotificationAdminController::markRead/$1');
    $routes->post('clear', 'NotificationAdminController::clear');
});

$routes->group('admin/roles', ['namespace' => 'User\Controllers\Admin'], static function ($routes) {
    $routes->get('/', 'RoleAdminController::index', ['filter' => 'sessionAuth']);
    $routes->get('create', 'RoleAdminController::create', ['filter' => 'sessionAuth']);
    $routes->post('store', 'RoleAdminController::store', ['filter' => 'sessionAuth']);
    $routes->get('edit/(:num)', 'RoleAdminController::edit/$1', ['filter' => 'sessionAuth']);
    $routes->post('update/(:num)', 'RoleAdminController::update/$1', ['filter' => 'sessionAuth']);
    $routes->post('delete/(:num)', 'RoleAdminController::delete/$1', ['filter' => 'sessionAuth']);
});

$routes->group('admin/users', ['namespace' => 'User\Controllers\Admin'], static function ($routes) {
    $routes->get('/', 'UserAdminController::index', ['filter' => 'sessionAuth']);
    $routes->get('create', 'UserAdminController::create', ['filter' => 'sessionAuth']);
    $routes->post('store', 'UserAdminController::store', ['filter' => 'sessionAuth']);
    $routes->get('edit/(:num)', 'UserAdminController::edit/$1', ['filter' => 'sessionAuth']);
    $routes->post('update/(:num)', 'UserAdminController::update/$1', ['filter' => 'sessionAuth']);
    $routes->post('delete/(:num)', 'UserAdminController::delete/$1', ['filter' => 'sessionAuth']);
});
$routes->group('admin/security', ['namespace' => 'App\Controllers\Admin', 'filter' => 'sessionAuth'], static function ($routes) {
    $routes->get('/', 'SecurityController::index');
    $routes->get('2fa', 'SecurityController::twoFactor');
    $routes->post('2fa/enable', 'SecurityController::twoFactorEnable');
    $routes->post('2fa/disable', 'SecurityController::twoFactorDisable');
    $routes->get('audit', 'SecurityController::audit');
});

$routes->group('admin/gdpr', ['namespace' => 'User\Controllers\Admin', 'filter' => 'sessionAuth'], static function ($routes) {
    $routes->get('/', 'GdprAdminController::index');
    $routes->get('export/(:num)', 'GdprAdminController::export/$1');
    $routes->post('delete-data/(:num)', 'GdprAdminController::deleteData/$1');
});
