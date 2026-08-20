<?php

namespace Contact\Controllers;

use App\Controllers\BaseController;
use Contact\Models\ContactFormModel;
use Contact\Models\ContactSubmissionModel;
use Setting\Models\SettingModel;

class ContactController extends BaseController
{
    protected ContactFormModel $formModel;
    protected ContactSubmissionModel $submissionModel;
    protected SettingModel $settingModel;

    public function __construct()
    {
        $this->formModel       = new ContactFormModel();
        $this->submissionModel = new ContactSubmissionModel();
        $this->settingModel    = new SettingModel();
    }

    public function index(string $slug = 'contact')
    {
        $form = $this->formModel->findBySlug($slug);
        if (! $form) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Contact form not found.');
        }

        $site = $this->settingModel->getByGroup('general');

        $data['pageTitle']       = $form['name'];
        $data['metaDescription'] = $site['site_description'] ?? '';
        $data['site']            = $site;
        $data['form']            = $form;
        $data['active']          = 'contact';

        return $this->renderTheme('contact/index', $data);
    }

    public function submit(string $slug = 'contact')
    {
        $form = $this->formModel->findBySlug($slug);
        if (! $form) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Contact form not found.');
        }

        if (! $this->passesSpamProtection()) {
            return redirect()->back()->withInput()->with('error', 'Your message could not be sent right now. Please try again shortly.');
        }

        $rules = [];
        $fieldData = [];
        foreach ($form['fields'] as $field) {
            $fieldName = $field['name'];
            $fieldData[$fieldName] = $this->request->getPost($fieldName);
            if ($field['required']) {
                $rules[$fieldName] = 'required';
            }
            if ($field['type'] === 'email') {
                $rules[$fieldName] = ($rules[$fieldName] ?? '') . '|valid_email';
            }
        }

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Please fill in all required fields correctly.');
        }

        $this->submissionModel->insert([
            'contact_form_id' => $form['id'],
            'data'            => json_encode($fieldData, JSON_UNESCAPED_UNICODE),
            'status'          => 'new',
            'ip_address'      => $this->request->getIPAddress(),
            'user_agent'      => $this->request->getUserAgent()->getAgentString(),
        ]);

        $submissionId = (int) $this->submissionModel->getInsertID();

        \User\Libraries\Notifications::notifyAdmin(
            'contact',
            'New contact form submission: ' . $form['name'],
            ($fieldData['name'] ?? '') . ' sent a message via the ' . $form['name'] . ' form.',
            '/contact-forms/submissions/' . $form['id']
        );

        $this->sendNotification($form, $fieldData);

        return redirect()->to('/contact/' . $form['slug'])->with('success', 'Thank you! Your message has been sent.');
    }

    private function passesSpamProtection(): bool
    {
        if (trim((string) $this->request->getPost('website')) !== '') {
            return false;
        }

        $startedAt = (int) $this->request->getPost('form_started_at');
        $token = (string) $this->request->getPost('form_token');
        $expected = hash_hmac('sha256', (string) $startedAt, (string) config('Encryption')->key);
        $age = time() - $startedAt;

        if ($startedAt <= 0 || ! hash_equals($expected, $token) || $age < 3 || $age > 7200) {
            return false;
        }

        $cache = cache();
        $ip = preg_replace('/[^a-zA-Z0-9:_\.-]/', '_', $this->request->getIPAddress());
        $key = 'contact_rate_' . md5($ip);
        $count = (int) ($cache->get($key) ?? 0);
        if ($count >= 5) {
            return false;
        }

        $cache->save($key, $count + 1, 3600);

        return true;
    }

    private function sendNotification(array $form, array $fieldData): void
    {
        $notify = $form['settings']['notify_email'] ?? '';
        if (! filter_var($notify, FILTER_VALIDATE_EMAIL)) {
            $notify = $this->settingModel->getSetting('admin_email');
        }

        if (! filter_var($notify, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $email = \Config\Services::email();
        $site  = $this->settingModel->getByGroup('general');
        $from  = $this->settingModel->getSetting('smtp_user') ?: 'noreply@kayacms.local';

        $email->setFrom($from, $site['site_name'] ?? 'KayaCMS');
        $email->setTo($notify);
        $email->setSubject('New submission: ' . $form['name']);

        $body = "You have received a new submission for form '{$form['name']}':\n\n";
        foreach ($fieldData as $key => $value) {
            $label = ucfirst(str_replace('_', ' ', $key));
            $body .= "{$label}: {$value}\n";
        }
        $email->setMessage($body);

        try {
            $email->send();
        } catch (\Throwable $e) {
            log_message('error', 'Contact form email failed: ' . $e->getMessage());
        }
    }

    private function renderTheme(string $view, array $data)
    {
        $themeModel = new \Theme\Models\ThemeModel();
        $activeTheme = $themeModel->getActive();
        $theme = $activeTheme['slug'] ?? 'default';
        $settings = array_merge(
            $this->settingModel->getByGroup('general'),
            $this->settingModel->getByGroup('seo'),
            $this->settingModel->getByGroup('email')
        );

        $data['theme'] = $theme;
        $data['settings'] = $settings;
        $data['site'] = $data['site'] ?? $settings;
        $data['theme_config'] = (new \Theme\Libraries\ThemeConfig())->resolve($activeTheme);
        $data['locale'] = current_locale();
        $data['defaultLocale'] = $this->settingModel->getSetting('site_default_locale', 'tr');
        $data['canonical_url'] = current_url();
        $data['title'] = $data['pageTitle'] ?? ($settings['site_name'] ?? 'KayaCMS');
        $data['page_title'] = $data['pageTitle'] ?? ($settings['site_name'] ?? 'KayaCMS');
        $data['meta_description'] = $data['metaDescription'] ?? ($settings['site_description'] ?? '');
        $data['structured_data'] = [];
        $data['menus'] = [];

        $menuModel = new \Menu\Models\MenuModel();
        $menuItemModel = new \Menu\Models\MenuItemModel();
        $menus = $menuModel->where('locale', current_locale())->findAll();
        foreach ($menus as $menu) {
            $data['menus'][$menu['location']] = $menuItemModel->getMenuTree((int) $menu['id']);
        }

        $data['currentLocale'] = current_locale();
        $data['activeLocales'] = explode(',', $this->settingModel->getSetting('site_active_locales', 'tr'));

        if (! is_file(APPPATH . "Views/themes/{$theme}/{$view}.php")) {
            $theme = 'default';
        }

        return view("themes/{$theme}/{$view}", $data);
    }
}
