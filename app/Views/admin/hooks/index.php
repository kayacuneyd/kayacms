<?php $title = $title ?? 'Hooks & Events'; ?>
<div class="ck-bg-white ck-p-6 ck-rounded-lg ck-shadow">
    <div class="ck-flex ck-justify-between ck-items-center ck-mb-6">
        <h3 class="ck-text-xl ck-font-bold">Hooks & Events</h3>
    </div>

    <p class="ck-text-sm ck-text-gray-600 ck-mb-6">
        Internal event system used by modules and plugins. Actions fire a side
        effect with <code>Hooks::doAction('hook', ...args)</code>; filters transform
        a value with <code>Hooks::applyFilters('hook', $value, ...args)</code>.
        Register with <code>Hooks::addAction</code> / <code>Hooks::addFilter</code>.
    </p>

    <h4 class="ck-font-bold ck-text-lg ck-mb-3">Documented Events</h4>
    <table class="ck-w-full ck-text-sm ck-mb-8">
        <thead>
            <tr class="ck-text-left ck-border-b ck-border-gray-200 ck-text-gray-500">
                <th class="ck-py-2 ck-pr-4">Hook</th>
                <th class="ck-py-2 ck-pr-4">Type</th>
                <th class="ck-py-2">Description</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach (($events ?? []) as $hook => $info): ?>
                <tr class="ck-border-b ck-border-gray-100">
                    <td class="ck-py-2 ck-pr-4 ck-font-mono"><?= esc($hook) ?></td>
                    <td class="ck-py-2 ck-pr-4">
                        <span class="ck-px-2 ck-py-1 ck-rounded ck-text-xs <?= $info['type'] === 'action' ? 'ck-bg-green-100 ck-text-green-700' : 'ck-bg-blue-100 ck-text-blue-700' ?>">
                            <?= esc($info['type']) ?>
                        </span>
                    </td>
                    <td class="ck-py-2"><?= esc($info['description']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h4 class="ck-font-bold ck-text-lg ck-mb-3">Registered This Request</h4>
    <?php $registered = $registered ?? ['filter' => [], 'action' => []]; ?>
    <?php if (empty($registered['action']) && empty($registered['filter'])): ?>
        <p class="ck-text-sm ck-text-gray-500 ck-p-4 ck-bg-gray-50 ck-rounded">
            No hooks registered during this request. Hooks are registered at runtime by
            modules and plugins via <code>Hooks::addAction</code>/<code>Hooks::addFilter</code>;
            this page is a reference listing of the events the CMS dispatches.
        </p>
    <?php else: ?>
        <table class="ck-w-full ck-text-sm">
            <thead>
                <tr class="ck-text-left ck-border-b ck-border-gray-200 ck-text-gray-500">
                    <th class="ck-py-2 ck-pr-4">Hook</th>
                    <th class="ck-py-2 ck-pr-4">Type</th>
                    <th class="ck-py-2">Callbacks</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registered as $type => $hooksList): ?>
                    <?php foreach ($hooksList as $hook => $count): ?>
                        <tr class="ck-border-b ck-border-gray-100">
                            <td class="ck-py-2 ck-pr-4 ck-font-mono"><?= esc($hook) ?></td>
                            <td class="ck-py-2 ck-pr-4"><?= esc($type) ?></td>
                            <td class="ck-py-2"><?= (int) $count ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>