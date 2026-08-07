// KayaCMS Admin Panel - Main entry point
document.addEventListener('DOMContentLoaded', () => {
    // Check authentication
    if (!Auth.isAuthenticated()) {
        window.location.href = '/admin/login';
        return;
    }

    // Initialize app
    initializeApp();
});

function initializeApp() {
    // Setup sidebar navigation
    setupSidebar();

    // Register routes
    registerRoutes();

    // Setup logout
    document.getElementById('btn-logout')?.addEventListener('click', (e) => {
        e.preventDefault();
        Auth.logout();
    });

    // Display user info
    const user = Auth.getUser();
    if (user) {
        document.getElementById('user-name').textContent = user.username;
    }
}

function setupSidebar() {
    const links = [
        { route: '/dashboard', label: 'Dashboard', icon: '📊' },
        { route: '/content', label: 'Content', icon: '📄' },
        { route: '/media', label: 'Media', icon: '🖼️' },
        { route: '/taxonomy', label: 'Categories', icon: '🏷️' },
        { route: '/menus', label: 'Menus', icon: '☰' },
        { route: '/settings', label: 'Settings', icon: '⚙️' },
    ];

    const nav = document.getElementById('sidebar-nav');
    links.forEach(link => {
        const a = document.createElement('a');
        a.href = `#${link.route}`;
        a.className = 'ck-sidebar-link';
        a.setAttribute('data-route', link.route);
        a.innerHTML = `<span>${link.icon}</span><span>${link.label}</span>`;
        nav.appendChild(a);
    });
}

function registerRoutes() {
    router.register('/dashboard', showDashboard);
    router.register('/content', showContent);
    router.register('/media', showMedia);
    router.register('/taxonomy', showTaxonomy);
    router.register('/menus', showMenus);
    router.register('/settings', showSettings);
}

// Route handlers
function showDashboard() {
    const content = document.getElementById('main-content');
    content.innerHTML = `
        <div class="ck-p-6">
            <div class="ck-page-header">
                <h1 class="ck-page-title">Dashboard</h1>
            </div>
            <div class="ck-grid ck-grid-cols-1 ck-md:grid-cols-3 ck-gap-6 ck-mb-6">
                <div class="ck-stat-card">
                    <div class="ck-stat-value">0</div>
                    <div class="ck-stat-label">Total Content</div>
                </div>
                <div class="ck-stat-card">
                    <div class="ck-stat-value">0</div>
                    <div class="ck-stat-label">Media Files</div>
                </div>
                <div class="ck-stat-card">
                    <div class="ck-stat-value">0</div>
                    <div class="ck-stat-label">Categories</div>
                </div>
            </div>
            <div class="ck-card">
                <h2 class="ck-text-xl ck-font-bold ck-mb-4">Welcome to KayaCMS</h2>
                <p class="ck-text-gray-600">Your modular headless CMS is ready to use.</p>
            </div>
        </div>
    `;
}

function showContent() {
    const content = document.getElementById('main-content');
    content.innerHTML = `
        <div class="ck-p-6">
            <div class="ck-page-header">
                <h1 class="ck-page-title">Content</h1>
                <button class="ck-btn ck-btn-primary" onclick="createContent()">+ New Content</button>
            </div>
            <div class="ck-card">
                <table class="ck-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="content-list">
                        <tr><td colspan="5" class="ck-text-center ck-text-gray-500">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    `;
    loadContentList();
}

async function loadContentList() {
    try {
        const data = await api.get('/content', { limit: 20 });
        const tbody = document.getElementById('content-list');
        if (data.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="ck-text-center ck-text-gray-500">No content found</td></tr>';
            return;
        }
        tbody.innerHTML = data.data.map(item => `
            <tr>
                <td>${item.title}</td>
                <td><span class="ck-badge ck-bg-gray-200">${item.content_type}</span></td>
                <td><span class="ck-badge ck-bg-${item.status === 'published' ? 'green' : 'yellow'}-100">${item.status}</span></td>
                <td>${new Date(item.created_at).toLocaleDateString()}</td>
                <td>
                    <button class="ck-btn ck-btn-sm ck-btn-ghost" onclick="editContent(${item.id})">Edit</button>
                    <button class="ck-btn ck-btn-sm ck-btn-ghost ck-text-red-600" onclick="deleteContent(${item.id})">Delete</button>
                </td>
            </tr>
        `).join('');
    } catch (error) {
        console.error('Failed to load content:', error);
    }
}

function showMedia() {
    const content = document.getElementById('main-content');
    content.innerHTML = `
        <div class="ck-p-6">
            <div class="ck-page-header">
                <h1 class="ck-page-title">Media Library</h1>
            </div>
            <div class="ck-card ck-mb-6">
                <div class="ck-dropzone">
                    <p class="ck-text-gray-600">Drag files here or click to upload</p>
                </div>
            </div>
            <div class="ck-grid ck-grid-cols-2 ck-md:grid-cols-4 ck-gap-4" id="media-grid"></div>
        </div>
    `;
}

function showTaxonomy() {
    const content = document.getElementById('main-content');
    content.innerHTML = `
        <div class="ck-p-6">
            <div class="ck-page-header">
                <h1 class="ck-page-title">Categories & Tags</h1>
                <button class="ck-btn ck-btn-primary">+ New Term</button>
            </div>
            <div class="ck-card">
                <p class="ck-text-gray-600">Manage your taxonomy terms here.</p>
            </div>
        </div>
    `;
}

function showMenus() {
    const content = document.getElementById('main-content');
    content.innerHTML = `
        <div class="ck-p-6">
            <div class="ck-page-header">
                <h1 class="ck-page-title">Menus</h1>
                <button class="ck-btn ck-btn-primary">+ New Menu</button>
            </div>
            <div class="ck-card">
                <p class="ck-text-gray-600">Manage your navigation menus here.</p>
            </div>
        </div>
    `;
}

function showSettings() {
    const content = document.getElementById('main-content');
    content.innerHTML = `
        <div class="ck-p-6">
            <div class="ck-page-header">
                <h1 class="ck-page-title">Settings</h1>
            </div>
            <div class="ck-card">
                <p class="ck-text-gray-600">Configure your CMS settings here.</p>
            </div>
        </div>
    `;
}
