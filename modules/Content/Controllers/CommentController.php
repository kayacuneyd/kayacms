<?php

namespace Content\Controllers;

use App\Controllers\BaseController;
use App\Libraries\Hooks;
use Content\Models\CommentModel;
use Content\Models\ContentModel;

class CommentController extends BaseController
{
    protected CommentModel $commentModel;
    protected ContentModel $contentModel;

    public function __construct()
    {
        $this->commentModel = new CommentModel();
        $this->contentModel = new ContentModel();
    }

    public function store()
    {
        $rules = [
            'content_id'   => 'required|integer',
            'author_name'  => 'required|string|max_length[255]',
            'author_email' => 'required|valid_email|max_length[255]',
            'author_url'   => 'permit_empty|valid_url|max_length[255]',
            'body'         => 'required|string',
            'parent_id'    => 'permit_empty|integer',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Please check your inputs.');
        }

        $contentId = (int) $this->request->getPost('content_id');
        $content = $this->contentModel->find($contentId);

        if (! $content) {
            return redirect()->back()->with('error', 'Content not found.');
        }

        $this->commentModel->insert([
            'content_id'   => $contentId,
            'parent_id'    => $this->request->getPost('parent_id') ?: null,
            'author_name'  => $this->request->getPost('author_name'),
            'author_email' => $this->request->getPost('author_email'),
            'author_url'   => $this->request->getPost('author_url'),
            'body'         => $this->request->getPost('body'),
            'status'       => 'pending',
        ]);

        $commentId = (int) $this->commentModel->getInsertID();

        \App\Libraries\Hooks::doAction('comment.created', $commentId, $contentId, [
            'author_name' => $this->request->getPost('author_name'),
            'author_email' => $this->request->getPost('author_email'),
            'body'        => $this->request->getPost('body'),
        ]);

        \User\Libraries\Notifications::notifyAdmin(
            'comment',
            'New comment pending approval',
            "{$this->request->getPost('author_name')} commented on \"{$content->title}\":\n" . substr((string) $this->request->getPost('body'), 0, 140),
            '/comments'
        );

        $mailer = new \User\Libraries\Mailer();
        $notify = (new \Setting\Models\SettingModel())->getSetting('admin_email');

        if ($notify) {
            $mailer->sendView($notify, 'New comment pending: ' . $content->title, 'emails/new_comment', [
                'author'    => $this->request->getPost('author_name'),
                'body'      => $this->request->getPost('body'),
                'contentTitle' => $content->title,
                'contentId' => $contentId,
            ]);
        }

        return redirect()->to('/content/' . $content->slug)
            ->with('success', 'Your comment has been submitted and is awaiting moderation.');
    }
}
