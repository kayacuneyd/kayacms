<?php

namespace App\Controllers\Admin;

use App\Libraries\PageCache;
use App\Libraries\QueryCache;

class CacheAdminController extends BaseAdminController
{
    public function index()
    {
        if ($redirect = $this->requirePermission('settings.update')) return $redirect;

        $data['active'] = 'settings';
        $data['title']  = 'Cache Management';

        $pageCache  = PageCache::instance();
        $queryCache = QueryCache::instance();

        $data['pageEnabled']  = $pageCache->isEnabled();
        $data['queryEnabled'] = $queryCache->isEnabled();

        return $this->render('admin/cache/index', $data);
    }

        public function clearPage()
    {
        if ($redirect = $this->requirePermission('settings.update')) return $redirect;

        PageCache::instance()->flush();
        $this->logActivity('cleared', 'cache', 0, "Cleared page cache");

        return redirect()->to('/admin/cache')->with('success', "Page cache cleared.");
    }

    public function clearQuery()
    {
        if ($redirect = $this->requirePermission('settings.update')) return $redirect;

        QueryCache::instance()->flush();
        $this->logActivity('cleared', 'cache', 0, "Cleared query cache");

        return redirect()->to('/admin/cache')->with('success', "Query cache cleared.");
    }

    public function clearAll()
    {
        if ($redirect = $this->requirePermission('settings.update')) return $redirect;

        PageCache::instance()->flush();
        QueryCache::instance()->flush();

        $this->logActivity('cleared', 'cache', 0, "Cleared all caches");

        return redirect()->to('/admin/cache')->with('success', "All caches cleared.");
    }
}
