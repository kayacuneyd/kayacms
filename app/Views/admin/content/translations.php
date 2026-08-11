<?php $title = $title ?? 'Translations'; ?>
<div class="ck-bg-white ck-rounded-lg ck-shadow">
    <div class="ck-p-6 ck-border-b ck-flex ck-justify-between ck-items-center">
        <div>
            <h3 class="ck-text-xl ck-font-bold">Translations</h3>
            <p class="ck-text-sm ck-text-gray-500 ck-mt-1"><?= esc($item->title) ?> <span class="ck-px-2 ck-py-1 ck-bg-blue-100 ck-text-blue-800 ck-text-xs ck-rounded"><?= esc($item->locale) ?></span></p>
        </div>
        <a href="/admin/content" class="ck-px-4 ck-py-2 ck-border ck-border-gray-300 ck-rounded hover:ck-bg-gray-50">Back</a>
    </div>

    <div class="ck-p-6 ck-border-b ck-bg-gray-50">
        <h4 class="ck-font-semibold ck-text-gray-800 ck-mb-4">Add New Translation</h4>
        <div class="ck-flex ck-gap-4 ck-flex-wrap">
            <?php foreach ($locales as $loc): ?>
                <?php if ($loc === $item->locale) continue; ?>
                <?php
                $hasTranslation = false;
                foreach ($translations as $t) {
                    if (($t->locale ?? null) === $loc) {
                        $hasTranslation = true;
                        break;
                    }
                }
                ?>
                <div class="ck-flex ck-items-center ck-gap-2 ck-bg-white ck-p-3 ck-rounded ck-border">
                    <span class="ck-font-medium"><?= strtoupper($loc) ?></span>
                    <?php if ($hasTranslation): ?>
                        <span class="ck-text-xs ck-text-green-600">✓ exists</span>
                    <?php else: ?>
                        <a href="/admin/content/create-translation/<?= $item->id ?>?locale=<?= $loc ?>" class="ck-text-sm ck-text-blue-600 hover:ck-text-blue-800">Create</a>
                    <?php endif; ?>
                    <a href="/admin/content/export-translation/<?= $item->id ?>/<?= $loc ?>" class="ck-text-sm ck-text-gray-600 hover:ck-text-gray-800">Export JSON</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="ck-p-6 ck-border-b">
        <h4 class="ck-font-semibold ck-text-gray-800 ck-mb-4">Import Translation JSON</h4>
        <form action="/admin/content/import-translation" method="post" enctype="multipart/form-data" class="ck-flex ck-gap-4 ck-items-end">
            <?= csrf_field() ?>
            <div class="ck-flex-1">
                <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Translation JSON File</label>
                <input type="file" name="translation_file" accept="application/json,.json" required class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
            </div>
            <button type="submit" class="ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded hover:ck-bg-blue-700">Import</button>
        </form>
    </div>

    <?php if (!empty($translations)): ?>
        <table class="ck-w-full">
            <thead class="ck-bg-gray-50">
                <tr>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Locale</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Title</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Slug</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Status</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($translations as $t): ?>
                    <tr class="ck-border-t">
                        <td class="ck-px-6 ck-py-4"><span class="ck-px-2 ck-py-1 ck-bg-blue-100 ck-text-blue-800 ck-text-xs ck-rounded"><?= esc($t->locale) ?></span></td>
                        <td class="ck-px-6 ck-py-4"><?= esc($t->title) ?></td>
                        <td class="ck-px-6 ck-py-4"><?= esc($t->slug) ?></td>
                        <td class="ck-px-6 ck-py-4"><?= esc($t->status) ?></td>
                        <td class="ck-px-6 ck-py-4">
                            <a href="/admin/content/edit/<?= $t->id ?>" class="ck-text-blue-600 hover:ck-text-blue-800">Edit</a>
                            <a href="/admin/content/export-translation/<?= $t->id ?>/<?= $t->locale ?>" class="ck-text-gray-600 hover:ck-text-gray-800 ck-ml-4">Export</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="ck-text-gray-500 ck-text-center ck-py-8">No translations yet</p>
    <?php endif; ?>
</div>
