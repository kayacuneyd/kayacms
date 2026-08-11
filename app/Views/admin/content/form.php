<?php $title = $title ?? 'Content'; ?>
<?php
$isTranslation = !empty($source);
$locales = $locales ?? ['tr'];
$defaultLocale = $defaultLocale ?? 'tr';
?>
<div class="ck-bg-white ck-rounded-lg ck-shadow">
    <div class="ck-p-6 ck-border-b">
        <h3 class="ck-text-xl ck-font-bold"><?= esc($title) ?></h3>
        <?php if ($isTranslation): ?>
            <p class="ck-text-sm ck-text-gray-500 ck-mt-1">
                Translating <strong><?= esc($source->title) ?></strong> from <code><?= esc($source->locale) ?></code> to <code><?= esc($targetLocale) ?></code>
            </p>
        <?php endif; ?>
    </div>

    <form id="content-form" method="post" action="<?= $item ? '/admin/content/update/' . $item->id : '/admin/content/store' ?>" class="ck-p-6 ck-space-y-6">
        <?= csrf_field() ?>
        <input type="hidden" name="translation_group_id" value="<?= esc($item->translation_group_id ?? $source->translation_group_id ?? '') ?>">

        <div class="ck-grid ck-grid-cols-1 md:ck-grid-cols-3 ck-gap-6">
            <div>
                <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Title</label>
                <input type="text" id="title-input" name="title" value="<?= esc($item->title ?? ($source->title ?? '')) ?>" required class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
            </div>

            <div>
                <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Locale</label>
                <select name="locale" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
                    <?php foreach ($locales as $loc): ?>
                        <option value="<?= $loc ?>" <?= ($item->locale ?? ($targetLocale ?: $defaultLocale)) === $loc ? 'selected' : '' ?>><?= strtoupper($loc) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Status</label>
                <select name="status" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
                    <?php foreach (['draft', 'published', 'archived'] as $status): ?>
                        <option value="<?= $status ?>" <?= ($item->status ?? 'draft') === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div>
            <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Slug</label>
            <div class="ck-flex ck-gap-2">
                <input type="text" id="slug-input" name="slug" value="<?= esc($item->slug ?? ($source->slug ?? '')) ?>" class="ck-flex-1 ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
                <button type="button" id="generate-slug" class="ck-px-4 ck-py-2 ck-bg-gray-200 ck-text-gray-700 ck-rounded hover:ck-bg-gray-300">Generate</button>
            </div>
            <p class="ck-text-xs ck-text-gray-500 ck-mt-1">URL-friendly identifier. Auto-generated from title.</p>
        </div>

        <div>
            <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Content Type</label>
            <select id="content-type-select" name="content_type" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
                <?php foreach (['article', 'page', 'product'] as $type): ?>
                    <option value="<?= $type ?>" <?= ($item->content_type ?? ($source->content_type ?? 'article')) === $type ? 'selected' : '' ?>><?= ucfirst($type) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div id="custom-fields-block" class="ck-bg-gray-50 ck-p-4 ck-rounded-md ck-space-y-4">
            <h4 class="ck-font-semibold ck-text-gray-800">Custom Fields</h4>
            <div id="custom-fields"></div>
            <p id="custom-fields-empty" class="ck-text-sm ck-text-gray-500">No custom fields defined for this content type. Manage them under <a href="/admin/content/schemas" class="ck-text-blue-600 hover:ck-underline">Custom Fields</a>.</p>
        </div>
        <script>
            const customSchemas = <?= $schemas ?? '{}' ?>;
            const customValues = <?= json_encode($customValues ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

            (function () {
                const container = document.getElementById('custom-fields');
                const emptyMsg = document.getElementById('custom-fields-empty');
                const typeSelect = document.getElementById('content-type-select');

                function escAttr(v) {
                    return String(v === null || v === undefined ? '' : v)
                        .replace(/&/g, '&amp;').replace(/"/g, '&quot;')
                        .replace(/</g, '&lt;').replace(/>/g, '&gt;');
                }

                function fieldValue(field) {
                    const stored = customValues[field.name];
                    if (stored !== undefined && stored !== null) return stored;
                    return field.default !== undefined && field.default !== null ? field.default : '';
                }

                function renderSelect(f, v) {
                    const options = (f.options || []).map(o =>
                        `<option value="${escAttr(o)}" ${String(o) === String(v) ? 'selected' : ''}>${escAttr(o)}</option>`
                    ).join('');
                    return `<select name="custom[${escAttr(f.name)}]" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">${options}</select>`;
                }

                function renderInput(f, v, type) {
                    const checked = (type === 'checkbox' || type === 'toggle')
                        ? (v ? 'checked' : '')
                        : '';
                    if (type === 'checkbox' || type === 'toggle') {
                        return `<label class="ck-inline-flex ck-items-center ck-gap-2">
                            <input type="checkbox" name="custom[${escAttr(f.name)}]" value="1" ${checked} class="ck-w-4 ck-h-4">
                            <input type="hidden" name="custom[${escAttr(f.name)}]" value="0">
                        </label>`;
                    }
                    return `<input type="${type}" name="custom[${escAttr(f.name)}]" value="${escAttr(v)}" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">`;
                }

                function renderField(f) {
                    const v = fieldValue(f);
                    const type = ['text', 'textarea', 'number', 'email', 'url', 'select', 'checkbox', 'date', 'datetime', 'toggle'].includes(f.type) ? f.type : 'text';
                    const required = f.required ? ' required' : '';
                    const label = f.label || f.name;

                    let control;
                    if (type === 'textarea') {
                        control = `<textarea name="custom[${escAttr(f.name)}]" rows="3" ${required} class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">${escAttr(v)}</textarea>`;
                    } else if (type === 'select') {
                        control = renderSelect(f, v);
                    } else if (type === 'checkbox' || type === 'toggle') {
                        control = renderInput(f, v, type);
                    } else {
                        control = renderInput(f, v, type);
                    }

                    return `<div class="ck-space-y-1">
                        <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700">${escAttr(label)}</label>
                        ${control}
                    </div>`;
                }

                function render() {
                    const fields = (customSchemas[typeSelect.value] || []).filter(f => f && f.name);
                    container.innerHTML = fields.map(renderField).join('');
                    emptyMsg.style.display = fields.length ? 'none' : 'block';
                }

                typeSelect.addEventListener('change', render);
                render();
            })();
        </script>

        <div>
            <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Body</label>
            <div id="editor-body" class="ck-w-full ck-border ck-border-gray-300 ck-rounded-md" style="min-height: 320px;"></div>
            <input type="hidden" name="body" id="body-input" value="<?= htmlspecialchars($item->body ?? ($source->body ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            <div class="ck-flex ck-items-center ck-justify-between ck-mt-2">
                <p class="ck-text-xs ck-text-gray-500">Source is stored as-is; output is sanitised through the ContentRenderer pipeline (<code>content.render.html</code> filter).</p>
                <button type="button" id="preview-toggle" class="ck-px-4 ck-py-2 ck-bg-gray-100 ck-text-gray-700 ck-rounded hover:ck-bg-gray-200">Preview Rendered HTML</button>
            </div>
            <div id="content-preview" class="ck-hidden ck-mt-3 ck-p-4 ck-border ck-rounded ck-bg-gray-50 ck-scaled-content"></div>
        </div>

        <div>
            <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Excerpt</label>
            <textarea name="excerpt" rows="3" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md"><?= esc($item->excerpt ?? ($source->excerpt ?? '')) ?></textarea>
        </div>

        <div>
            <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Featured Image URL</label>
            <input type="text" name="featured_image" value="<?= esc($item->featured_image ?? ($source->featured_image ?? '')) ?>" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
        </div>

        <div class="ck-bg-gray-50 ck-p-4 ck-rounded-md ck-space-y-4">
            <h4 class="ck-font-semibold ck-text-gray-800">SEO Settings</h4>
            <div class="ck-grid ck-grid-cols-1 md:ck-grid-cols-2 ck-gap-6">
                <div>
                    <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Meta Title</label>
                    <input type="text" name="meta_title" value="<?= esc($item->meta_title ?? ($source->meta_title ?? '')) ?>" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
                </div>
                <div>
                    <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Canonical URL</label>
                    <input type="text" name="canonical_url" value="<?= esc($item->canonical_url ?? ($source->canonical_url ?? '')) ?>" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
                </div>
            </div>
            <div>
                <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Meta Description</label>
                <textarea id="meta-desc-input" name="meta_description" rows="3" maxlength="160" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md"><?= esc($item->meta_description ?? ($source->meta_description ?? '')) ?></textarea>
                <p class="ck-text-xs ck-text-gray-500 ck-mt-1"><span id="meta-desc-count">0</span>/160 characters</p>
            </div>
            <div>
                <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Meta Keywords</label>
                <input type="text" name="meta_keywords" value="<?= esc($item->meta_keywords ?? ($source->meta_keywords ?? '')) ?>" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
            </div>
        </div>

        <div>
            <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Published At</label>
            <input type="datetime-local" name="published_at" value="<?= esc($item->published_at ?? '') ?>" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
        </div>

        <div class="ck-flex ck-items-center ck-gap-2">
            <input type="checkbox" name="is_featured" id="is-featured" value="1" <?= ($item->is_featured ?? false) ? 'checked' : '' ?> class="ck-w-4 ck-h-4">
            <label for="is-featured" class="ck-text-sm ck-font-medium ck-text-gray-700">Featured content</label>
        </div>

        <div>
            <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Related Content</label>
            <select name="related_ids[]" multiple size="6" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
                <?php foreach ($allContent ?? [] as $c): ?>
                    <?php if ($item && (int) $c->id === (int) $item->id) { continue; } ?>
                    <option value="<?= (int) $c->id ?>" <?= in_array((int) $c->id, $relatedIds ?? [], true) ? 'selected' : '' ?>>
                        <?= esc($c->title) ?> (<?= esc($c->content_type) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <p class="ck-text-xs ck-text-gray-500 ck-mt-1">Hold Ctrl/Cmd to select multiple related items.</p>
        </div>

        <div class="ck-flex ck-items-center ck-gap-4">
            <button type="submit" class="ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded hover:ck-bg-blue-700">Save</button>
            <a href="/admin/content" class="ck-px-4 ck-py-2 ck-border ck-border-gray-300 ck-rounded hover:ck-bg-gray-50">Cancel</a>
        </div>
    </form>
</div>

<script src="<?= base_url('assets/vendor/ckeditor/ckeditor.js') ?>"></script>
<script>
    function slugify(text) {
        return text
            .toString()
            .toLowerCase()
            .trim()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^\w\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/--+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    const titleInput = document.getElementById('title-input');
    const slugInput = document.getElementById('slug-input');
    const generateSlugBtn = document.getElementById('generate-slug');

    generateSlugBtn.addEventListener('click', function () {
        if (titleInput.value) {
            slugInput.value = slugify(titleInput.value);
        }
    });

    titleInput.addEventListener('blur', function () {
        if (!slugInput.value && titleInput.value) {
            slugInput.value = slugify(titleInput.value);
        }
    });

    const metaDescInput = document.getElementById('meta-desc-input');
    const metaDescCount = document.getElementById('meta-desc-count');
    function updateMetaCount() {
        metaDescCount.textContent = (metaDescInput.value || '').length;
    }
    metaDescInput.addEventListener('input', updateMetaCount);
    updateMetaCount();

    const previewToggle = document.getElementById('preview-toggle');
    const contentPreview = document.getElementById('content-preview');

    previewToggle.addEventListener('click', async function () {
        const body = document.getElementById('body-input').value;
        const form = document.getElementById('content-form');
        const csrf = form.querySelector('input[name="<?= csrf_token() ?>"]');

        try {
            const res = await fetch('/admin/content/preview', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({ body, '<?= csrf_token() ?>': csrf ? csrf.value : '' })
            });

            const html = await res.text();
            contentPreview.innerHTML = html;
            contentPreview.classList.remove('ck-hidden');
        } catch (e) {
            contentPreview.textContent = 'Preview failed: ' + e;
            contentPreview.classList.remove('ck-hidden');
        }
    });

    ClassicEditor
        .create(document.querySelector('#editor-body'), {
            toolbar: [
                'heading', '|',
                'bold', 'italic', 'link', '|',
                'bulletedList', 'numberedList', 'blockQuote', '|',
                'insertTable', 'mediaEmbed', '|',
                'undo', 'redo'
            ],
            heading: {
                options: [
                    { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                    { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                    { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
                    { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' }
                ]
            }
        })
        .then(editor => {
            const initialBody = document.getElementById('body-input').value;
            if (initialBody) {
                editor.setData(initialBody);
            }

            document.getElementById('content-form').addEventListener('submit', function () {
                document.getElementById('body-input').value = editor.getData();
            });
        })
        .catch(error => {
            console.error('CKEditor error:', error);
        });
</script>
