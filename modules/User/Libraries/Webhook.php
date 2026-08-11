<?php

namespace User\Libraries;

use CodeIgniter\HTTP\CURLRequest;

/**
 * Webhook dispatcher: delivers JSON events to registered endpoints
 * with an HMAC signature and records delivery attempts.
 */
class Webhook
{
    protected $table = 'webhooks';
    protected $deliveryTable = 'webhook_deliveries';

    /**
     * Deliver a payload to all active webhooks subscribed to an event.
     * Also fires the equivalent internal hook ('hook.' prefix) for plugins.
     */
    public function dispatch(string $event, array $payload): void
    {
        \App\Libraries\Hooks::doAction($event, $payload);

        $db = \Config\Database::connect();

        $hooks = $db->table($this->table)
            ->where('event', $event)
            ->where('is_active', 1)
            ->get()
            ->getResultArray();

        foreach ($hooks as $hook) {
            $this->deliver($hook, $event, $payload);
        }
    }

    /**
     * Perform a single delivery attempt for a hook.
     */
    public function deliver(array $hook, string $event, array $payload): void
    {
        $db = \Config\Database::connect();
        $json = json_encode($payload);

        $signature = hash_hmac('sha256', $json, $hook['secret'] ?? '');

        try {
            $client = service('curlrequest', ['timeout' => 10]);
            $response = $client->post($hook['url'], [
                'headers' => [
                    'X-KayaCMS-Event'     => $event,
                    'X-KayaCMS-Signature' => 'sha256=' . $signature,
                    'Content-Type'        => 'application/json',
                    'User-Agent'          => 'KayaCMS-Webhook/1.0',
                ],
                'body' => $json,
            ]);

            $code = $response->getStatusCode();
            $body = substr($response->getBody() ?? '', 0, 2000);
            $status = $code >= 200 && $code < 300 ? 'success' : 'failed';
        } catch (\Throwable $e) {
            $code   = 0;
            $body   = $e->getMessage();
            $status = 'failed';
        }

        $db->table($this->deliveryTable)->insert([
            'webhook_id'    => $hook['id'],
            'event'         => $event,
            'payload'       => $json,
            'status'        => $status,
            'response_code' => $code,
            'response_body' => $body,
            'attempts'      => 1,
        ]);

        if ($status === 'failed') {
            SecurityLog::warning('webhook_failed', 'Webhook delivery failed for #' . $hook['id'] . ' -> ' . $hook['url']);
        }
    }

    public function recentDeliveries(int $limit = 20): array
    {
        return \Config\Database::connect()
            ->table($this->deliveryTable)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }
}