<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'KayaCMS - Reset Password') ?></title>
    <link rel="stylesheet" href="/assets/css/ckcss.css">
</head>
<body class="ck-bg-gray-50">
    <div class="ck-min-flex ck-flex ck-flex-col ck-items-center ck-justify-center ck-p-4">
        <div class="ck-bg-white ck-rounded-lg ck-shadow ck-p-8 ck-w-105 ck-max-w-md">
            <h1 class="ck-text-2xl ck-font-bold ck-mb-2">Reset Password</h1>
            <p class="ck-text-gray-600 ck-mb-6">Choose a new password for your account.</p>

            <?php if (session()->getFlashdata('error')): ?>
                <p class="ck-bg-red-50 ck-text-red-700 ck-p-3 ck-rounded-md ck-mb-4"><?= esc(session()->getFlashdata('error')) ?></p>
            <?php endif; ?>

            <form method="post" action="/admin/reset-password/<?= esc($token) ?>" class="ck-space-y-4">
                <?= csrf_field() ?>
                <div>
                    <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-1">New Password</label>
                    <input type="password" name="password" required minlength="6" class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
                </div>
                <div>
                    <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-1">Confirm Password</label>
                    <input type="password" name="confirmation" required class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md">
                </div>
                <button type="submit" class="ck-w-full ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded hover:ck-bg-blue-700">Reset Password</button>
            </form>
        </div>
    </div>
</body>
</html>