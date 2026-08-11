<?php

namespace Content\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use Content\Models\CommentModel;

class CommentAdminController extends BaseAdminController
{
    protected CommentModel $commentModel;

    public function __construct()
    {
        $this->commentModel = new CommentModel();
    }

    public function index()
    {
        $this->requirePermission('comments.manage');

        $status  = $this->request->getGet('status') ?: null;
        $keyword = $this->request->getGet('q');
        $perPage = (int) ($this->request->getGet('per_page') ?: 20);

        $builder = $this->commentModel->orderBy('created_at', 'DESC');

        if ($status && $status !== 'all') {
            $builder->where('status', $status);
        }

        if ($keyword) {
            $builder->groupStart()
                ->like('author_name', $keyword)
                ->orLike('author_email', $keyword)
                ->orLike('body', $keyword)
                ->groupEnd();
        }

        $data['active'] = 'comments';
        $data['title']  = 'Comments';
        $data['items']  = $builder->findAll($perPage);
        $data['total']  = (new CommentModel())->where('1=1')
            ->groupStart()
            ->where('1=1')
            ->groupEnd()
            ->countAllResults();
        $data['status']   = $status;
        $data['keyword']  = $keyword;
        $data['pendingCount'] = (int) $this->commentModel->where('status', 'pending')->countAllResults();

        return $this->render('admin/comments/index', $data);
    }

    public function approve(int $id)
    {
        $this->requirePermission('comments.manage');

        $comment = $this->commentModel->find($id);

        if (! $comment) {
            return redirect()->to('/admin/comments')->with('error', 'Comment not found.');
        }

        $this->commentModel->update($id, ['status' => 'approved']);

        return redirect()->to('/admin/comments')->with('success', 'Comment approved.');
    }

    public function spam(int $id)
    {
        $this->requirePermission('comments.manage');

        $comment = $this->commentModel->find($id);

        if (! $comment) {
            return redirect()->to('/admin/comments')->with('error', 'Comment not found.');
        }

        $this->commentModel->update($id, ['status' => 'spam']);

        return redirect()->to('/admin/comments')->with('success', 'Comment marked as spam.');
    }

    public function delete(int $id)
    {
        $this->requirePermission('comments.manage');

        $comment = $this->commentModel->find($id);

        if (! $comment) {
            return redirect()->to('/admin/comments')->with('error', 'Comment not found.');
        }

        $this->commentModel->delete($id);

        return redirect()->to('/admin/comments')->with('success', 'Comment deleted.');
    }
}
