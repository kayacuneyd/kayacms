<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passwordless Sign In - KayaCMS Admin</title>
    <link rel="stylesheet" href="/assets/css/ckcss.css">
</head>
<body class="ck-bg-gray-100 ck-h-screen ck-flex ck-items-center ck-justify-center">
    <div class="ck-w-full ck-max-w-md">
        <div class="ck-bg-white ck-shadow-lg ck-rounded-lg ck-p-8">
            <div class="ck-text-center ck-mb-8">
                <h1 class="ck-text-3xl ck-font-bold ck-text-gray-900">KayaCMS</h1>
                <p class="ck-text-gray-600 ck-mt-2">Passwordless Sign In</p>
            </div>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="ck-bg-red-100 ck-border ck-border-red-400 ck-text-red-700 ck-px-4 ck-py-3 ck-rounded ck-mb-4">
                    <?= esc(session()->getFlashdata('error')) ?>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="ck-bg-green-100 ck-border ck-border-green-400 ck-text-green-700 ck-px-4 ck-py-3 ck-rounded ck-mb-4">
                    <?= esc(session()->getFlashdata('success')) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="/admin/magic-link" class="ck-space-y-6">
                <?= csrf_field() ?>

                <div>
                    <label for="email" class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        required
                        class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md focus:ck-outline-none focus:ck-ring-2 focus:ck-ring-blue-500"
                        placeholder="admin@kayacms.local"
                        value="<?= old('email') ?>"
                    >
                </div>

                <button
                    type="submit"
                    class="ck-w-full ck-bg-blue-600 ck-text-white ck-py-2 ck-px-4 ck-rounded-md hover:ck-bg-blue-700 focus:ck-outline-none focus:ck-ring-2 focus:ck-ring-blue-500"
                >
                    Send Sign-In Link
                </button>
            </form>

            <div class="ck-mt-6 ck-text-center ck-text-sm ck-text-gray-600">
                <a href="/admin/login" class="ck-text-blue-600">Back to password login</a>
            </div>
        </div>
    </div>
</body>
</html>