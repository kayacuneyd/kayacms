<?php

namespace Maintenance\Controllers;

use CodeIgniter\Controller;
use Maintenance\Libraries\WebCron;

/**
 * Web-cron endpoint.
 *
 * Replaces a shell cron job on shared hosting where only HTTP is available.
 * Scheduled tasks (media queue, backup) run when a valid `cron/run/{token}`
 * URL is requested (e.g. `curl https://site.test/cron/run/…` from a remote
 * scheduler). The token lives in the `cron_token` setting; an empty token
 * disables the endpoint entirely.
 */
class WebCronController extends Controller
{
    public function run(?string $token = null)
    {
        $webcron = new WebCron();

        if (! $webcron->isValidToken($token)) {
            return $this->response
                ->setStatusCode(403)
                ->setJSON(['error' => 'Invalid cron token.']);
        }

        return $this->response->setJSON([
            'ok'      => true,
            'results' => $webcron->run(),
            'ran_at'  => date('c'),
        ]);
    }
}