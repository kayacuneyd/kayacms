<?php $title = $title ?? 'API Tokens'; ?>
<div class="ck-bg-white ck-rounded-lg ck-shadow">
    <div class="ck-p-6 ck-border-b ck-flex ck-justify-between ck-items-center">
        <h3 class="ck-text-xl ck-font-bold">API Tokens</h3>
        <form method="post" action="/admin/api-tokens/store" class="ck-flex ck-items-center ck-gap-2">
            <?= csrf_field() ?>
            <input type="text" name="name" required placeholder="Token name (e.g. CI/CD)" class="ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md ck-text-sm">
            <button type="submit" class="ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded ck-text-sm hover:ck-bg-blue-700">Create</button>
        </form>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="ck-bg-green-50 ck-border ck-border-green-300 ck-text-green-800 ck-p-4 ck-mx-6 ck-mt-4 ck-rounded">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($tokens)): ?>
        <table class="ck-w-full">
            <thead class="ck-bg-gray-50">
                <tr>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Name</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Created</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Last Used</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Expires</th>
                    <th class="ck-px-6 ck-py-3 ck-text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tokens as $token): ?>
                    <tr class="ck-border-t">
                        <td class="ck-px-6 ck-py-4 ck-font-medium"><?= esc($token['name']) ?></td>
                        <td class="ck-px-6 ck-py-4 ck-text-sm"><?= esc($token['created_at'] ?? '-') ?></td>
                        <td class="ck-px-6 ck-py-4 ck-text-sm"><?= esc($token['last_used_at'] ?? 'never') ?></td>
                        <td class="ck-px-6 ck-py-4 ck-text-sm"><?= esc($token['expires_at'] ?? 'never') ?></td>
                        <td class="ck-px-6 ck-py-4">
                            <form action="/admin/api-tokens/revoke/<?= (int) $token['id'] ?>" method="post" class="ck-inline" onsubmit="return confirm('Revoke this token?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="ck-text-red-600 hover:ck-text-red-800 ck-text-sm">Revoke</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="ck-text-gray-500 ck-text-center ck-py-8">No API tokens yet.</p>
    <?php endif; ?>
</div>