<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use Content\Models\CommentModel;
use Content\Models\ContentModel;
use Media\Models\MediaModel;
use User\Models\ActivityLogModel;
use User\Models\NotificationModel;
use User\Models\UserModel;

class DashboardController extends BaseAdminController
{
    protected ContentModel $contentModel;
    protected MediaModel $mediaModel;
    protected UserModel $userModel;

    public function __construct()
    {
        $this->contentModel = new ContentModel();
        $this->mediaModel = new MediaModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $data['active'] = 'dashboard';
        $data['title']  = 'Dashboard';

        $uid = (int) ($this->data['user']['id'] ?? 0);

        $data['stats'] = [
            'total_content'     => $this->contentModel->countAllResults(),
            'published_content' => $this->contentModel->where('status', 'published')->countAllResults(),
            'draft_content'     => $this->contentModel->where('status', 'draft')->countAllResults(),
            'total_media'       => $this->mediaModel->countAllResults(),
            'total_users'       => $this->userModel->countAllResults(),
            'pending_comments'  => (new CommentModel())->getPendingCount(),
            'unread_notifications' => (new NotificationModel())->unread($uid),
        ];

        $data['recentContent'] = $this->contentModel->orderBy('updated_at', 'DESC')->findAll(5);
        $data['recentComments'] = (new CommentModel())->orderBy('created_at', 'DESC')
            ->where('status !=', 'spam')
            ->findAll(5);
        $data['recentActivity'] = (new ActivityLogModel())->getRecent(8);

        // Content published per day (last 14 days) for the chart
        $db    = \Config\Database::connect();
        $since = date('Y-m-d H:i:s', strtotime('-13 days'));
        $rows  = $db->table('content')
            ->select("date(created_at) as day, COUNT(*) as total")
            ->where('created_at >=', $since)
            ->groupBy('day')
            ->orderBy('day', 'ASC')
            ->get()
            ->getResultArray();

        $chart = [];
        for ($i = 13; $i >= 0; $i--) {
            $key = date('Y-m-d', strtotime("-{$i} days"));
            $chart[$key] = 0;
        }

        foreach ($rows as $row) {
            if (isset($chart[$row['day']])) {
                $chart[$row['day']] = (int) $row['total'];
            }
        }

        $data['chart'] = [
            'labels' => array_map(static fn ($d) => date('M j', strtotime($d)), array_keys($chart)),
            'values' => array_values($chart),
        ];

        return $this->render('admin/dashboard', $data);
    }
}