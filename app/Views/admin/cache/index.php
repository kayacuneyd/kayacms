<?php $title = $title ?? 'Cache Management'; ?>
<div class="ck-bg-white ck-p-6 ck-rounded-lg ck-shadow">
    <div class="ck-flex ck-justify-between ck-items-center ck-mb-6">
        <h3 class="ck-text-xl ck-font-bold">Cache Management</h3>
    </div>

    <div class="ck-grid ck-grid-cols-1 md:ck-grid-cols-2 ck-gap-6 ck-mb-8">
        <div class="ck-p-4 ck-border ck-rounded ck-bg-gray-50">
            <h4 class="ck-font-bold ck-text-lg ck-mb-2">Page Cache</h4>
            <p class="ck-text-sm ck-text-gray-600 ck-mb-4">
                Status: <span class="ck-font-medium <?= $pageEnabled ? 'ck-text-green-600' : 'ck-text-red-600' ?>"><?= $pageEnabled ? 'Enabled' : 'Disabled' ?></span>
            </p>
            <form action="/admin/cache/clear-page" method="post" class="ck-inline">
                <?= csrf_field() ?>
                <button type="submit" class="ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded hover:ck-bg-blue-700">Clear Page Cache</button>
            </form>
        </div>

        <div class="ck-p-4 ck-border ck-rounded ck-bg-gray-50">
            <h4 class="ck-font-bold ck-text-lg ck-mb-2">Query Cache</h4>
            <p class="ck-text-sm ck-text-gray-600 ck-mb-4">
                Status: <span class="ck-font-medium <?= $queryEnabled ? 'ck-text-green-600' : 'ck-text-red-600' ?>"><?= $queryEnabled ? 'Enabled' : 'Disabled' ?></span>
            </p>
            <form action="/admin/cache/clear-query" method="post" class="ck-inline">
                <?= csrf_field() ?>
                <button type="submit" class="ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded hover:ck-bg-blue-700">Clear Query Cache</button>
            </form>
        </div>
    </div>

    <div class="ck-p-4 ck-border ck-rounded ck-bg-red-50">
        <h4 class="ck-font-bold ck-text-lg ck-mb-2 ck-text-red-700">Clear All Caches</h4>
        <p class="ck-text-sm ck-text-gray-600 ck-mb-4">This will remove all cached pages and query results.</p>
        <form action="/admin/cache/clear-all" method="post" class="ck-inline" onsubmit="return confirm('Clear all caches?')">
            <?= csrf_field() ?>
            <button type="submit" class="ck-px-4 ck-py-2 ck-bg-red-600 ck-text-white ck-rounded hover:ck-bg-red-700">Clear All</button>
        </form>
    </div>

    <div class="ck-mt-8 ck-text-sm ck-text-gray-500">
        <p>Enable cache from <a href="/admin/settings" class="ck-text-blue-600 hover:ck-text-blue-800">Settings</a> by setting <code>cache_enabled</code> to true and configuring <code>cache_ttl</code> (seconds).</p>
    </div>
</div>
