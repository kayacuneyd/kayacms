<div class="bp-admin-stack">
    <div class="bp-admin-actionbar">
        <div>
            <h2 class="ck-text-xl ck-font-bold ck-m-0">RSS Ideas</h2>
            <p class="bp-admin-help">External feeds are stored as an idea pool. Nothing is published automatically.</p>
        </div>
        <div class="bp-admin-row-actions">
            <form method="post" action="<?= site_url('admin/rss/items/purge-old') ?>" onsubmit="return confirm('Delete all old RSS ideas? Created articles are not affected.');">
                <?= csrf_field() ?>
                <button class="bp-admin-secondary-button" type="submit">Eski RSS fikirlerini temizle</button>
            </form>
            <a class="bp-admin-secondary-button" href="<?= site_url('admin/rss/sources') ?>">Sources</a>
        </div>
    </div>

    <div class="bp-admin-actionbar">
        <div class="ck-flex ck-gap-2 ck-flex-wrap">
            <?php foreach (['' => 'All', 'new' => 'New', 'shortlisted' => 'Shortlisted', 'drafted' => 'Drafted', 'ignored' => 'Ignored'] as $key => $label): ?>
                <a class="bp-admin-secondary-button <?= ($status ?? '') === $key ? 'is-active' : '' ?>" href="<?= site_url('admin/rss/inbox' . ($key !== '' ? '?status=' . $key : '')) ?>">
                    <?= esc($label) ?><?= $key !== '' ? ' (' . (int) ($counts[$key] ?? 0) . ')' : '' ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <form id="rss-bulk-form" method="post" action="<?= site_url('admin/rss/items/bulk') ?>" onsubmit="return this.bulk_action.value === 'delete_selected' ? confirm('Delete selected RSS ideas? Created articles are not affected.') : true;">
        <?= csrf_field() ?>
    </form>

    <div class="bp-admin-actionbar">
        <div class="ck-flex ck-gap-2 ck-flex-wrap ck-items-center">
            <select class="bp-admin-select" name="bulk_action" form="rss-bulk-form" aria-label="Bulk RSS action">
                <option value="delete_selected">Seçilenleri sil</option>
            </select>
            <button class="bp-admin-secondary-button" type="submit" form="rss-bulk-form">Uygula</button>
        </div>
        <p class="bp-admin-help">RSS ideas are a temporary pool; deleting them does not delete any drafts/articles already created from them.</p>
    </div>

    <div class="bp-admin-table-wrap">
        <table class="bp-admin-table">
            <thead>
                <tr>
                    <th><input type="checkbox" data-rss-select-all aria-label="Tüm RSS fikirlerini seç"></th>
                    <th>Item</th>
                    <th>Source</th>
                    <th>Status / Kullanım</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (($items ?? []) as $item): ?>
                    <?php
                    $suggestion = json_decode((string) ($item['ai_suggestion'] ?? ''), true) ?: [];
                    $createdContentId = (int) ($item['created_content_id'] ?? 0);
                    $hasCreatedContent = $createdContentId > 0 && ! empty($item['created_content_title']);
                    ?>
                    <tr class="<?= $hasCreatedContent ? 'is-rss-used' : '' ?>">
                        <td><input type="checkbox" name="item_ids[]" form="rss-bulk-form" value="<?= esc((string) $item['id']) ?>" aria-label="RSS fikrini seç: <?= esc($item['original_title']) ?>"></td>
                        <td>
                            <strong><?= esc($item['original_title']) ?></strong>
                            <p class="bp-admin-help"><?= esc(mb_strimwidth(strip_tags((string) $item['original_summary']), 0, 220, '...')) ?></p>
                            <?php if ($suggestion): ?><p class="bp-admin-help"><strong>Öneri:</strong> <?= esc($suggestion['baslik_onerisi'] ?? '') ?></p><?php endif; ?>
                            <a href="<?= esc($item['original_url']) ?>" target="_blank" rel="noopener">Original source</a>
                        </td>
                        <td>
                            <?= esc($item['source_name'] ?? '') ?><br>
                            <span class="bp-admin-help"><?= esc(($item['country'] ?? '') . ' / ' . ($item['language'] ?? '')) ?></span>
                            <?php if (! empty($item['published_at'])): ?><br><span class="bp-admin-help"><?= esc(date('d.m.Y H:i', strtotime($item['published_at']))) ?></span><?php endif; ?>
                        </td>
                        <td>
                            <span class="bp-status-pill bp-status-<?= esc($item['status']) ?>"><?= esc($item['status']) ?></span>
                            <?php if ($hasCreatedContent): ?>
                                <div class="bp-admin-used-marker">
                                    <span class="bp-status-pill bp-status-used">✓ Kullanıldı</span>
                                    <a href="<?= site_url('admin/content/edit/' . $createdContentId) ?>">#<?= esc((string) $createdContentId) ?> <?= esc($item['created_content_title']) ?></a>
                                    <span class="bp-admin-help">(<?= esc($item['created_content_status'] ?? 'draft') ?>)</span>
                                </div>
                            <?php elseif ($createdContentId > 0): ?>
                                <div class="bp-admin-used-marker"><span class="bp-status-pill bp-status-failed">İçerik bulunamadı</span></div>
                            <?php else: ?>
                                <label class="bp-admin-used-marker bp-admin-help"><input type="checkbox" disabled> Henüz kullanılmadı</label>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="bp-admin-row-actions">
                                <form method="post" action="<?= site_url('admin/rss/items/status/' . $item['id']) ?>"><?= csrf_field() ?><input type="hidden" name="status" value="ignored"><button class="bp-admin-icon-button" type="submit" title="Ignore" aria-label="Ignore"><i data-lucide="ban" aria-hidden="true"></i></button></form>
                                <form method="post" action="<?= site_url('admin/rss/items/status/' . $item['id']) ?>"><?= csrf_field() ?><input type="hidden" name="status" value="shortlisted"><button class="bp-admin-icon-button" type="submit" title="Shortlist" aria-label="Shortlist"><i data-lucide="star" aria-hidden="true"></i></button></form>
                                <form method="post" action="<?= site_url('admin/rss/items/suggest/' . $item['id']) ?>"><?= csrf_field() ?><button class="bp-admin-icon-button" type="submit" title="Generate suggestion" aria-label="Generate suggestion"><i data-lucide="sparkles" aria-hidden="true"></i></button></form>
                                <form method="post" action="<?= site_url('admin/rss/items/draft/' . $item['id']) ?>"><?= csrf_field() ?><button class="bp-admin-icon-button" type="submit" title="Create draft" aria-label="Create draft"><i data-lucide="file-plus-2" aria-hidden="true"></i></button></form>
                                <form method="post" action="<?= site_url('admin/rss/items/delete/' . $item['id']) ?>" onsubmit="return confirm('Delete this RSS idea? Any article already created from it is not affected.');"><?= csrf_field() ?><button class="bp-admin-icon-button" type="submit" title="Delete RSS idea" aria-label="Delete RSS idea"><i data-lucide="trash-2" aria-hidden="true"></i></button></form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($items)): ?><tr><td class="bp-empty-cell" colspan="5">No RSS ideas yet. Run <code>php spark rss:fetch</code>.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <script>
        document.querySelector('[data-rss-select-all]')?.addEventListener('change', function () {
            document.querySelectorAll('input[name="item_ids[]"]').forEach((checkbox) => { checkbox.checked = this.checked; });
        });
    </script>

    <?php
    $pagination = $pagination ?? [
        'current_page' => 1,
        'per_page' => 30,
        'total_items' => count($items ?? []),
        'total_pages' => 1,
        'query' => [],
    ];
    $currentPage = (int) ($pagination['current_page'] ?? 1);
    $totalPages = (int) ($pagination['total_pages'] ?? 1);
    $totalItems = (int) ($pagination['total_items'] ?? 0);
    $perPage = (int) ($pagination['per_page'] ?? 30);
    $from = $totalItems > 0 ? (($currentPage - 1) * $perPage) + 1 : 0;
    $to = min($totalItems, $currentPage * $perPage);
    ?>
    <div class="bp-admin-pagination-wrap" aria-label="RSS inbox pagination">
        <p class="bp-admin-help"><?= esc((string) $from) ?>-<?= esc((string) $to) ?> / <?= esc((string) $totalItems) ?> item shown</p>
        <?php if ($totalPages > 1): ?>
            <nav class="bp-admin-pagination">
                <?php
                $pageUrl = static function (int $page) use ($pagination): string {
                    $query = array_merge($pagination['query'] ?? [], ['page' => $page]);
                    return site_url('admin/rss/inbox?' . http_build_query($query));
                };
                $startPage = max(1, $currentPage - 2);
                $endPage = min($totalPages, $currentPage + 2);
                ?>
                <?php if ($currentPage > 1): ?>
                    <a class="bp-admin-page-link" href="<?= esc($pageUrl($currentPage - 1)) ?>">Prev</a>
                <?php endif; ?>
                <?php if ($startPage > 1): ?>
                    <a class="bp-admin-page-link" href="<?= esc($pageUrl(1)) ?>">1</a>
                    <?php if ($startPage > 2): ?><span class="bp-admin-page-gap">...</span><?php endif; ?>
                <?php endif; ?>
                <?php for ($page = $startPage; $page <= $endPage; $page++): ?>
                    <a class="bp-admin-page-link <?= $page === $currentPage ? 'is-active' : '' ?>" href="<?= esc($pageUrl($page)) ?>" aria-current="<?= $page === $currentPage ? 'page' : 'false' ?>">
                        <?= esc((string) $page) ?>
                    </a>
                <?php endfor; ?>
                <?php if ($endPage < $totalPages): ?>
                    <?php if ($endPage < $totalPages - 1): ?><span class="bp-admin-page-gap">...</span><?php endif; ?>
                    <a class="bp-admin-page-link" href="<?= esc($pageUrl($totalPages)) ?>"><?= esc((string) $totalPages) ?></a>
                <?php endif; ?>
                <?php if ($currentPage < $totalPages): ?>
                    <a class="bp-admin-page-link" href="<?= esc($pageUrl($currentPage + 1)) ?>">Next</a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    </div>
</div>
