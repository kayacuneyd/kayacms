<?php $title = $title ?? 'Roles'; ?>
<?php
$currentPermissions = [];
if ($item && !empty($item->permissions)) {
    $currentPermissions = is_array($item->permissions) ? $item->permissions : [];
}
?>
<div class="ck-bg-white ck-rounded-lg ck-shadow">
    <div class="ck-p-6 ck-border-b">
        <h3 class="ck-text-xl ck-font-bold"><?= esc($item ? 'Edit Role' : 'New Role') ?></h3>
    </div>

    <form method="post" action="<?= $item ? '/admin/roles/update/' . $item->id : '/admin/roles/store' ?>" class="ck-p-6 ck-space-y-6">
        <?= csrf_field() ?>

        <div>
            <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Name</label>
            <input type="text" name="name" value="<?= esc($item->name ?? '') ?>" required class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
        </div>

        <div>
            <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Permissions</label>
            <div class="ck-space-y-4 ck-mt-2">
                <?php foreach ($permissionGroups as $group => $permissions): ?>
                    <div class="ck-border ck-rounded-md ck-p-4">
                        <h4 class="ck-font-bold ck-text-gray-800 ck-mb-3"><?= esc(ucfirst($group)) ?></h4>
                        <div class="ck-grid ck-grid-cols-1 md:ck-grid-cols-2 ck-gap-3">
                            <?php foreach ($permissions as $key => $label): ?>
                                <label class="ck-flex ck-items-center ck-space-x-2">
                                    <input type="checkbox" name="permissions[]" value="<?= esc($key) ?>" <?= in_array($key, $currentPermissions, true) ? 'checked' : '' ?>>
                                    <span class="ck-text-sm"><?= esc($label) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="ck-flex ck-items-center ck-gap-4">
            <button type="submit" class="ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded hover:ck-bg-blue-700">Save</button>
            <a href="/admin/roles" class="ck-px-4 ck-py-2 ck-border ck-border-gray-300 ck-rounded hover:ck-bg-gray-50">Cancel</a>
        </div>
    </form>
</div>
