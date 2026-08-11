<?php $title = $title ?? 'Themes'; ?>
<div class="ck-bg-white ck-p-6 ck-rounded-lg ck-shadow">
    <h3 class="ck-text-xl ck-font-bold ck-mb-6">Themes</h3>

    <div class="ck-grid ck-grid-cols-1 md:ck-grid-cols-3 ck-gap-6">
        <?php foreach ($items as $item):
            $item = is_array($item) ? $item : (array) $item;
        ?>
            <div class="ck-border ck-rounded-lg ck-p-6">
                <h4 class="ck-text-lg ck-font-bold"><?= esc($item['name']) ?></h4>
                <p class="ck-text-gray-500 ck-text-sm ck-mt-2">Slug: <?= esc($item['slug']) ?></p>
                <?php if ($canActivate ?? false): ?>
                    <div class="ck-mt-4 ck-flex ck-gap-3">
                        <form action="/admin/themes/activate/<?= $item['id'] ?>" method="post" class="ck-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded hover:ck-bg-blue-700">Activate</button>
                        </form>
                        <a href="/admin/themes/config/<?= $item['id'] ?>" class="ck-px-4 ck-py-2 ck-bg-gray-600 ck-text-white ck-rounded hover:ck-bg-gray-700">Configure</a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($items)): ?>
        <p class="ck-text-gray-500 ck-text-center ck-py-8">No themes installed yet.</p>
    <?php endif; ?>
</div>
