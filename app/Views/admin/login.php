<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - KayaCMS Admin</title>
    <link rel="stylesheet" href="/assets/css/ckcss.css">
</head>
<body class="ck-bg-gray-100 ck-h-screen ck-flex ck-items-center ck-justify-center">
    <div class="ck-w-full ck-max-w-md">
        <div class="ck-bg-white ck-shadow-lg ck-rounded-lg ck-p-8">
            <div class="ck-text-center ck-mb-8">
                <h1 class="ck-text-3xl ck-font-bold ck-text-gray-900">KayaCMS</h1>
                <p class="ck-text-gray-600 ck-mt-2">Admin Panel</p>
            </div>

            <div id="error-message" class="ck-hidden ck-bg-red-100 ck-border ck-border-red-400 ck-text-red-700 ck-px-4 ck-py-3 ck-rounded ck-mb-4"></div>

            <form id="login-form" class="ck-space-y-6">
                <div>
                    <label for="email" class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">
                        Email
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        required
                        class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md focus:ck-outline-none focus:ck-ring-2 focus:ck-ring-blue-500"
                        placeholder="admin@kayacms.local"
                    >
                </div>

                <div>
                    <label for="password" class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">
                        Password
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md focus:ck-outline-none focus:ck-ring-2 focus:ck-ring-blue-500"
                        placeholder="Enter your password"
                    >
                </div>

                <button
                    type="submit"
                    class="ck-w-full ck-bg-blue-600 ck-text-white ck-py-2 ck-px-4 ck-rounded-md hover:ck-bg-blue-700 focus:ck-outline-none focus:ck-ring-2 focus:ck-ring-blue-500"
                >
                    Sign In
                </button>
            </form>

            <div class="ck-mt-6 ck-text-center ck-text-sm ck-text-gray-600">
                <p>Default credentials: admin@kayacms.local / admin123</p>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('login-form').addEventListener('submit', async (e) => {
            e.preventDefault();

            const errorDiv = document.getElementById('error-message');
            errorDiv.classList.add('ck-hidden');

            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            try {
                const response = await fetch('/api/auth/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ email, password })
                });

                const data = await response.json();

                if (data.success && data.token) {
                    // Store token in localStorage
                    localStorage.setItem('auth_token', data.token);
                    localStorage.setItem('user', JSON.stringify(data.user));

                    // Redirect to admin dashboard
                    window.location.href = '/admin/dashboard';
                } else {
                    errorDiv.textContent = data.message || 'Login failed';
                    errorDiv.classList.remove('ck-hidden');
                }
            } catch (error) {
                errorDiv.textContent = 'Network error. Please try again.';
                errorDiv.classList.remove('ck-hidden');
            }
        });

        // Check if already logged in - verify token is valid
        const existingToken = localStorage.getItem('auth_token');
        if (existingToken) {
            // Verify token with API before redirecting
            fetch('/api/auth/me', {
                headers: {
                    'Authorization': `Bearer ${existingToken}`
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '/admin/dashboard';
                } else {
                    // Token invalid, clear it
                    localStorage.removeItem('auth_token');
                    localStorage.removeItem('user');
                }
            })
            .catch(() => {
                // Error verifying, clear token
                localStorage.removeItem('auth_token');
                localStorage.removeItem('user');
            });
        }
    </script>
</body>
</html>
