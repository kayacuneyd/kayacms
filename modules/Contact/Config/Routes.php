<?php

namespace Contact\Config;

$routes = service('routes');

$routes->group('admin/contact-forms', ['namespace' => 'Contact\\Controllers\\Admin', 'filter' => 'sessionAuth'], static function ($routes) {
    $routes->get('', 'ContactFormAdminController::index');
    $routes->get('create', 'ContactFormAdminController::create');
    $routes->post('store', 'ContactFormAdminController::store');
    $routes->get('edit/(:num)', 'ContactFormAdminController::edit/$1');
    $routes->post('update/(:num)', 'ContactFormAdminController::update/$1');
    $routes->post('delete/(:num)', 'ContactFormAdminController::delete/$1');

    $routes->get('submissions/(:num)', 'ContactSubmissionAdminController::index/$1');
    $routes->get('submissions/show/(:num)', 'ContactSubmissionAdminController::show/$1');
    $routes->post('submissions/status/(:num)/(:any)', 'ContactSubmissionAdminController::updateStatus/$1/$2');
    $routes->post('submissions/delete/(:num)', 'ContactSubmissionAdminController::delete/$1');
});

$routes->group('contact', ['namespace' => 'Contact\\Controllers'], static function ($routes) {
    $routes->get('(:segment)', 'ContactController::index/$1');
    $routes->post('(:segment)/submit', 'ContactController::submit/$1');
});
$routes->get('contact', '\\Contact\\Controllers\\ContactController::index/contact');
$routes->post('contact/submit', '\\Contact\\Controllers\\ContactController::submit/contact');
