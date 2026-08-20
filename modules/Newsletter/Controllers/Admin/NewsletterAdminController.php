<?php
namespace Newsletter\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use Newsletter\Libraries\NewsletterService;
use Newsletter\Models\CampaignModel;
use Newsletter\Models\NewsletterListModel;
use Newsletter\Models\QueueModel;
use Newsletter\Models\SubscriberListModel;
use Newsletter\Models\SubscriberModel;

class NewsletterAdminController extends BaseAdminController
{
    protected SubscriberModel $subscribers;
    protected CampaignModel $campaigns;
    protected NewsletterListModel $lists;
    protected QueueModel $queue;

    public function __construct()
    {
        $this->subscribers = new SubscriberModel();
        $this->campaigns = new CampaignModel();
        $this->lists = new NewsletterListModel();
        $this->queue = new QueueModel();
    }

    public function index()
    {
        return $this->render('Newsletter\Views\admin\index', [
            'active' => 'newsletter',
            'title' => 'Newsletter',
            'subscriberCount' => $this->subscribers->where('status', 'subscribed')->countAllResults(),
            'campaigns' => $this->campaigns->orderBy('created_at', 'DESC')->findAll(10),
            'queueStats' => $this->queueStats(),
        ]);
    }

    public function subscribers()
    {
        $q = trim((string) $this->request->getGet('q'));
        if ($q !== '') {
            $this->subscribers->groupStart()->like('email', $q)->orLike('name', $q)->groupEnd();
        }

        return $this->render('Newsletter\Views\admin\subscribers', [
            'active' => 'newsletter',
            'title' => 'Newsletter Subscribers',
            'items' => $this->subscribers->orderBy('created_at', 'DESC')->findAll(100),
            'q' => $q,
        ]);
    }

    public function storeSubscriber()
    {
        $email = trim((string) $this->request->getPost('email'));
        $name = trim((string) $this->request->getPost('name'));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->withInput()->with('error', 'Valid email is required.');
        }

        (new NewsletterService())->subscribe($email, $name ?: null, 'admin', 'Admin-created subscriber', $this->request->getIPAddress());

        return redirect()->to('/admin/newsletter/subscribers')->with('success', 'Subscriber saved.');
    }

    public function importSubscribers()
    {
        $file = $this->request->getFile('csv');
        if (! $file || ! $file->isValid()) {
            return redirect()->back()->with('error', 'CSV file is required.');
        }

        $handle = fopen($file->getTempName(), 'rb');
        $created = 0;
        $service = new NewsletterService();
        while (($row = fgetcsv($handle)) !== false) {
            $email = trim((string) ($row[0] ?? ''));
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            $service->subscribe($email, trim((string) ($row[1] ?? '')) ?: null, 'csv', 'Imported subscriber list', $this->request->getIPAddress());
            $created++;
        }
        fclose($handle);

        return redirect()->to('/admin/newsletter/subscribers')->with('success', "Imported {$created} subscribers.");
    }

    public function exportSubscribers()
    {
        $rows = $this->subscribers->orderBy('email', 'ASC')->findAll();
        $csv = "email,name,status,source,consented_at,created_at\n";
        foreach ($rows as $row) {
            $csv .= implode(',', array_map([$this, 'csvCell'], [
                $row['email'], $row['name'], $row['status'], $row['source'], $row['consented_at'], $row['created_at'],
            ])) . "\n";
        }

        return $this->response
            ->setHeader('Content-Disposition', 'attachment; filename="newsletter-subscribers.csv"')
            ->setContentType('text/csv')
            ->setBody($csv);
    }

    public function unsubscribeSubscriber(int $id)
    {
        $this->subscribers->update($id, ['status' => 'unsubscribed']);
        return redirect()->back()->with('success', 'Subscriber unsubscribed.');
    }

    public function createCampaign()
    {
        return $this->campaignForm(null);
    }

    public function editCampaign(int $id)
    {
        $campaign = $this->campaigns->find($id);
        if (! $campaign) {
            return redirect()->to('/admin/newsletter')->with('error', 'Campaign not found.');
        }

        return $this->campaignForm($campaign);
    }

    public function storeCampaign()
    {
        return $this->saveCampaign();
    }

    public function updateCampaign(int $id)
    {
        return $this->saveCampaign($id);
    }

    public function enqueueCampaign(int $id)
    {
        $created = (new NewsletterService())->enqueueCampaign($id);
        return redirect()->to('/admin/newsletter')->with('success', "Queued {$created} messages.");
    }

    public function scheduleCampaign(int $id)
    {
        $campaign = $this->campaigns->find($id);
        if (! $campaign) {
            return redirect()->to('/admin/newsletter')->with('error', 'Campaign not found.');
        }

        if (empty($campaign['scheduled_at'])) {
            return redirect()->back()->with('error', 'Set a scheduled date/time before scheduling this campaign.');
        }

        $this->campaigns->update($id, ['status' => 'scheduled']);
        return redirect()->to('/admin/newsletter')->with('success', 'Campaign scheduled. It will be queued automatically when due.');
    }

    public function runQueue()
    {
        $stats = (new NewsletterService())->work(25);
        return redirect()->to('/admin/newsletter')->with('success', "Queue run: {$stats['sent']} sent, {$stats['failed']} failed, {$stats['skipped']} skipped.");
    }

    protected function campaignForm(?array $campaign)
    {
        return $this->render('Newsletter\Views\admin\campaign_form', [
            'active' => 'newsletter',
            'title' => $campaign ? 'Edit Campaign' : 'New Campaign',
            'item' => $campaign,
            'lists' => $this->lists->findAll(),
        ]);
    }

    protected function saveCampaign(?int $id = null)
    {
        $data = [
            'subject' => (string) $this->request->getPost('subject'),
            'preheader' => (string) $this->request->getPost('preheader'),
            'body_html' => (string) $this->request->getPost('body_html'),
            'body_text' => strip_tags((string) $this->request->getPost('body_html')),
            'status' => (string) ($this->request->getPost('status') ?: 'draft'),
            'provider' => (string) ($this->request->getPost('provider') ?: 'smtp'),
            'list_id' => $this->request->getPost('list_id') ? (int) $this->request->getPost('list_id') : null,
            'scheduled_at' => $this->normalizeScheduledAt((string) $this->request->getPost('scheduled_at')),
            'created_by' => session()->get('user_id'),
        ];

        $ok = $id ? $this->campaigns->update($id, $data) : $this->campaigns->insert($data);
        if (! $ok) {
            return redirect()->back()->withInput()->with('error', implode(', ', $this->campaigns->errors()));
        }

        return redirect()->to('/admin/newsletter')->with('success', 'Campaign saved.');
    }

    protected function queueStats(): array
    {
        $stats = [];
        foreach (['pending', 'sent', 'failed', 'skipped'] as $status) {
            $stats[$status] = $this->queue->where('status', $status)->countAllResults();
        }
        return $stats;
    }

    protected function csvCell($value): string
    {
        $value = (string) $value;
        return '"' . str_replace('"', '""', $value) . '"';
    }
}
