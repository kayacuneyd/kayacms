<?php
namespace Newsletter\Libraries;

use Newsletter\Models\CampaignModel;
use Newsletter\Models\NewsletterListModel;
use Newsletter\Models\QueueModel;
use Newsletter\Models\SubscriberListModel;
use Newsletter\Models\SubscriberModel;
use User\Libraries\Mailer;

class NewsletterService
{
    protected SubscriberModel $subscribers;
    protected NewsletterListModel $lists;
    protected SubscriberListModel $subscriberLists;
    protected CampaignModel $campaigns;
    protected QueueModel $queue;

    public function __construct()
    {
        $this->subscribers = new SubscriberModel();
        $this->lists = new NewsletterListModel();
        $this->subscriberLists = new SubscriberListModel();
        $this->campaigns = new CampaignModel();
        $this->queue = new QueueModel();
    }

    public function subscribe(string $email, ?string $name, string $source, string $consentText, ?string $ip = null): array
    {
        $email = strtolower(trim($email));
        $existing = $this->subscribers->findByEmail($email);
        $now = date('Y-m-d H:i:s');

        if ($existing) {
            $this->subscribers->update((int) $existing['id'], [
                'name' => $name ?: $existing['name'],
                'status' => 'subscribed',
                'source' => $source,
                'consent_text' => $consentText,
                'consent_ip' => $ip,
                'consented_at' => $now,
            ]);
            $subscriberId = (int) $existing['id'];
        } else {
            $subscriberId = (int) $this->subscribers->insert([
                'email' => $email,
                'name' => $name,
                'status' => 'subscribed',
                'source' => $source,
                'consent_text' => $consentText,
                'consent_ip' => $ip,
                'consented_at' => $now,
                'unsubscribe_token' => $this->subscribers->createToken(),
            ]);
        }

        $list = $this->lists->defaultList();
        if ($list) {
            $this->subscriberLists->attach($subscriberId, (int) $list['id']);
        }

        return $this->subscribers->find($subscriberId);
    }

    public function enqueueCampaign(int $campaignId): int
    {
        $campaign = $this->campaigns->find($campaignId);
        if (! $campaign) {
            return 0;
        }

        $subscribers = $this->subscribers->activeForList($campaign['list_id'] ? (int) $campaign['list_id'] : null);
        $created = 0;
        foreach ($subscribers as $subscriber) {
            $exists = $this->queue
                ->where('campaign_id', $campaignId)
                ->where('subscriber_id', $subscriber['id'])
                ->first();

            if ($exists) {
                continue;
            }

            $this->queue->insert([
                'campaign_id' => $campaignId,
                'subscriber_id' => (int) $subscriber['id'],
                'email' => $subscriber['email'],
                'status' => 'pending',
                'attempts' => 0,
                'max_attempts' => 3,
                'available_at' => date('Y-m-d H:i:s'),
            ]);
            $created++;
        }

        if ($created > 0) {
            $this->campaigns->update($campaignId, ['status' => 'sending']);
        }

        return $created;
    }


    public function enqueueDueCampaigns(): int
    {
        $due = $this->campaigns
            ->where('status', 'scheduled')
            ->where('scheduled_at IS NOT NULL', null, false)
            ->where('scheduled_at <=', date('Y-m-d H:i:s'))
            ->findAll(20);

        $created = 0;
        foreach ($due as $campaign) {
            $created += $this->enqueueCampaign((int) $campaign['id']);
        }

        return $created;
    }

    public function work(int $limit = 25): array
    {
        $this->enqueueDueCampaigns();
        $stats = ['sent' => 0, 'failed' => 0, 'skipped' => 0];
        foreach ($this->queue->pending($limit) as $job) {
            $this->sendJob($job, $stats);
        }

        return $stats;
    }

    protected function sendJob(array $job, array &$stats): void
    {
        $campaign = $this->campaigns->find((int) $job['campaign_id']);
        $subscriber = $this->subscribers->find((int) $job['subscriber_id']);

        if (! $campaign || ! $subscriber || $subscriber['status'] !== 'subscribed') {
            $this->queue->update((int) $job['id'], ['status' => 'skipped', 'error' => 'Campaign or subscriber unavailable.']);
            $stats['skipped']++;
            return;
        }

        if (($campaign['provider'] ?? 'smtp') === 'external') {
            $this->queue->update((int) $job['id'], ['status' => 'skipped', 'error' => 'External provider export required.']);
            $stats['skipped']++;
            return;
        }

        $body = $this->renderBody($campaign, $subscriber);
        $sent = (new Mailer())->send($subscriber['email'], $campaign['subject'], $body);

        if ($sent === true) {
            $this->queue->update((int) $job['id'], [
                'status' => 'sent',
                'attempts' => ((int) $job['attempts']) + 1,
                'sent_at' => date('Y-m-d H:i:s'),
                'error' => null,
            ]);
            $stats['sent']++;
            $this->markCampaignSentIfComplete((int) $job['campaign_id']);
            return;
        }

        $attempts = ((int) $job['attempts']) + 1;
        $failed = $attempts >= (int) $job['max_attempts'];
        $this->queue->update((int) $job['id'], [
            'status' => $failed ? 'failed' : 'pending',
            'attempts' => $attempts,
            'available_at' => date('Y-m-d H:i:s', time() + (60 * $attempts)),
            'error' => 'SMTP send failed.',
        ]);
        $stats['failed']++;
    }

    protected function renderBody(array $campaign, array $subscriber): string
    {
        $unsubscribe = base_url('/newsletter/unsubscribe/' . $subscriber['unsubscribe_token']);
        $body = str_replace(
            ['{{email}}', '{{name}}', '{{unsubscribe_url}}'],
            [esc($subscriber['email']), esc($subscriber['name'] ?? ''), esc($unsubscribe)],
            (string) $campaign['body_html']
        );

        return $body . '<p style="margin-top:32px;font-size:12px;color:#6b7280">If you no longer wish to receive this newsletter, you can <a href="' . esc($unsubscribe) . '">unsubscribe here</a>.</p>';
    }

    protected function markCampaignSentIfComplete(int $campaignId): void
    {
        $remaining = $this->queue
            ->where('campaign_id', $campaignId)
            ->whereIn('status', ['pending', 'processing'])
            ->countAllResults();

        if ((int) $remaining === 0) {
            $this->campaigns->update($campaignId, [
                'status' => 'sent',
                'sent_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
