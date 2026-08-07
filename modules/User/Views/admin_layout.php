<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KayaCMS Admin</title>
    <link rel="stylesheet" href="/assets/css/ckcss.css">
</head>
<body>
    <!-- Sidebar -->
    <aside class="ck-admin-sidebar">
        <div class="ck-sidebar-brand">
            <span>🚀</span> KayaCMS
        </div>
        <nav class="ck-sidebar-nav" id="sidebar-nav">
            <!-- Links added dynamically -->
        </nav>
    </aside>

    <!-- Main Content Area -->
    <main class="ck-admin-main">
        <!-- Top Bar -->
        <header class="ck-admin-topbar">
            <div class="ck-flex ck-items-center ck-gap-4">
                <h2 class="ck-text-lg ck-font-semibold">Admin Panel</h2>
            </div>
            <div class="ck-flex ck-items-center ck-gap-4">
                <span id="user-name" class="ck-text-sm ck-text-gray-600"></span>
                <button id="btn-logout" class="ck-btn ck-btn-sm ck-btn-outline">Logout</button>
            </div>
        </header>

        <!-- Dynamic Content -->
        <div id="main-content"></div>
    </main>

    <!-- Scripts -->
    <script src="/assets/js/core/api-client.js"></script>
    <script src="/assets/js/core/auth.js"></script>
    <script src="/assets/js/core/router.js"></script>
    <script src="/assets/js/app.js"></script>
</body>
</html>
