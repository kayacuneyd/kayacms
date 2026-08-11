<?php

namespace User\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use User\Models\ActivityLogModel;

class ActivityLogController extends BaseAdminController
{
    protected ActivityLogModel $activityLogModel;

    public function __construct()
    {
        $this->activityLogModel = new ActivityLogModel();
    }

    public function index()
    {
        if ($redirect = $this->requirePermission('activity_logs.view')) {
            return $redirect;
        }

        $data['active'] = 'activity_logs';
        $data['title']  = 'Activity Logs';

        $limit = (int) ($this->request->getGet('limit') ?: 50);
        $entityType = $this->request->getGet('entity_type') ?: null;
        $action = $this->request->getGet('action') ?: null;

        $data['items'] = $this->activityLogModel->getRecent($limit, $entityType, $action);
        $data['summary'] = $this->activityLogModel->getSummary();
        $data['filters'] = [
            'limit'       => $limit,
            'entity_type' => $entityType,
            'action'      => $action,
        ];

        return $this->render('admin/activity_logs/index', $data);
    }
}
