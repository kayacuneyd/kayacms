<?php

namespace User\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use CodeIgniter\HTTP\RedirectResponse;
use User\Libraries\GdprExport;
use User\Models\UserModel;

class GdprAdminController extends BaseAdminController
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        if ($redirect = $this->requirePermission('gdpr.view')) {
            return $redirect;
        }

        $q     = trim((string) ($this->request->getGet('q') ?? ''));
        $users = [];

        if ($q !== '') {
            $users = $this->userModel
                ->like('username', $q, 'both', null, true)
                ->orLike('email', $q, 'both', null, true)
                ->orderBy('username', 'ASC')
                ->findAll(50);
        }

        $data['active'] = 'gdpr';
        $data['title']  = 'GDPR Export';
        $data['q']      = $q;
        $data['users']  = $users;

        return $this->render('admin/gdpr/index', $data);
    }

    public function export(int $id)
    {
        if ($redirect = $this->requirePermission('gdpr.export')) {
            return $redirect;
        }

        $user = $this->userModel->find($id);

        if (! $user) {
            return redirect()->to('/admin/gdpr')->with('error', 'User not found.');
        }

        $format = strtolower((string) $this->request->getGet('format'));
        $format = in_array($format, ['json', 'csv'], true) ? $format : 'json';

        $exporter = new GdprExport();
        $email    = (string) ($user->email ?? '');

        if ($format === 'csv') {
            $body     = $exporter->toCsv((int) $user->id, $email);
            $mime     = 'text/csv; charset=UTF-8';
            $filename = 'gdpr-' . $user->username . '-' . date('Ymd-His') . '.csv';
        } else {
            $body     = $exporter->toJson((int) $user->id, $email);
            $mime     = 'application/json; charset=UTF-8';
            $filename = 'gdpr-' . $user->username . '-' . date('Ymd-His') . '.json';
        }

        $this->logActivity('exported', 'user', $id, "Exported GDPR data ({$format}) for: {$user->username}");

        return $this->response
            ->setStatusCode(200)
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Cache-Control', 'no-store')
            ->setBody($body);
    }

    public function deleteData(int $id)
    {
        if ($redirect = $this->requirePermission('gdpr.export')) {
            return $redirect;
        }

        $user = $this->userModel->find($id);

        if (! $user) {
            return redirect()->to('/admin/gdpr')->with('error', 'User not found.');
        }

        if ((int) $user->id === (int) session()->get('user_id')) {
            return redirect()->to('/admin/gdpr')->with('error', 'Cannot erase your own account from this screen.');
        }

        \App\Libraries\Hooks::doAction('user.deleted', (int) $user->id, $user->username);

        // GDPR / KVKK right-to-erasure: permanently remove the account record.
        $this->userModel->builder()->where('id', (int) $user->id)->delete();

        $this->logActivity('erased', 'user', (int) $user->id, "Erased personal data for: {$user->username}");

        return redirect()->to('/admin/gdpr')->with('success', 'User account removed.');
    }
}