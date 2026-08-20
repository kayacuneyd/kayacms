<?php $title = $title ?? 'Error Log'; ?>
<div class="ck-bg-white ck-rounded-lg ck-shadow">
    <div class="ck-p-6 ck-border-b ck-flex ck-items-center ck-justify-between ck-flex-wrap ck-gap-4">
        <div>
            <h3 class="ck-text-xl ck-font-bold">Error Log</h3>
            <p class="ck-text-sm ck-text-gray-500 ck-mt-1">PHP errors and browser-side JS errors are collected here.</p>
        </div>
        <div class="ck-flex ck-gap-4 ck-text-sm ck-text-gray-600">
            <span><strong><?= (int) ($counts['total'] ?? 0) ?></strong> total</span>
            <span><strong><?= (int) ($counts['unresolved'] ?? 0) ?></strong> unresolved</span>
            <span><strong><?= (int) ($counts['php'] ?? 0) ?></strong> PHP</span>
            <span><strong><?= (int) ($counts['js'] ?? 0) ?></strong> JS</span>
        </div>
    </div>

    <div class="ck-p-6 ck-border-b">
        <form method="get" class="ck-flex ck-gap-3 ck-flex-wrap ck-items-end">
            <div>
                <label class="ck-block ck-text-xs ck-text-gray-500 ck-mb-1">Source</label>
                <select name="source" class="ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
                    <option value="">All</option>
                    <option value="php" <?= ($filters['source'] ?? '') === 'php' ? 'selected' : '' ?>>PHP</option>
                    <option value="js" <?= ($filters['source'] ?? '') === 'js' ? 'selected' : '' ?>>JS</option>
                </select>
            </div>
            <div>
                <label class="ck-block ck-text-xs ck-text-gray-500 ck-mb-1">Level</label>
                <select name="level" class="ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
                    <option value="">All</option>
                    <?php foreach (['critical', 'error', 'warning', 'unhandledrejection'] as $lvl): ?>
                        <option value="<?= $lvl ?>" <?= ($filters['level'] ?? '') === $lvl ? 'selected' : '' ?>><?= esc(ucfirst($lvl)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="ck-block ck-text-xs ck-text-gray-500 ck-mb-1">Status</label>
                <select name="resolved" class="ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
                    <option value="">All</option>
                    <option value="0" <?= ($filters['resolved'] ?? '') === '0' ? 'selected' : '' ?>>Unresolved</option>
                    <option value="1" <?= ($filters['resolved'] ?? '') === '1' ? 'selected' : '' ?>>Resolved</option>
                </select>
            </div>
            <div class="ck-flex-1 ck-min-w-[200px]">
                <label class="ck-block ck-text-xs ck-text-gray-500 ck-mb-1">Search</label>
                <input type="text" name="search" value="<?= esc($filters['search'] ?? '') ?>" placeholder="Search message or URL..." class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
            </div>
            <button type="submit" class="ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded hover:ck-bg-blue-700">Filter</button>
            <a href="/admin/error-logs" class="ck-px-4 ck-py-2 ck-border ck-border-gray-300 ck-rounded hover:ck-bg-gray-50">Reset</a>
        </form>
    </div>

    <div class="ck-p-6 ck-space-y-3">
        <?php if (empty($items)): ?>
            <p class="ck-text-gray-500">No entries found.</p>
        <?php endif; ?>

        <?php foreach (($items ?? []) as $item): ?>
            <?php
                $isResolved = (int) ($item['resolved'] ?? 0) === 1;
                $level = $item['level'] ?? '';
                $badgeColor = in_array($level, ['critical', 'emergency', 'alert'], true) ? 'ck-bg-red-100 ck-text-red-700'
                    : (in_array($level, ['error', 'unhandledrejection'], true) ? 'ck-bg-orange-100 ck-text-orange-700'
                    : 'ck-bg-yellow-100 ck-text-yellow-700');
                $context = json_decode((string) ($item['context'] ?? ''), true) ?: [];
            ?>
            <details class="ck-border ck-border-gray-200 ck-rounded-md <?= $isResolved ? 'ck-opacity-50' : '' ?>">
                <summary class="ck-p-4 ck-cursor-pointer ck-flex ck-items-center ck-gap-3 ck-flex-wrap">
                    <span class="ck-px-2 ck-py-1 ck-rounded ck-text-xs ck-font-semibold <?= $badgeColor ?>"><?= esc(strtoupper($level)) ?></span>
                    <span class="ck-px-2 ck-py-1 ck-rounded ck-text-xs ck-bg-gray-100 ck-text-gray-700"><?= esc(strtoupper($item['source'] ?? '')) ?></span>
                    <span class="ck-text-sm ck-text-gray-500"><?= esc($item['created_at'] ?? '') ?></span>
                    <span class="ck-flex-1 ck-truncate ck-text-sm"><?= esc(mb_strimwidth((string) ($item['message'] ?? ''), 0, 140, '…')) ?></span>
                    <?php if ($isResolved): ?><span class="ck-text-xs ck-text-green-700">Resolved</span><?php endif; ?>
                </summary>
                <div class="ck-p-4 ck-border-t ck-border-gray-100 ck-bg-gray-50 ck-space-y-3">
                    <pre class="ck-text-xs ck-whitespace-pre-wrap ck-break-all"><?= esc($item['message'] ?? '') ?></pre>
                    <?php if (! empty($context['stack'])): ?>
                        <div>
                            <p class="ck-text-xs ck-font-semibold ck-text-gray-500 ck-mb-1">Stack</p>
                            <pre class="ck-text-xs ck-whitespace-pre-wrap ck-break-all ck-bg-white ck-p-2 ck-border ck-rounded"><?= esc($context['stack']) ?></pre>
                        </div>
                    <?php endif; ?>
                    <div class="ck-grid ck-grid-cols-1 md:ck-grid-cols-2 ck-gap-2 ck-text-xs ck-text-gray-600">
                        <div><strong>URL:</strong> <?= esc($item['url'] ?? '') ?></div>
                        <div><strong>Method:</strong> <?= esc($item['method'] ?? '') ?></div>
                        <div><strong>IP:</strong> <?= esc($item['ip_address'] ?? '') ?></div>
                        <div><strong>User agent:</strong> <?= esc($item['user_agent'] ?? '') ?></div>
                        <?php if (! empty($context['line'])): ?>
                            <div><strong>Line:Column:</strong> <?= esc((string) $context['line']) ?>:<?= esc((string) ($context['column'] ?? 0)) ?></div>
                        <?php endif; ?>
                        <?php if (! empty($context['source'])): ?>
                            <div><strong>File:</strong> <?= esc($context['source']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="ck-flex ck-gap-2">
                        <?php if ($isResolved): ?>
                            <form method="post" action="/admin/error-logs/unresolve/<?= (int) $item['id'] ?>"><?= csrf_field() ?>
                                <button type="submit" class="ck-px-3 ck-py-1 ck-text-sm ck-bg-gray-200 ck-rounded hover:ck-bg-gray-300">Mark unresolved</button>
                            </form>
                        <?php else: ?>
                            <form method="post" action="/admin/error-logs/resolve/<?= (int) $item['id'] ?>"><?= csrf_field() ?>
                                <button type="submit" class="ck-px-3 ck-py-1 ck-text-sm ck-bg-green-100 ck-text-green-700 ck-rounded hover:ck-bg-green-200">Mark resolved</button>
                            </form>
                        <?php endif; ?>
                        <form method="post" action="/admin/error-logs/delete/<?= (int) $item['id'] ?>" onsubmit="return confirm('Are you sure you want to delete this entry?');"><?= csrf_field() ?>
                            <button type="submit" class="ck-px-3 ck-py-1 ck-text-sm ck-bg-red-100 ck-text-red-700 ck-rounded hover:ck-bg-red-200">Delete</button>
                        </form>
                    </div>
                </div>
            </details>
        <?php endforeach; ?>
    </div>

    <?php if (($pages ?? 1) > 1): ?>
        <div class="ck-p-6 ck-pt-0 ck-flex ck-gap-2 ck-flex-wrap">
            <?php for ($p = 1; $p <= $pages; $p++): ?>
                <a href="?<?= http_build_query(array_merge($filters, ['page' => $p])) ?>" class="ck-px-3 ck-py-1 ck-rounded ck-border <?= $p === ($page ?? 1) ? 'ck-bg-blue-600 ck-text-white' : 'ck-bg-white' ?>"><?= $p ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>

    <?php if (($counts['total'] ?? 0) > 0): ?>
        <div class="ck-p-6 ck-pt-0">
            <form method="post" action="/admin/error-logs/clear-resolved" onsubmit="return confirm('Delete all resolved entries?');"><?= csrf_field() ?>
                <button type="submit" class="ck-px-4 ck-py-2 ck-border ck-border-gray-300 ck-rounded hover:ck-bg-gray-50 ck-text-sm">Clear resolved entries</button>
            </form>
        </div>
    <?php endif; ?>
</div>
