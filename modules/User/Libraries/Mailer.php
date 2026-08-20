<?php

namespace User\Libraries;

use Setting\Models\SettingModel;

/**
 * Mailer builds a CodeIgniter Email instance from SMTP settings
 * stored in the settings table, and provides a simple send API.
 */
class Mailer
{
    protected SettingModel $setting;

    public function __construct()
    {
        $this->setting = new SettingModel();
    }

    /**
     * Build an Email instance configured from SMTP settings.
     */
    public function instance(): \CodeIgniter\Email\Email
    {
        $email = \Config\Services::email();

        $smtpHost = $this->setting->getSetting('smtp_host', '');
        $smtpUser = $this->setting->getSetting('smtp_user', '');

        if ($smtpHost !== '') {
            $emailConfig = [
                'protocol'    => 'smtp',
                'SMTPHost'    => $smtpHost,
                'SMTPPort'    => (int) $this->setting->getSetting('smtp_port', 587),
                'SMTPUser'    => $smtpUser,
                'SMTPPass'    => $this->setting->getSetting('smtp_pass', ''),
                'SMTPCrypto'  => $this->setting->getSetting('smtp_crypto', 'tls'),
                'mailType'    => 'html',
                'wordWrap'    => true,
                'charset'     => 'UTF-8',
                'newline'     => "\r\n",
                'CRLF'        => "\r\n",
            ];
            $email->initialize($emailConfig);
        } elseif ($this->setting->getSetting('mail_protocol', '') === 'sendmail') {
            $email->initialize([
                'protocol' => 'sendmail',
                'mailPath' => $this->setting->getSetting('sendmail_path', '/usr/sbin/sendmail'),
                'mailType' => 'html',
                'wordWrap' => true,
                'charset'  => 'UTF-8',
                'newline'  => "\n",
                'CRLF'     => "\n",
            ]);
        }

        return $email;
    }

    /**
     * Current default from address.
     */
    public function defaultFrom(): string
    {
        return $this->setting->getSetting('smtp_from_email')
            ?: ($this->setting->getSetting('smtp_user') ?: 'noreply@kayacms.local');
    }

    /**
     * Whether mail delivery is configured.
     */
    public function isConfigured(): bool
    {
        if ($this->setting->getSetting('mail_protocol', '') === 'sendmail') {
            return true;
        }

        $host = (string) $this->setting->getSetting('smtp_host', '');
        $user = (string) $this->setting->getSetting('smtp_user', '');
        $pass = (string) $this->setting->getSetting('smtp_pass', '');

        return $host !== '' && ($user === '' || $pass !== '');
    }

    /**
     * Send a simple email.
     *
     * @return bool|string true on success, error string on failure
     */
    public function send(
        string $to,
        string $subject,
        string $message,
        array $from = []
    ) {
        $email = $this->instance();

        $fromEmail = $from['email'] ?? $this->defaultFrom();
        $fromName  = $from['name'] ?? ($this->setting->getSetting('smtp_from_name') ?: $this->setting->getSetting('site_name', 'KayaCMS'));

        try {
            $email->setFrom($fromEmail, $fromName);
            $email->setTo($to);
            $email->setSubject($subject);
            $email->setMessage($message);
            $sent = $email->send();

            if (! $sent) {
                log_message('error', 'Mailer send failed: ' . $email->printDebugger(['headers']));
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            log_message('error', 'Mailer exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Render a view into an email body and send it.
     */
    public function sendView(
        string $to,
        string $subject,
        string $view,
        array $data = []
    ) {
        $body = view($view, $data);

        return $this->send($to, $subject, $body);
    }
}