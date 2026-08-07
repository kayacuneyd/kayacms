// Content Manager - Full CRUD with Editor.js (Page-based, not modal)
let editor = null;

// Toast Notification System
function showToast(message, type = 'info') {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
        <div class="ck-font-medium">${message}</div>
    `;
    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Initialize Editor.js
function initContentEditor(data = null) {
    if (editor) {
        editor.destroy();
    }

    // Check if tools are loaded
    const tools = {};

    if (typeof Header !== 'undefined') {
        tools.header = {
            class: Header,
            config: {
                levels: [1, 2, 3, 4],
                defaultLevel: 2
            }
        };
    }

    if (typeof List !== 'undefined') {
        tools.list = {
            class: List,
            inlineToolbar: true
        };
    }

    if (typeof Quote !== 'undefined') {
        tools.quote = Quote;
    }

    if (typeof ImageTool !== 'undefined') {
        tools.image = {
            class: ImageTool,
            config: {
                endpoints: {
                    byFile: '/api/media/upload',
                    byUrl: '/api/media/fetch-url'
                },
                additionalRequestHeaders: {
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
                }
            }
        };
    }

    editor = new EditorJS({
        holder: 'editorjs',
        tools: tools,
        data: data,
        placeholder: 'Start writing your content...',
        minHeight: 400
    });
}

// Setup content editor page
function setupContentEditorPage(contentId = null) {
    // Auto-generate slug from title
    const titleInput = document.getElementById('content-title');
    const slugInput = document.getElementById('content-slug');

    if (titleInput && slugInput) {
        titleInput.addEventListener('input', (e) => {
            const title = e.target.value;
            const slug = title.toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/(^-|-$)/g, '');
            slugInput.value = slug;
        });
    }

    // Setup form submission
    const form = document.getElementById('content-form');
    if (form) {
        form.addEventListener('submit', saveContent);
    }

    // Load content for editing or initialize empty editor
    if (contentId) {
        loadContentForEdit(contentId);
    } else {
        initContentEditor();
    }
}

// Load content for editing
async function loadContentForEdit(id) {
    try {
        const token = localStorage.getItem('auth_token');
        const response = await fetch(`/api/content/${id}`, {
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });

        const result = await response.json();
        if (result.success && result.data) {
            const content = result.data;

            document.getElementById('content-id').value = content.id;
            document.getElementById('content-title').value = content.title;
            document.getElementById('content-slug').value = content.slug;
            document.getElementById('content-type').value = content.content_type;
            document.getElementById('content-status').value = content.status;
            document.getElementById('content-excerpt').value = content.excerpt || '';

            // Parse body for Editor.js
            let editorData = null;
            try {
                editorData = JSON.parse(content.body);
            } catch (e) {
                // If body is not JSON, create a simple paragraph
                editorData = {
                    blocks: [{
                        type: 'paragraph',
                        data: { text: content.body }
                    }]
                };
            }

            initContentEditor(editorData);
        } else {
            showToast('Failed to load content', 'error');
        }
    } catch (error) {
        showToast('Error loading content: ' + error.message, 'error');
    }
}

// Save content (Create or Update)
async function saveContent(e) {
    e.preventDefault();

    try {
        const editorData = await editor.save();
        const token = localStorage.getItem('auth_token');
        const contentId = document.getElementById('content-id').value;
        const user = JSON.parse(localStorage.getItem('user') || '{}');

        const formData = {
            title: document.getElementById('content-title').value,
            slug: document.getElementById('content-slug').value,
            content_type: document.getElementById('content-type').value,
            status: document.getElementById('content-status').value,
            excerpt: document.getElementById('content-excerpt').value,
            body: JSON.stringify(editorData),
            author_id: user.id,
            published_at: document.getElementById('content-status').value === 'published'
                ? new Date().toISOString().slice(0, 19).replace('T', ' ')
                : null
        };

        const url = contentId ? `/api/content/${contentId}` : '/api/content';
        const method = contentId ? 'PUT' : 'POST';

        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify(formData)
        });

        const result = await response.json();

        if (result.success) {
            showToast(contentId ? 'Content updated successfully!' : 'Content created successfully!', 'success');
            // Redirect to content list
            setTimeout(() => {
                window.location.hash = '/content';
            }, 1000);
        } else {
            showToast('Error: ' + (result.message || 'Failed to save content'), 'error');
        }
    } catch (error) {
        showToast('Error saving content: ' + error.message, 'error');
    }
}

// Load content list
async function loadContentList() {
    try {
        const token = localStorage.getItem('auth_token');
        const response = await fetch('/api/content', {
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });

        const result = await response.json();
        const tbody = document.querySelector('#content-list-table tbody');

        if (!tbody) return;

        if (result.success && result.data.length > 0) {
            tbody.innerHTML = result.data.map(item => `
                <tr class="ck-border-b ck-border-gray-200 hover:ck-bg-gray-50">
                    <td class="ck-px-4 ck-py-3">${item.title}</td>
                    <td class="ck-px-4 ck-py-3">
                        <span class="ck-px-2 ck-py-1 ck-text-xs ck-rounded-full ck-bg-gray-100 ck-text-gray-800">
                            ${item.content_type}
                        </span>
                    </td>
                    <td class="ck-px-4 ck-py-3">
                        <span class="ck-px-2 ck-py-1 ck-text-xs ck-rounded-full ${getStatusClass(item.status)}">
                            ${item.status}
                        </span>
                    </td>
                    <td class="ck-px-4 ck-py-3">
                        <a href="#/content/edit/${item.id}" class="ck-text-blue-600 hover:ck-text-blue-800 ck-mr-3">
                            Edit
                        </a>
                        <button onclick="deleteContent(${item.id})" class="ck-text-red-600 hover:ck-text-red-800">
                            Delete
                        </button>
                    </td>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="ck-px-4 ck-py-8 ck-text-center ck-text-gray-500">
                        No content found. Create your first content item!
                    </td>
                </tr>
            `;
        }
    } catch (error) {
        showToast('Error loading content: ' + error.message, 'error');
    }
}

function getStatusClass(status) {
    const classes = {
        'published': 'ck-bg-green-100 ck-text-green-800',
        'draft': 'ck-bg-yellow-100 ck-text-yellow-800',
        'archived': 'ck-bg-gray-100 ck-text-gray-800'
    };
    return classes[status] || classes.draft;
}

// Delete content
async function deleteContent(id) {
    if (!confirm('Are you sure you want to delete this content?')) {
        return;
    }

    try {
        const token = localStorage.getItem('auth_token');
        const response = await fetch(`/api/content/${id}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });

        const result = await response.json();

        if (result.success) {
            showToast('Content deleted successfully!', 'success');
            loadContentList();
        } else {
            showToast('Error deleting content', 'error');
        }
    } catch (error) {
        showToast('Error: ' + error.message, 'error');
    }
}

// Load dashboard stats
async function loadDashboardStats() {
    try {
        const token = localStorage.getItem('auth_token');

        // Load content count
        const contentRes = await fetch('/api/content', {
            headers: { 'Authorization': `Bearer ${token}` }
        });
        const contentData = await contentRes.json();

        // Load media count
        const mediaRes = await fetch('/api/media', {
            headers: { 'Authorization': `Bearer ${token}` }
        });
        const mediaData = await mediaRes.json();

        // Load taxonomy count
        const termsRes = await fetch('/api/terms', {
            headers: { 'Authorization': `Bearer ${token}` }
        });
        const termsData = await termsRes.json();

        // Update dashboard stats
        document.querySelectorAll('.stat-content').forEach((el, index) => {
            if (index === 0) el.textContent = contentData.pagination?.total_items || 0;
            if (index === 1) el.textContent = mediaData.pagination?.total_items || 0;
            if (index === 2) el.textContent = termsData.pagination?.total_items || 0;
        });
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}
