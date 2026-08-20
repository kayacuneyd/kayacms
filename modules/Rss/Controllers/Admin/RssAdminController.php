<?php

namespace Rss\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use Rss\Libraries\RssIdeaService;
use Rss\Models\RssItemModel;
use Rss\Models\RssSourceModel;

class RssAdminController extends BaseAdminController
{
    private RssSourceModel $sources;
    private RssItemModel $items;

    public function __construct()
    {
        $this->sources = new RssSourceModel();
        $this->items = new RssItemModel();
    }

    public function inbox()
    {
        if ($redirect = $this->requirePermission('rss.view')) return $redirect;

        $status = (string) ($this->request->getGet('status') ?? '');
        $perPage = 30;
        $page = max(1, (int) ($this->request->getGet('page') ?? 1));
        $builder = $this->items->withSource()->orderBy('rss_items.published_at', 'DESC')->orderBy('rss_items.id', 'DESC');
        if ($status !== '') {
            $builder->where('rss_items.status', $status);
        }

        $totalItems = (int) $builder->countAllResults(false);
        $totalPages = max(1, (int) ceil($totalItems / $perPage));
        $page = min($page, $totalPages);

        return $this->render('admin/rss/inbox', [
            'active' => 'rss',
            'title' => 'RSS Ideas',
            'items' => $builder->findAll($perPage, ($page - 1) * $perPage),
            'status' => $status,
            'counts' => $this->statusCounts(),
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total_items' => $totalItems,
                'total_pages' => $totalPages,
                'query' => $status !== '' ? ['status' => $status] : [],
            ],
        ]);
    }

    public function sources()
    {
        if ($redirect = $this->requirePermission('rss.view')) return $redirect;

        return $this->render('admin/rss/sources', [
            'active' => 'rss',
            'title' => 'RSS Sources',
            'sources' => $this->sources->orderBy('country', 'ASC')->orderBy('name', 'ASC')->findAll(),
        ]);
    }

    public function toggleSource(int $id)
    {
        if ($redirect = $this->requirePermission('rss.manage')) return $redirect;

        $source = $this->sources->find($id);
        if ($source) {
            $this->sources->update($id, ['is_active' => empty($source['is_active']) ? 1 : 0]);
        }

        return redirect()->back()->with('success', 'RSS source updated.');
    }

    public function status(int $id)
    {
        if ($redirect = $this->requirePermission('rss.manage')) return $redirect;

        $status = (string) $this->request->getPost('status');
        if (in_array($status, ['new', 'ignored', 'shortlisted', 'drafted'], true)) {
            $this->items->update($id, ['status' => $status]);
        }

        return redirect()->back()->with('success', 'RSS item updated.');
    }

    public function deleteItem(int $id)
    {
        if ($redirect = $this->requirePermission('rss.manage')) return $redirect;

        if ($this->items->find($id)) {
            $this->items->delete($id);
        }

        return redirect()->back()->with('success', 'RSS item deleted.');
    }

    public function bulkItems()
    {
        if ($redirect = $this->requirePermission('rss.manage')) return $redirect;

        $action = (string) $this->request->getPost('bulk_action');
        $ids = array_values(array_unique(array_filter(array_map('intval', (array) $this->request->getPost('item_ids')))));

        if ($action === 'delete_selected') {
            if ($ids) {
                $this->items->whereIn('id', $ids)->delete();
            }

            return redirect()->back()->with('success', count($ids) . ' RSS item(s) deleted.');
        }

        if ($action === 'delete_previous_days') {
            $deleted = $this->deletePreviousDays();

            return redirect()->back()->with('success', $deleted . ' old RSS item(s) deleted.');
        }

        return redirect()->back()->with('error', 'Bulk action was not recognized.');
    }

    public function purgeOld()
    {
        if ($redirect = $this->requirePermission('rss.manage')) return $redirect;

        $deleted = $this->deletePreviousDays();

        return redirect()->back()->with('success', $deleted . ' old RSS item(s) deleted.');
    }

    public function suggest(int $id)
    {
        if ($redirect = $this->requirePermission('rss.manage')) return $redirect;

        $item = $this->items->find($id);
        if (! $item) {
            return redirect()->back()->with('error', 'RSS item not found.');
        }

        try {
            (new RssIdeaService())->suggest($item);
            return redirect()->back()->with('success', 'Draft suggestion generated.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function draft(int $id)
    {
        if ($redirect = $this->requirePermission('rss.manage')) return $redirect;

        $item = $this->items->find($id);
        if (! $item) {
            return redirect()->back()->with('error', 'RSS item not found.');
        }

        $source = $this->sources->find((int) $item['source_id']) ?: [];

        try {
            $contentId = (new RssIdeaService())->createDraft($item, $source);
            return redirect()->to('/admin/content/edit/' . $contentId)->with('success', 'Draft created from RSS idea.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    private function deletePreviousDays(): int
    {
        $cutoff = date('Y-m-d 00:00:00');
        $db = \Config\Database::connect();
        $builder = $db->table('rss_items');
        $builder->where('created_at <', $cutoff)->delete();

        return $db->affectedRows();
    }

    private function statusCounts(): array
    {
        $counts = ['new' => 0, 'ignored' => 0, 'shortlisted' => 0, 'drafted' => 0];
        foreach ($this->items->select('status, COUNT(*) AS total')->groupBy('status')->findAll() as $row) {
            $counts[(string) $row['status']] = (int) $row['total'];
        }

        return $counts;
    }
}
