<?php $title = $title ?? 'Security Audit'; ?>
<div class="ck-bg-white ck-rounded-lg ck-shadow ck-p-6">
    <div class="ck-flex ck-justify-between ck-items-center ck-mb-6">
        <h3 class="ck-text-xl ck-font-bold">Security Audit</h3>
        <a href="/admin/security" class="ck-text-sm ck-text-blue-600 hover:ck-underline">Back to Security</a>
    </div>

    <?php if (empty($issues)): ?>
        <p class="ck-text-green-700 ck-bg-green-50 ck-p-4 ck-rounded">No issues detected. Your configuration looks good.</p>
    <?php else: ?>
        <div class="ck-space-y-4">
            <?php foreach ($issues as $issue): ?>
                <div class="ck-p-4 ck-rounded ck-border <?= $issue['severity'] === 'high' ? 'ck-bg-red-50 ck-border-red-300' : ($issue['severity'] === 'info' ? 'ck-bg-blue-50 ck-border-blue-300' : 'ck-bg-yellow-50 ck-border-yellow-300') ?>">
                    <div class="ck-flex ck-items-center ck-justify-between">
                        <p class="ck-font-medium"><?= esc($issue['title']) ?></p>
                        <span class="ck-px-2 ck-py-1 ck-rounded ck-text-xs ck-font-medium ck-uppercase
                            <?= $issue['severity'] === 'high' ? 'ck-bg-red-200 ck-text-red-800' : ($issue['severity'] === 'info' ? 'ck-bg-blue-200 ck-text-blue-800' : 'ck-bg-yellow-200 ck-text-yellow-800') ?>
                        "><?= esc($issue['severity']) ?></span>
                    </div>
                    <p class="ck-text-sm ck-text-gray-600 ck-mt-1"><?= esc($issue['detail']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>