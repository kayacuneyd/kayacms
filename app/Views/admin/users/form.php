<?php $title = $title ?? 'Users'; ?>
<div class="ck-bg-white ck-rounded-lg ck-shadow">
    <div class="ck-p-6 ck-border-b">
        <h3 class="ck-text-xl ck-font-bold"><?= esc($item ? 'Edit User' : 'New User') ?></h3>
    </div>

    <form method="post" action="<?= $item ? '/admin/users/update/' . $item->id : '/admin/users/store' ?>" class="ck-p-6 ck-space-y-6">
        <?= csrf_field() ?>

        <div>
            <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Username</label>
            <input type="text" name="username" value="<?= esc($item->username ?? '') ?>" required class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
        </div>

        <div>
            <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Email</label>
            <input type="email" name="email" value="<?= esc($item->email ?? '') ?>" required class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
        </div>

        <div>
            <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Password</label>
            <input type="password" name="password" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
            <?php if ($item): ?>
                <p class="ck-text-xs ck-text-gray-500 ck-mt-1">Leave blank to keep current password</p>
            <?php endif; ?>
        </div>

        <div>
            <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Role</label>
            <select name="role_id" required class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
                <option value="">Select role</option>
                <?php foreach ($roles as $role): ?>
                    <option value="<?= $role->id ?>" <?= ($item->role_id ?? '') == $role->id ? 'selected' : '' ?>><?= esc($role->name) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Status</label>
            <select name="status" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
                <?php foreach (['active', 'inactive', 'banned'] as $status): ?>
                    <option value="<?= $status ?>" <?= ($item->status ?? 'active') === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="ck-flex ck-items-center ck-gap-4">
            <button type="submit" class="ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded hover:ck-bg-blue-700">Save</button>
            <a href="/admin/users" class="ck-px-4 ck-py-2 ck-border ck-border-gray-300 ck-rounded hover:ck-bg-gray-50">Cancel</a>
        </div>
    </form>
</div>
