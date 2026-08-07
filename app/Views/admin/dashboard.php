<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KayaCMS - Admin Dashboard</title>
    <link rel="stylesheet" href="/assets/css/ckcss.css">
    <!-- Editor.js - Using IIFE bundles for browser compatibility -->
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/editorjs@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/header@latest/dist/header.umd.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/list@latest/dist/list.umd.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/quote@latest/dist/quote.umd.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/image@latest/dist/image.umd.js"></script>
    <style>
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }
        .modal.active {
            display: flex;
        }
        .modal-content {
            background: white;
            border-radius: 8px;
            max-width: 800px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        /* Toast Styles */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
        }
        .toast {
            background: white;
            padding: 16px 24px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 10px;
            min-width: 300px;
            border-left: 4px solid;
            animation: slideIn 0.3s ease-out;
        }
        .toast.success { border-left-color: #10b981; }
        .toast.error { border-left-color: #ef4444; }
        .toast.info { border-left-color: #3b82f6; }
        @keyframes slideIn {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        /* Editor.js custom styles */
        .ce-block__content {
            max-width: 100%;
        }
        .codex-editor {
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            padding: 12px;
            min-height: 300px;
        }
    </style>
</head>
<body class="ck-bg-gray-50">
    <div id="app">
        <!-- Admin Panel Content Loaded by JavaScript -->
        <div class="ck-flex ck-h-screen">
            <!-- Sidebar -->
            <aside class="ck-w-64 ck-bg-gray-900 ck-text-white">
                <div class="ck-p-6">
                    <h1 class="ck-text-2xl ck-font-bold">KayaCMS</h1>
                    <p class="ck-text-gray-400 ck-text-sm">Admin Panel</p>
                </div>
                <nav class="ck-mt-6">
                    <a href="#/dashboard" class="nav-link ck-block ck-px-6 ck-py-3 ck-text-white hover:ck-bg-gray-800" data-route="/dashboard">
                        Dashboard
                    </a>
                    <a href="#/content" class="nav-link ck-block ck-px-6 ck-py-3 ck-text-gray-400 hover:ck-bg-gray-800" data-route="/content">
                        Content
                    </a>
                    <a href="#/media" class="nav-link ck-block ck-px-6 ck-py-3 ck-text-gray-400 hover:ck-bg-gray-800" data-route="/media">
                        Media
                    </a>
                    <a href="#/taxonomy" class="nav-link ck-block ck-px-6 ck-py-3 ck-text-gray-400 hover:ck-bg-gray-800" data-route="/taxonomy">
                        Taxonomy
                    </a>
                    <a href="#/menus" class="nav-link ck-block ck-px-6 ck-py-3 ck-text-gray-400 hover:ck-bg-gray-800" data-route="/menus">
                        Menus
                    </a>
                    <a href="#/settings" class="nav-link ck-block ck-px-6 ck-py-3 ck-text-gray-400 hover:ck-bg-gray-800" data-route="/settings">
                        Settings
                    </a>
                </nav>
            </aside>

            <!-- Main Content -->
            <main class="ck-flex-1 ck-overflow-auto">
                <header class="ck-bg-white ck-border-b ck-border-gray-200 ck-px-6 ck-py-4">
                    <div class="ck-flex ck-items-center ck-justify-between">
                        <h2 class="ck-text-2xl ck-font-semibold ck-text-gray-800" id="page-title">Dashboard</h2>
                        <div class="ck-flex ck-items-center ck-gap-4">
                            <span class="ck-text-sm ck-text-gray-600" id="user-info">Loading...</span>
                            <button onclick="logout()" class="ck-px-4 ck-py-2 ck-bg-red-500 ck-text-white ck-rounded hover:ck-bg-red-600">
                                Logout
                            </button>
                        </div>
                    </div>
                </header>

                <div class="ck-p-6" id="content">
                    <!-- Content will be loaded by router -->
                </div>
            </main>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container" id="toast-container"></div>

    <script>
        // Check authentication and verify token
        async function checkAuth() {
            const token = localStorage.getItem('auth_token');

            if (!token) {
                window.location.href = '/admin/login';
                return;
            }

            // Verify token with API
            try {
                const response = await fetch('/api/auth/me', {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                });

                const data = await response.json();

                if (data.success && data.user) {
                    // Token is valid, update user info
                    document.getElementById('user-info').textContent = `Welcome, ${data.user.username}`;
                    localStorage.setItem('user', JSON.stringify(data.user));
                } else {
                    // Token invalid, redirect to login
                    localStorage.removeItem('auth_token');
                    localStorage.removeItem('user');
                    window.location.href = '/admin/login';
                }
            } catch (error) {
                // Error verifying token, redirect to login
                localStorage.removeItem('auth_token');
                localStorage.removeItem('user');
                window.location.href = '/admin/login';
            }
        }

        // Run auth check
        checkAuth();

        function logout() {
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
            window.location.href = '/admin/login';
        }
    </script>
    <script src="/assets/js/router-content.js"></script>
    <script src="/assets/js/content-manager.js"></script>
    <script>
        // Main router function
        function handleRoute() {
            const hash = window.location.hash.slice(1) || '/dashboard';
            const contentDiv = document.getElementById('content');
            const pageTitle = document.getElementById('page-title');

            // Parse hash to get base route and params
            const hashParts = hash.split('/').filter(p => p);
            const baseRoute = hashParts.length > 0 ? '/' + hashParts[0] : '/dashboard';
            const action = hashParts[1];
            const param = hashParts[2];

            // Update active link (only for main nav items)
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('ck-bg-gray-800', 'ck-text-white');
                link.classList.add('ck-text-gray-400');
                const linkRoute = link.getAttribute('data-route');
                if (linkRoute === baseRoute) {
                    link.classList.add('ck-bg-gray-800', 'ck-text-white');
                    link.classList.remove('ck-text-gray-400');
                }
            });

            // Destroy editor if it exists when leaving content editor
            if (editor && !hash.startsWith('/content/')) {
                editor.destroy();
                editor = null;
            }

            // Handle special routes
            if (baseRoute === '/dashboard') {
                pageTitle.textContent = 'Dashboard';
                contentDiv.innerHTML = renderDashboard();
                setTimeout(() => loadDashboardStats(), 100);
            } else if (baseRoute === '/content') {
                if (action === 'new') {
                    // New content page
                    pageTitle.textContent = 'New Content';
                    contentDiv.innerHTML = renderContentEditor();
                    setTimeout(() => setupContentEditorPage(), 100);
                } else if (action === 'edit' && param) {
                    // Edit content page
                    pageTitle.textContent = 'Edit Content';
                    contentDiv.innerHTML = renderContentEditor(param);
                    setTimeout(() => setupContentEditorPage(param), 100);
                } else {
                    // Content list page
                    pageTitle.textContent = 'Content';
                    contentDiv.innerHTML = renderContentPage();
                    setTimeout(() => loadContentList(), 100);
                }
            } else if (baseRoute === '/media') {
                pageTitle.textContent = 'Media';
                contentDiv.innerHTML = `
                    <div class="ck-bg-white ck-p-6 ck-rounded-lg ck-shadow ck-mb-6">
                        <div class="ck-border-2 ck-border-dashed ck-border-gray-300 ck-rounded-lg ck-p-12 ck-text-center">
                            <div class="ck-text-gray-400 ck-text-4xl ck-mb-4">📁</div>
                            <p class="ck-text-gray-600">Drag files here or click to upload</p>
                        </div>
                    </div>
                    <div class="ck-bg-white ck-p-6 ck-rounded-lg ck-shadow">
                        <h3 class="ck-text-lg ck-font-bold ck-mb-4">Media Library</h3>
                        <p class="ck-text-gray-500 ck-text-center ck-py-8">No media files yet</p>
                    </div>
                `;
            } else if (baseRoute === '/taxonomy') {
                pageTitle.textContent = 'Taxonomy';
                contentDiv.innerHTML = `
                    <div class="ck-bg-white ck-p-6 ck-rounded-lg ck-shadow">
                        <div class="ck-flex ck-justify-between ck-items-center ck-mb-6">
                            <h3 class="ck-text-xl ck-font-bold">Categories & Tags</h3>
                            <button class="ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded hover:ck-bg-blue-700">
                                + New Term
                            </button>
                        </div>
                        <p class="ck-text-gray-500 ck-text-center ck-py-8">No taxonomy terms yet</p>
                    </div>
                `;
            } else if (baseRoute === '/menus') {
                pageTitle.textContent = 'Menus';
                contentDiv.innerHTML = `
                    <div class="ck-bg-white ck-p-6 ck-rounded-lg ck-shadow">
                        <div class="ck-flex ck-justify-between ck-items-center ck-mb-6">
                            <h3 class="ck-text-xl ck-font-bold">Navigation Menus</h3>
                            <button class="ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded hover:ck-bg-blue-700">
                                + New Menu
                            </button>
                        </div>
                        <p class="ck-text-gray-500 ck-text-center ck-py-8">No menus configured yet</p>
                    </div>
                `;
            } else if (baseRoute === '/settings') {
                pageTitle.textContent = 'Settings';
                contentDiv.innerHTML = `
                    <div class="ck-bg-white ck-p-6 ck-rounded-lg ck-shadow">
                        <h3 class="ck-text-xl ck-font-bold ck-mb-6">Site Settings</h3>
                        <form class="ck-space-y-4">
                            <div>
                                <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Site Name</label>
                                <input type="text" value="KayaCMS" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
                            </div>
                            <div>
                                <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Site Description</label>
                                <textarea class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md" rows="3">A modular headless CMS</textarea>
                            </div>
                            <div>
                                <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Items per Page</label>
                                <input type="number" value="10" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
                            </div>
                            <button type="submit" class="ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded hover:ck-bg-blue-700">
                                Save Settings
                            </button>
                        </form>
                    </div>
                `;
            } else {
                // Unknown route, redirect to dashboard
                window.location.hash = '/dashboard';
            }
        }

        // Handle hash changes
        window.addEventListener('hashchange', handleRoute);

        // Initial route
        handleRoute();
    </script>
</body>
</html>
