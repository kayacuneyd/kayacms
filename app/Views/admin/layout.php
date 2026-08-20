<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'KayaCMS - Admin') ?></title>
    <link rel="stylesheet" href="/assets/css/ckcss.css">
    <link rel="stylesheet" href="<?= base_url('assets/admin/css/admin.css') ?>">
    <style>
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
    </style>
</head>
<body class="ck-bg-gray-50">
    <div id="app" class="ck-flex ck-h-screen">
        <!-- Sidebar -->
        <aside class="ck-w-64 ck-bg-gray-900 ck-text-white">
            <div class="ck-p-6">
                <h1 class="ck-text-2xl ck-font-bold">KayaCMS</h1>
                <p class="ck-text-gray-400 ck-text-sm">Admin Panel</p>
            </div>
            <nav class="ck-mt-6">
                <a href="/admin/dashboard" class="ck-block ck-px-6 ck-py-3 ck-text-gray-400 hover:ck-text-white <?= ($active ?? '') === 'dashboard' ? 'ck-text-white' : '' ?>">
                    Dashboard
                </a>
                <a href="/admin/content" class="ck-block ck-px-6 ck-py-3 ck-text-gray-400 hover:ck-text-white <?= ($active ?? '') === 'content' ? 'ck-text-white' : '' ?>">
                    Content
                </a>
                <a href="/admin/virtual-pages" class="ck-block ck-px-6 ck-py-3 ck-text-gray-400 hover:ck-text-white <?= ($active ?? '') === 'virtual_pages' ? 'ck-text-white' : '' ?>">
                    Virtual Pages
                </a>
                <a href="/admin/collections" class="ck-block ck-px-6 ck-py-3 ck-text-gray-400 hover:ck-text-white <?= ($active ?? '') === 'collections' ? 'ck-text-white' : '' ?>">
                    Collections
                </a>
                <a href="/admin/content/schemas" class="ck-block ck-px-6 ck-py-3 ck-text-gray-400 hover:ck-text-white <?= ($active ?? '') === 'content_schemas' ? 'ck-text-white' : '' ?>">
                    Custom Fields
                </a>
                <a href="/admin/comments" class="ck-block ck-px-6 ck-py-3 ck-text-gray-400 hover:ck-text-white <?= ($active ?? '') === 'comments' ? 'ck-text-white' : '' ?>">
                    Comments
                </a>
                <a href="/admin/contact-forms" class="ck-block ck-px-6 ck-py-3 ck-text-gray-400 hover:ck-text-white <?= ($active ?? '') === 'contact_forms' ? 'ck-text-white' : '' ?>">
                    Contact Forms
                </a>
                <a href="/admin/newsletter" class="ck-block ck-px-6 ck-py-3 ck-text-gray-400 hover:ck-text-white <?= ($active ?? '') === 'newsletter' ? 'ck-text-white' : '' ?>">
                    Newsletter
                </a>
                <a href="/admin/rss" class="ck-block ck-px-6 ck-py-3 ck-text-gray-400 hover:ck-text-white <?= ($active ?? '') === 'rss' ? 'ck-text-white' : '' ?>">
                    RSS
                </a>
                <a href="/admin/media" class="ck-block ck-px-6 ck-py-3 ck-text-gray-400 hover:ck-text-white <?= ($active ?? '') === 'media' ? 'ck-text-white' : '' ?>">
                    Media
                </a>
                <a href="/admin/media/queue" class="ck-block ck-px-6 ck-py-3 ck-text-gray-400 hover:ck-text-white <?= ($active ?? '') === 'media_queue' ? 'ck-text-white' : '' ?>">
                    Media Queue
                </a>
                <a href="/admin/taxonomy" class="ck-block ck-px-6 ck-py-3 ck-text-gray-400 hover:ck-text-white <?= ($active ?? '') === 'taxonomy' ? 'ck-text-white' : '' ?>">
                    Taxonomy
                </a>
                <a href="/admin/menus" class="ck-block ck-px-6 ck-py-3 ck-text-gray-400 hover:ck-text-white <?= ($active ?? '') === 'menus' ? 'ck-text-white' : '' ?>">
                    Menus
                </a>
                <a href="/admin/users" class="ck-block ck-px-6 ck-py-3 ck-text-gray-400 hover:ck-text-white <?= ($active ?? '') === 'users' ? 'ck-text-white' : '' ?>">
                    Users
                </a>
                <a href="/admin/roles" class="ck-block ck-px-6 ck-py-3 ck-text-gray-400 hover:ck-text-white <?= ($active ?? '') === 'roles' ? 'ck-text-white' : '' ?>">
                    Roles
                </a>
                <a href="/admin/settings" class="ck-block ck-px-6 ck-py-3 ck-text-gray-400 hover:ck-text-white <?= ($active ?? '') === 'settings' ? 'ck-text-white' : '' ?>">
                    Settings
                </a>
                <a href="/admin/cache" class="ck-block ck-px-6 ck-py-3 ck-text-gray-400 hover:ck-text-white <?= ($active ?? '') === 'cache' ? 'ck-text-white' : '' ?>">
                    Cache
                </a>
                <a href="/admin/themes" class="ck-block ck-px-6 ck-py-3 ck-text-gray-400 hover:ck-text-white <?= ($active ?? '') === 'themes' ? 'ck-text-white' : '' ?>">
                    Themes
                </a>
                <a href="/admin/security" class="ck-block ck-px-6 ck-py-3 ck-text-gray-400 hover:ck-text-white <?= ($active ?? '') === 'security' ? 'ck-text-white' : '' ?>">
                    Security
                </a>
                <a href="/admin/error-logs" class="ck-block ck-px-6 ck-py-3 ck-text-gray-400 hover:ck-text-white <?= ($active ?? '') === 'error_logs' ? 'ck-text-white' : '' ?>">
                    Error Log
                </a>
                <a href="/admin/system-health" class="ck-block ck-px-6 ck-py-3 ck-text-gray-400 hover:ck-text-white <?= ($active ?? '') === 'system_health' ? 'ck-text-white' : '' ?>">
                    System Health
                </a>
                <a href="/admin/seo-audit" class="ck-block ck-px-6 ck-py-3 ck-text-gray-400 hover:ck-text-white <?= ($active ?? '') === 'seo_audit' ? 'ck-text-white' : '' ?>">
                    SEO Audit
                </a>
                <a href="/admin/gdpr" class="ck-block ck-px-6 ck-py-3 ck-text-gray-400 hover:ck-text-white <?= ($active ?? '') === 'gdpr' ? 'ck-text-white' : '' ?>">
                    GDPR Export
                </a>
                <a href="/admin/api-tokens" class="ck-block ck-px-6 ck-py-3 ck-text-gray-400 hover:ck-text-white <?= ($active ?? '') === 'api_tokens' ? 'ck-text-white' : '' ?>">
                    API Tokens
                </a>
                <a href="/admin/webhooks" class="ck-block ck-px-6 ck-py-3 ck-text-gray-400 hover:ck-text-white <?= ($active ?? '') === 'webhooks' ? 'ck-text-white' : '' ?>">
                    Webhooks
                </a>
                <a href="/admin/hooks" class="ck-block ck-px-6 ck-py-3 ck-text-gray-400 hover:ck-text-white <?= ($active ?? '') === 'hooks' ? 'ck-text-white' : '' ?>">
                    Hooks & Events
                </a>
                <a href="/admin/maintenance" class="ck-block ck-px-6 ck-py-3 ck-text-gray-400 hover:ck-text-white <?= ($active ?? '') === 'maintenance' ? 'ck-text-white' : '' ?>">
                    Backup & Maintenance
                </a>
            </nav>
            <div class="ck-absolute ck-bottom-0 ck-left-0 ck-w-64">
                <div class="ck-p-4 ck-border-t ck-border-gray-700">
                    <a href="/admin/logout" class="ck-text-sm ck-text-gray-400 hover:ck-text-white">
                        Logout
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="ck-flex-1 ck-overflow-auto">
            <header class="ck-bg-white ck-shadow ck-px-8 ck-py-4 ck-flex ck-justify-between ck-items-center">
                <h2 class="ck-text-xl ck-font-bold"><?= esc($title ?? 'Dashboard') ?></h2>
                <div class="ck-flex ck-items-center ck-space-x-4">
                    <?php $unread = \User\Libraries\Notifications::unreadCount($user['id'] ?? null); ?>
                    <a href="/admin/notifications" class="ck-relative ck-text-sm ck-text-gray-600 hover:ck-text-gray-900" title="Notifications">
                        <span class="ck-text-xl">&#128276;</span>
                        <?php if ($unread > 0): ?>
                            <span class="ck-absolute ck-top-0 ck-right-0 ck-bg-red-500 ck-text-white ck-text-xs ck-rounded-full ck-px-2 ck-transform ck-translate-x-1/-translate-y-1"><?= $unread ?></span>
                        <?php endif; ?>
                    </a>
                    <span class="ck-text-sm ck-text-gray-600"><?= esc($user['username'] ?? 'admin') ?></span>
                </div>
            </header>

            <div class="ck-p-8">
                <?= $content ?>
            </div>

            <footer class="ck-px-8 ck-pb-6 ck-text-xs ck-text-gray-500">
                KayaCMS v<?= esc(\Config\Version::current()) ?> · CodeIgniter 4
            </footer>
        </main>
    </div>

    <div id="toast-container" class="toast-container"></div>
    <script src="https://unpkg.com/lucide@0.468.0/dist/umd/lucide.min.js"></script>
    <script src="<?= base_url('assets/js/media-picker.js') ?>" defer></script>
    <script src="<?= base_url('assets/js/error-logger.js') ?>" defer></script>
    <script>
        if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons();

        function showToast(message, type = 'info') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.textContent = message;
            container.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }

        <?php if (session()->getFlashdata('success')): ?>
            showToast('<?= esc(session()->getFlashdata('success'), 'js') ?>', 'success');
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            showToast('<?= esc(session()->getFlashdata('error'), 'js') ?>', 'error');
        <?php endif; ?>
    </script>
</body>
</html>
