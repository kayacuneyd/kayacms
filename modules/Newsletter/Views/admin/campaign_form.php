<form action="<?= $item ? '/admin/newsletter/campaigns/update/' . (int) $item['id'] : '/admin/newsletter/campaigns/store' ?>" method="post" class="ck-bg-white ck-p-6 ck-rounded ck-shadow ck-space-y-5">
    <div>
        <label class="ck-block ck-text-sm ck-font-medium ck-mb-1">Subject</label>
        <input name="subject" value="<?= esc($item['subject'] ?? '') ?>" required class="ck-w-full ck-px-3 ck-py-2 ck-border ck-rounded">
    </div>
    <div>
        <label class="ck-block ck-text-sm ck-font-medium ck-mb-1">Preheader</label>
        <input name="preheader" value="<?= esc($item['preheader'] ?? '') ?>" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-rounded">
    </div>
    <div class="ck-grid ck-grid-cols-1 md:ck-grid-cols-2 ck-gap-4">
        <div>
            <label class="ck-block ck-text-sm ck-font-medium ck-mb-1">List</label>
            <select name="list_id" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-rounded">
                <option value="">All subscribed</option>
                <?php foreach ($lists as $list): ?>
                    <option value="<?= (int) $list['id'] ?>" <?= (int) ($item['list_id'] ?? 0) === (int) $list['id'] ? 'selected' : '' ?>><?= esc($list['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="ck-block ck-text-sm ck-font-medium ck-mb-1">Provider</label>
            <select name="provider" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-rounded">
                <option value="smtp" <?= ($item['provider'] ?? 'smtp') === 'smtp' ? 'selected' : '' ?>>Queued SMTP</option>
                <option value="external" <?= ($item['provider'] ?? '') === 'external' ? 'selected' : '' ?>>External provider export</option>
            </select>
        </div>
        <div>
            <label class="ck-block ck-text-sm ck-font-medium ck-mb-1">Status</label>
            <select name="status" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-rounded">
                <?php foreach (['draft' => 'Draft', 'scheduled' => 'Scheduled', 'sending' => 'Sending', 'sent' => 'Sent'] as $value => $label): ?>
                    <option value="<?= esc($value) ?>" <?= ($item['status'] ?? 'draft') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="ck-block ck-text-sm ck-font-medium ck-mb-1">Scheduled send time</label>
            <?php $scheduledValue = ! empty($item['scheduled_at'] ?? '') ? date('Y-m-d\TH:i', strtotime((string) $item['scheduled_at'])) : ''; ?>
            <input type="datetime-local" name="scheduled_at" value="<?= esc($scheduledValue) ?>" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-rounded">
            <p class="ck-text-xs ck-text-gray-500 ck-mt-1">When status is Scheduled, the queue command will enqueue this campaign at/after this time.</p>
        </div>
    </div>
    <div>
        <label class="ck-block ck-text-sm ck-font-medium ck-mb-1">HTML Body</label>
        <textarea name="body_html" rows="16" required class="ck-w-full ck-px-3 ck-py-2 ck-border ck-rounded"><?= esc($item['body_html'] ?? '') ?></textarea>
        <p class="ck-text-xs ck-text-gray-500 ck-mt-1">Available tags: {{email}}, {{name}}, {{unsubscribe_url}}</p>
    </div>
    <div class="ck-flex ck-gap-3">
        <button class="ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded">Save Campaign</button>
        <a href="/admin/newsletter" class="ck-px-4 ck-py-2 ck-bg-white ck-border ck-rounded">Cancel</a>
    </div>
</form>
