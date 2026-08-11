<?php

namespace Contact\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use Contact\Models\ContactFormModel;
use Contact\Models\ContactSubmissionModel;

class ContactSubmissionAdminController extends BaseAdminController
{
    protected ContactFormModel $formModel;
    protected ContactSubmissionModel $submissionModel;

    public function __construct()
    {
        $this->formModel       = new ContactFormModel();
        $this->submissionModel = new ContactSubmissionModel();
    }

    public function index(int $formId)
    {
        if ($redirect = $this->requirePermission('contact_submissions.view')) return $redirect;

        $form = $this->formModel->find($formId);
        if (! $form) {
            return redirect()->to('/admin/contact-forms')->with('error', 'Contact form not found.');
        }

        $status = $this->request->getGet('status') ?? '';
        $submissions = $this->submissionModel->getByForm($formId, $status);
        foreach ($submissions as &$sub) {
            $sub = $this->submissionModel->decodeData($sub);
        }

        $data['active'] = 'contact_forms';
        $data['title']  = 'Submissions: ' . $form['name'];
        $data['form']   = $form;
        $data['items']  = $submissions;
        $data['status'] = $status;
        $data['canUpdate'] = $this->can('contact_submissions.update');
        $data['canDelete'] = $this->can('contact_submissions.delete');

        return $this->render('admin/contact_submissions/index', $data);
    }

    public function show(int $id)
    {
        if ($redirect = $this->requirePermission('contact_submissions.view')) return $redirect;

        $submission = $this->submissionModel->find($id);
        if (! $submission) {
            return redirect()->to('/admin/contact-forms')->with('error', 'Submission not found.');
        }

        $submission = $this->submissionModel->decodeData($submission);
        $form = $this->formModel->find($submission['contact_form_id']);

        if ($submission['status'] === 'new' && $this->can('contact_submissions.update')) {
            $this->submissionModel->update($id, ['status' => 'read', 'read_at' => date('Y-m-d H:i:s')]);
        }

        $data['active'] = 'contact_forms';
        $data['title']  = 'Submission #' . $id;
        $data['form']   = $form;
        $data['item']   = $submission;

        return $this->render('admin/contact_submissions/show', $data);
    }

    public function updateStatus(int $id, string $status)
    {
        if ($redirect = $this->requirePermission('contact_submissions.update')) return $redirect;

        if (! in_array($status, ['new', 'read', 'archived'], true)) {
            return redirect()->back()->with('error', 'Invalid status.');
        }

        $submission = $this->submissionModel->find($id);
        if (! $submission) {
            return redirect()->to('/admin/contact-forms')->with('error', 'Submission not found.');
        }

        $update = ['status' => $status];
        if ($status === 'read') {
            $update['read_at'] = date('Y-m-d H:i:s');
        }

        $this->submissionModel->update($id, $update);
        $this->logActivity('updated', 'contact_submission', $id, "Marked submission #{$id} as {$status}");

        return redirect()->back()->with('success', 'Submission status updated.');
    }

    public function delete(int $id)
    {
        if ($redirect = $this->requirePermission('contact_submissions.delete')) return $redirect;

        $submission = $this->submissionModel->find($id);
        if (! $submission) {
            return redirect()->to('/admin/contact-forms')->with('error', 'Submission not found.');
        }

        $this->submissionModel->delete($id);
        $this->logActivity('deleted', 'contact_submission', $id, "Deleted submission #{$id}");

        return redirect()->back()->with('success', 'Submission deleted.');
    }
}
