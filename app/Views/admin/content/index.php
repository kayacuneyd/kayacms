<?php $title = $title ?? 'Content'; ?>
<div class="ck-bg-white ck-rounded-lg ck-shadow">
    <div class="ck-p-6 ck-border-b ck-flex ck-justify-between ck-items-center">
        <h3 class="ck-text-xl ck-font-bold">All Content</h3>
        <?php if ($canCreate ?? false): ?>
            <a href="/admin/content/create" class="ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded hover:ck-bg-blue-700">
                + New Content
            </a>
        <?php endif; ?>
    </div>

    <form method="get" action="/admin/content" class="ck-p-6 ck-border-b">
        <div class="ck-flex ck-gap-4">
            <input type="text" name="search" value="<?= esc($search ?? '') ?>" placeholder="Search content..." class="ck-flex-1 ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
            <select name="locale" class="ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
                <option value="">All Locales</option>
                <?php foreach ($locales ?? [] as $loc): ?>
                    <option value="<?= $loc ?>" <?= ($locale ?? '') === $loc ? 'selected' : '' ?>><?= strtoupper($loc) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="ck-px-4 ck-py-2 ck-bg-gray-800 ck-text-white ck-rounded hover:ck-bg-gray-900">Search</button>
        </div>
    </form>

    <?php if (!empty($items)): ?>
        <table class="ck-w-full">
            <thead class="ck-bg-gray-50">
                <tr>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Title</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Locale</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Status</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Type</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Featured</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr class="ck-border-t">
                        <td class="ck-px-6 ck-py-4"><?= esc($item->title ?? '') ?><?= ($item->is_featured ?? false) ? ' <span class="ck-text-yellow-500">★</span>' : '' ?></td>
                        <td class="ck-px-6 ck-py-4">
                            <span class="ck-px-2 ck-py-1 ck-bg-blue-100 ck-text-blue-800 ck-text-xs ck-rounded"><?= esc($item->locale ?? 'tr') ?></span>
                        </td>
                        <td class="ck-px-6 ck-py-4"><?= esc($item->status ?? '') ?></td>
                        <td class="ck-px-6 ck-py-4"><?= esc($item->content_type ?? '') ?></td>
                        <td class="ck-px-6 ck-py-4">
                            <?php if ($canEdit ?? false): ?>
                                <form action="/admin/content/featured/<?= $item->id ?? '' ?>" method="post">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="featured" value="<?= ($item->is_featured ?? false) ? '0' : '1' ?>">
                                    <button type="submit" class="ck-text-lg ck-bg-transparent <?= ($item->is_featured ?? false) ? 'ck-text-yellow-500' : 'ck-text-gray-300' ?>" title="<?= ($item->is_featured ?? false) ? 'Unfeature' : 'Feature' ?>">★</button>
                                </form>
                            <?php endif; ?>
                        </td>
                        <td class="ck-px-6 ck-py-4">
                            <?php if ($canEdit ?? false): ?>
                                <a href="/admin/content/edit/<?= $item->id ?? '' ?>" class="ck-text-blue-600 hover:ck-text-blue-800">Edit</a>
                                <a href="/admin/content/revisions/<?= $item->id ?? '' ?>" class="ck-text-gray-600 hover:ck-text-gray-800 ck-ml-4">Revisions</a>
                                <a href="/admin/content/translations/<?= $item->id ?? '' ?>" class="ck-text-green-600 hover:ck-text-green-800 ck-ml-4">Translations</a>
                            <?php endif; ?>
                            <?php if ($canDelete ?? false): ?>
                                <form action="/admin/content/delete/<?= $item->id ?? '' ?>" method="post" class="ck-inline" onsubmit="return confirm('Are you sure?')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="ck-text-red-600 hover:ck-text-red-800 ck-ml-4">Delete</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if (!empty($pagination['total_pages']) && $pagination['total_pages'] > 1): ?>
            <div class="ck-p-6 ck-flex ck-justify-between ck-items-center">
                <span class="ck-text-sm ck-text-gray-500">
                    Page <?= $pagination['current_page'] ?> of <?= $pagination['total_pages'] ?> (<?= $pagination['total_items'] ?> items)
                </span>
                <div class="ck-space-x-2">
                    <?php if ($pagination['current_page'] > 1): ?>
                        <a href="?page=<?= $pagination['current_page'] - 1 ?>&search=<?= urlencode($search ?? '') ?>&locale=<?= urlencode($locale ?? '') ?>" class="ck-px-3 ck-py-1 ck-border ck-rounded hover:ck-bg-gray-50">Previous</a>
                    <?php endif; ?>
                    <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                        <a href="?page=<?= $pagination['current_page'] + 1 ?>&search=<?= urlencode($search ?? '') ?>&locale=<?= urlencode($locale ?? '') ?>" class="ck-px-3 ck-py-1 ck-border ck-rounded hover:ck-bg-gray-50">Next</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <p class="ck-text-gray-500 ck-text-center ck-py-8">No content yet</p>
    <?php endif; ?>
</div>
