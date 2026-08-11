<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'KayaCMS - Forgot Password') ?></title>
    <link rel="stylesheet" href="/assets/css/ckcss.css">
</head>
<body class="ck-bg-gray-50">
    <div class="ck-min-h-screen ck-flex ck-flex-col ck-items-center ck-justify-center ck-p-4">
        <div class="ck-bg-white ck-rounded-lg ck-shadow ck-p-8 ck-w-full ck-max-w-md">
            <h1 class="ck-text-2xl ck-font-bold ck-mb-2">KayaCMS</h1>
            <p class="ck-text-gray-600 ck-mb-6">Enter your email and we will send you a password reset link.</p>

            <?php if (session()->getFlashdata('error')): ?>
                <p class="ck-bg-red-50 ck-text-red-700 ck-p-3 ck-rounded ck-mb-4"><?= esc(session()->getFlashdata('error')) ?></p>
            <?php endif; ?>
            <?php if (session()->getFlashdata('success')): ?>
                <p class="ck-bg-green-50 ck-text-green-700 ck-p-3 ck-rounded ck-mb-4"><?= esc(session()->getFlashdata('success')) ?></p>
            <?php endif; ?>

            <form method="post" action="/api/auth/forgot-password" class="ck-space-y-4">
                <?= csrf_field() ?>
                <div>
                    <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-1">Email</label>
                    <input type="email" name="email" required class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
                </div>
                <button type="submit" class="ck-w-full ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded hover:ck-bg-blue-700">Send reset link</button>
            </form>

            <p class="ck-text-center ck-text-sm ck-text-gray-500 ck-mt-4">
                <a href="/admin/login" class="ck-text-blue-600">Back to login</a>
            </p>
        </div>
    </div>
</body>
</html>