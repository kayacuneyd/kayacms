<?php $title = $title ?? 'Submission'; ?>
<div class="ck-bg-white ck-p-6 ck-rounded-lg ck-shadow">
    <div class="ck-flex ck-justify-between ck-items-center ck-mb-6">
        <h3 class="ck-text-xl ck-font-bold">Submission #<?= $item['id'] ?></h3>
        <a href="/admin/contact-forms/submissions/<?= $form['id'] ?>" class="ck-px-4 ck-py-2 ck-border ck-border-gray-300 ck-rounded hover:ck-bg-gray-50">Back to Submissions</a>
    </div>

    <div class="ck-mb-4">
        <?php if ($item['status'] === 'new'): ?>
            <span class="ck-px-2 ck-py-1 ck-bg-red-100 ck-text-red-700 ck-rounded ck-text-xs">New</span>
        <?php elseif ($item['status'] === 'read'): ?>
            <span class="ck-px-2 ck-py-1 ck-bg-blue-100 ck-text-blue-700 ck-rounded ck-text-xs">Read</span>
        <?php else: ?>
            <span class="ck-px-2 ck-py-1 ck-bg-gray-100 ck-text-gray-600 ck-rounded ck-text-xs">Archived</span>
        <?php endif; ?>
        <span class="ck-text-sm ck-text-gray-500 ck-ml-4"><?= esc($item['created_at']) ?></span>
    </div>

    <div class="ck-space-y-4">
        <?php foreach ($item['data'] ?? [] as $key => $value): ?>
            <div class="ck-p-4 ck-bg-gray-50 ck-rounded">
                <p class="ck-text-xs ck-font-medium ck-text-gray-500 ck-uppercase"><?= esc(str_replace('_', ' ', $key)) ?></p>
                <p class="ck-text-gray-900 ck-whitespace-pre-wrap"><?= esc(is_array($value) ? implode(', ', $value) : $value) ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="ck-mt-6 ck-flex ck-gap-4">
        <?php if ($item['status'] !== 'read'): ?>
            <form action="/admin/contact-forms/submissions/status/<?= $item['id'] ?>/read" method="post" class="ck-inline">
                <?= csrf_field() ?>
                <button type="submit" class="ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded hover:ck-bg-blue-700">Mark as Read</button>
            </form>
        <?php endif; ?>
        <?php if ($item['status'] !== 'archived'): ?>
            <form action="/admin/contact-forms/submissions/status/<?= $item['id'] ?>/archived" method="post" class="ck-inline">
                <?= csrf_field() ?>
                <button type="submit" class="ck-px-4 ck-py-2 ck-bg-gray-600 ck-text-white ck-rounded hover:ck-bg-gray-700">Archive</button>
            </form>
        <?php endif; ?>
        <form action="/admin/contact-forms/submissions/delete/<?= $item['id'] ?>" method="post" class="ck-inline" onsubmit="return confirm('Delete this submission?')">
            <?= csrf_field() ?>
            <button type="submit" class="ck-px-4 ck-py-2 ck-bg-red-600 ck-text-white ck-rounded hover:ck-bg-red-700">Delete</button>
        </form>
    </div>
</div>
