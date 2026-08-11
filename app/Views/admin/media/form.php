<?php $title = $title ?? 'Upload Media'; ?>
<div class="ck-bg-white ck-rounded-lg ck-shadow">
    <div class="ck-p-6 ck-border-b">
        <h3 class="ck-text-xl ck-font-bold">Upload Media</h3>
        <p class="ck-text-sm ck-text-gray-500 ck-mt-1">You can select multiple files at once.</p>
    </div>

    <form method="post" action="/admin/media/store" enctype="multipart/form-data" class="ck-p-6 ck-space-y-6" id="upload-form">
        <?= csrf_field() ?>

        <div
            id="drop-zone"
            class="ck-border-2 ck-border-dashed ck-border-gray-300 ck-rounded-lg ck-p-8 ck-text-center ck-cursor-pointer hover:ck-bg-gray-50"
            onclick="document.getElementById('file-input').click()"
        >
            <p class="ck-text-gray-600">Drag and drop files here or click to browse</p>
            <input type="file" id="file-input" name="files[]" multiple class="ck-hidden" onchange="previewFiles(this)">
            <div id="file-preview" class="ck-mt-4">
                <div id="preview-list" class="ck-grid ck-grid-cols-3 ck-gap-3"></div>
            </div>
        </div>

        <div>
            <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Alt Text</label>
            <input type="text" name="alt_text" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
        </div>

        <div>
            <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Folder</label>
            <select name="folder_id" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
                <option value="">Root</option>
                <?php foreach (($folders ?? []) as $folder): ?>
                    <option value="<?= $folder['id'] ?>" <?= ($folderId ?? 0) === $folder['id'] ? 'selected' : '' ?>><?= $folder['label'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="ck-flex ck-items-center ck-gap-4">
            <button type="submit" class="ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded hover:ck-bg-700">Upload</button>
            <a href="/admin/media" class="ck-px-4 ck-py-2 ck-border ck-border-gray-300 ck-rounded hover:ck-bg-gray-50">Cancel</a>
        </div>
    </form>
</div>

<script>
    const dropZone = document.getElementById('drop-zone');

    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('ck-bg-gray-50');
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('ck-bg-gray-50');
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('ck-bg-gray-50');
        const files = e.dataTransfer.files;
        if (files.length) {
            document.getElementById('file-input').files = files;
            previewFiles(document.getElementById('file-input'));
        }
    });

    const form = document.querySelector('#upload-form');
    form.addEventListener('submit', (e) => {
        const fileInput = document.getElementById('file-input');
        if (!fileInput.files || fileInput.files.length === 0) {
            e.preventDefault();
            alert('Please select at least one file to upload.');
        }
    });

    function previewFiles(input) {
        const list = document.getElementById('preview-list');
        list.innerHTML = '';

        Array.from(input.files).forEach((file) => {
            const div = document.createElement('div');
            div.className = 'ck-border ck-rounded ck-p-2 ck-text-center';

            if (file.type.startsWith('image/')) {
                const img = document.createElement('img');
                img.className = 'ck-w-full ck-h-24 ck-object-cover ck-rounded';
                const reader = new FileReader();
                reader.onload = (e) => { img.src = e.target.result; };
                reader.readAsDataURL(file);
                div.appendChild(img);
            } else {
                const span = document.createElement('span');
                span.className = 'ck-text-gray-400 ck-text-xs';
                span.textContent = file.type || 'File';
                div.appendChild(span);
            }

            const name = document.createElement('p');
            name.className = 'ck-text-xs ck-text-gray-600 ck-truncate ck-mt-1';
            name.textContent = file.name;
            div.appendChild(name);

            list.appendChild(div);
        });
    }
</script>