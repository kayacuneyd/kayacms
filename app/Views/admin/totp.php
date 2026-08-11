<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication - KayaCMS Admin</title>
    <link rel="stylesheet" href="/assets/css/ckcss.css">
</head>
<body class="ck-bg-gray-100 ck-h-screen ck-flex ck-items-center ck-justify-center">
    <div class="ck-w-full ck-max-w-md">
        <div class="ck-bg-white ck-shadow-lg ck-rounded-lg ck-p-8">
            <div class="ck-text-center ck-mb-8">
                <h1 class="ck-text-3xl ck-font-bold ck-text-gray-900">Two-Factor</h1>
                <p class="ck-text-gray-600 ck-mt-2">Enter the code from your authenticator app</p>
            </div>

            <?php if ($error = session()->getFlashdata('error')): ?>
                <div class="ck-bg-red-100 ck-border ck-border-red-400 ck-text-red-700 ck-px-4 ck-py-3 ck-rounded ck-mb-4">
                    <?= esc($error) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="/admin/auth/totp" class="ck-space-y-6">
                <?= csrf_field() ?>

                <div>
                    <label for="code" class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">
                        6-digit code
                    </label>
                    <input
                        type="text"
                        id="code"
                        name="code"
                        required
                        maxlength="6"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        class="ck-w-full ck-text-center ck-tracking-widest ck-py-3 ck-border ck-border-gray-300 ck-rounded-md focus:ck-outline-none focus:ck-ring-2 focus:ck-ring-blue-500"
                        placeholder="000000"
                    >
                </div>

                <button
                    type="submit"
                    class="ck-w-full ck-bg-blue-600 ck-text-white ck-py-2 ck-px-4 ck-rounded-md hover:ck-bg-blue-700"
                >
                    Verify
                </button>
            </form>

            <div class="ck-mt-6 ck-text-center ck-text-sm ck-text-gray-600">
                <a href="/admin/login" class="ck-text-blue-600">Back to login</a>
            </div>
        </div>
    </div>
</body>
</html>