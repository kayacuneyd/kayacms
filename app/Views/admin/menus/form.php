<?php $title = $title ?? 'Menus'; ?>
<div class="ck-bg-white ck-rounded-lg ck-shadow ck-mb-8">
    <div class="ck-p-6 ck-border-b">
        <h3 class="ck-text-xl ck-font-bold"><?= esc($item ? 'Edit Menu' : 'New Menu') ?></h3>
    </div>

    <form method="post" action="<?= $item ? '/admin/menus/update/' . $item['id'] : '/admin/menus/store' ?>" class="ck-p-6 ck-space-y-6">
        <?= csrf_field() ?>

        <div>
            <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Name</label>
            <input type="text" name="name" value="<?= esc($item['name'] ?? '') ?>" required class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
        </div>

        <div>
            <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Location</label>
            <input type="text" name="location" value="<?= esc($item['location'] ?? '') ?>" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
            <p class="ck-text-xs ck-text-gray-500 ck-mt-1">Examples: header, footer, sidebar</p>
        </div>

        <div class="ck-flex ck-items-center ck-gap-4">
            <button type="submit" class="ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded hover:ck-bg-blue-700">Save Menu</button>
            <a href="/admin/menus" class="ck-px-4 ck-py-2 ck-border ck-border-gray-300 ck-rounded hover:ck-bg-gray-50">Cancel</a>
        </div>
    </form>
</div>

<?php if ($item): ?>
<div class="ck-bg-white ck-rounded-lg ck-shadow ck-mb-8">
    <div class="ck-p-6 ck-border-b">
        <h3 class="ck-text-xl ck-font-bold">Add Menu Item</h3>
    </div>

    <form method="post" action="/admin/menus/items/store/<?= $item['id'] ?>" class="ck-p-6 ck-space-y-6">
        <?= csrf_field() ?>

        <div class="ck-grid ck-grid-cols-1 md:ck-grid-cols-2 ck-gap-6">
            <div>
                <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Title</label>
                <input type="text" name="title" required class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
            </div>

            <div>
                <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">URL (or select content below)</label>
                <input type="text" name="url" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md" placeholder="/content/about">
            </div>
        </div>

        <div class="ck-grid ck-grid-cols-1 md:ck-grid-cols-2 ck-gap-6">
            <div>
                <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Link to Content</label>
                <select name="content_id" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
                    <option value="">-- None --</option>
                    <?php foreach ($contentOptions as $content): ?>
                        <option value="<?= $content->id ?? '' ?>"><?= esc($content->title ?? '') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Parent Item</label>
                <select name="parent_id" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
                    <option value="">-- Top level --</option>
                    <?php foreach ($menuItems as $mi): ?>
                        <?php if (empty($mi['parent_id'])): ?>
                            <option value="<?= $mi['id'] ?>"><?= esc($mi['title']) ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div>
            <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Target</label>
            <select name="target" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
                <option value="_self">Same window</option>
                <option value="_blank">New window</option>
            </select>
        </div>

        <button type="submit" class="ck-px-4 ck-py-2 ck-bg-green-600 ck-text-white ck-rounded hover:ck-bg-green-700">+ Add Item</button>
    </form>
</div>

<div class="ck-bg-white ck-rounded-lg ck-shadow">
    <div class="ck-p-6 ck-border-b ck-flex ck-justify-between ck-items-center">
        <h3 class="ck-text-xl ck-font-bold">Menu Items</h3>
        <button type="button" id="save-order" class="ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded hover:ck-bg-blue-700">Save Order</button>
    </div>

    <form id="reorder-form" method="post" action="/admin/menus/items/reorder/<?= $item['id'] ?>" class="ck-hidden">
        <?= csrf_field() ?>
        <input type="hidden" name="order" id="order-data">
    </form>

    <div class="ck-p-6">
        <?php if (!empty($menuItems)): ?>
            <ul id="menu-tree" class="ck-space-y-2">
                <?php foreach ($menuItems as $mi): ?>
                    <?php if (empty($mi['parent_id'])): ?>
                        <?= view('admin/menus/_item', ['item' => $mi, 'menuItems' => $menuItems, 'contentOptions' => $contentOptions]) ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="ck-text-gray-500 ck-text-center ck-py-8">No menu items yet.</p>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    document.querySelectorAll('.menu-children').forEach(el => {
        new Sortable(el, {
            group: 'nested',
            animation: 150,
            fallbackClass: 'dragging',
            onEnd: updateOrder
        });
    });

    new Sortable(document.getElementById('menu-tree'), {
        group: 'nested',
        animation: 150,
        onEnd: updateOrder
    });

    function updateOrder() {
        const root = document.getElementById('menu-tree');
        const order = serialize(root);
        document.getElementById('order-data').value = JSON.stringify(order);
    }

    function serialize(ul) {
        return Array.from(ul.children).map(li => {
            const children = li.querySelector(':scope > ul.menu-children');
            const obj = { id: li.dataset.id };
            if (children && children.children.length) {
                obj.children = serialize(children);
            }
            return obj;
        });
    }

    document.getElementById('save-order').addEventListener('click', () => {
        updateOrder();
        document.getElementById('reorder-form').submit();
    });

    document.querySelectorAll('.edit-item-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            document.getElementById('edit-form-' + id).classList.toggle('ck-hidden');
        });
    });
</script>
<?php endif; ?>
