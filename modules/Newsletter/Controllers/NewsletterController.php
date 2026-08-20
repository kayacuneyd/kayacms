<?php
namespace Newsletter\Controllers;

use App\Controllers\BaseController;
use Newsletter\Libraries\NewsletterService;
use Newsletter\Models\SubscriberModel;

class NewsletterController extends BaseController
{
    public function subscribe()
    {
        if (trim((string) $this->request->getPost('website')) !== '') {
            return redirect()->back()->with('success', 'Your newsletter subscription has been recorded.');
        }

        $lastSignup = (int) (session('newsletter_submit_at') ?? 0);
        if ($lastSignup > 0 && time() - $lastSignup < 60) {
            return redirect()->back()->with('error', 'Please wait a moment before submitting another subscription request.');
        }

        $email = trim((string) $this->request->getPost('email'));
        $name = trim((string) $this->request->getPost('name'));
        $consent = (string) $this->request->getPost('consent');

        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || $consent === '') {
            return redirect()->back()->withInput()->with('error', 'A valid email and consent are required to subscribe.');
        }

        session()->set('newsletter_submit_at', time());

        (new NewsletterService())->subscribe(
            $email,
            $name !== '' ? $name : null,
            'site',
            'Newsletter signup consent',
            $this->request->getIPAddress()
        );

        return redirect()->back()->with('success', 'Your newsletter subscription has been recorded.');
    }

    public function unsubscribe(string $token)
    {
        $subscriber = (new SubscriberModel())->where('unsubscribe_token', $token)->first();
        if ($subscriber) {
            (new SubscriberModel())->update((int) $subscriber['id'], ['status' => 'unsubscribed']);
        }

        return view('Newsletter\Views\unsubscribe', ['found' => (bool) $subscriber]);
    }
}
