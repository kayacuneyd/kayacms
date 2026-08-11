<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$locales = 'tr|en';

// Default locale routes (no prefix)
$routes->get('/', 'Home::index');
$routes->get('search', 'Home::search');
$routes->get('sitemap.xml', 'Home::sitemap');
$routes->get('robots.txt', 'Home::robots');
$routes->get('feed.xml', 'Home::feed');
$routes->get('rss.xml', 'Home::feed');
$routes->get('content/(:segment)', 'Home::show/$1');
$routes->get('category/(:segment)', 'Home::category/$1');
$routes->get('tag/(:segment)', 'Home::tag/$1');
$routes->get('page/(:segment)', 'Home::page/$1');

// Localized routes with locale prefix
$routes->get("($locales)", 'Home::index');
$routes->get("($locales)/search", 'Home::search');
$routes->get("($locales)/sitemap.xml", 'Home::sitemap');
$routes->get("($locales)/robots.txt", 'Home::robots');
$routes->get("($locales)/feed.xml", 'Home::feed');
$routes->get("($locales)/rss.xml", 'Home::feed');
$routes->get("($locales)/content/(:segment)", 'Home::show/$2');
$routes->get("($locales)/category/(:segment)", 'Home::category/$2');
$routes->get("($locales)/tag/(:segment)", 'Home::tag/$2');
$routes->get("($locales)/page/(:segment)", 'Home::page/$2');

// Admin Routes — registered before the virtual-page catch-all below so that
// /admin and admin sub-routes are never captured by the single-segment catch-all.
$routes->get('admin', 'Admin\\AuthController::login');
$routes->get('admin/login', 'Admin\\AuthController::login');
$routes->post('admin/auth/attempt', 'Admin\\AuthController::attempt');
$routes->get('admin/auth/totp', 'Admin\\AuthController::totpForm');
$routes->post('admin/auth/totp', 'Admin\\AuthController::totpVerify');
$routes->get('admin/magic-link', 'Admin\\AuthController::magicLinkForm');
$routes->post('admin/magic-link', 'Admin\\AuthController::magicLinkRequest');
$routes->get('admin/magic-link/(:segment)', 'Admin\\AuthController::magicLinkConsume/$1');
$routes->get('admin/logout', 'Admin\\AuthController::logout');
$routes->get('admin/dashboard', 'Admin\\DashboardController::index', ['filter' => 'sessionAuth']);
$routes->get('admin/cache', 'Admin\\CacheAdminController::index', ['filter' => 'sessionAuth']);
$routes->get('admin/hooks', 'Admin\\HooksAdminController::index', ['filter' => 'sessionAuth']);
$routes->post('admin/cache/clear-page', 'Admin\\CacheAdminController::clearPage', ['filter' => 'sessionAuth']);
$routes->post('admin/cache/clear-query', 'Admin\\CacheAdminController::clearQuery', ['filter' => 'sessionAuth']);
$routes->post('admin/cache/clear-all', 'Admin\\CacheAdminController::clearAll', ['filter' => 'sessionAuth']);

// Contact — registered before the single-segment catch-all below so that the
// standalone /contact page is served by the Contact module, not Home::virtualPage.
$routes->get('contact', '\Contact\Controllers\ContactController::index/contact');
$routes->post('contact/submit', '\Contact\Controllers\ContactController::submit/contact');
$routes->get('contact/(:segment)', '\Contact\Controllers\ContactController::index/$1');
$routes->post('contact/(:segment)/submit', '\Contact\Controllers\ContactController::submit/$1');

// Virtual pages — any remaining single-segment URL is checked against the
// virtual_pages table before a 404 is emitted. Because specific routes above
// (content, category, page, admin, api, feed…) are registered first, this
// catch-all only fires for otherwise-unmatched paths.
$routes->get('(:segment)', 'Home::virtualPage/$1');
