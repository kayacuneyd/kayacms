<?php
namespace Newsletter\Config;

$routes = service('routes');

$routes->post('newsletter/subscribe', '\Newsletter\Controllers\NewsletterController::subscribe');
$routes->get('newsletter/unsubscribe/(:segment)', '\Newsletter\Controllers\NewsletterController::unsubscribe/$1');

$routes->group('admin/newsletter', ['namespace' => 'Newsletter\Controllers\Admin', 'filter' => 'sessionAuth'], static function ($routes) {
    $routes->get('/', 'NewsletterAdminController::index');
    $routes->get('subscribers', 'NewsletterAdminController::subscribers');
    $routes->post('subscribers/store', 'NewsletterAdminController::storeSubscriber');
    $routes->post('subscribers/import', 'NewsletterAdminController::importSubscribers');
    $routes->get('subscribers/export', 'NewsletterAdminController::exportSubscribers');
    $routes->post('subscribers/unsubscribe/(:num)', 'NewsletterAdminController::unsubscribeSubscriber/$1');
    $routes->get('campaigns/create', 'NewsletterAdminController::createCampaign');
    $routes->post('campaigns/store', 'NewsletterAdminController::storeCampaign');
    $routes->get('campaigns/edit/(:num)', 'NewsletterAdminController::editCampaign/$1');
    $routes->post('campaigns/update/(:num)', 'NewsletterAdminController::updateCampaign/$1');
    $routes->post('campaigns/enqueue/(:num)', 'NewsletterAdminController::enqueueCampaign/$1');
    $routes->post('campaigns/schedule/(:num)', 'NewsletterAdminController::scheduleCampaign/$1');
    $routes->post('queue/run', 'NewsletterAdminController::runQueue');
});
