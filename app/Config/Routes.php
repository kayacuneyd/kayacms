<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

// Admin Routes
$routes->get('admin', 'Admin\AuthController::login');
$routes->get('admin/login', 'Admin\AuthController::login');
$routes->get('admin/logout', 'Admin\AuthController::logout');
$routes->get('admin/dashboard', 'Admin\DashboardController::index');
