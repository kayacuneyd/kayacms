<?php $title = $title ?? 'API Documentation'; ?>
<div class="ck-bg-white ck-rounded-lg ck-shadow ck-p-6">
    <div class="ck-flex ck-justify-between ck-items-center ck-mb-6">
        <h3 class="ck-text-xl ck-font-bold">API Documentation</h3>
        <a href="/api/openapi" class="ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded-md ck-text-sm">Download OpenAPI JSON</a>
    </div>

    <p class="ck-text-gray-600 ck-mb-6">The KayaCMS API is a REST API. Authenticate with a JWT from <code>POST /api/auth/login</code>
    (sent as <code>Authorization: Bearer &lt;token&gt;</code>) or with a personal access token (sent as <code>API-Key: &lt;token&gt;</code>).</p>

    <div class="ck-space-y-4">
        <div class="ck-border ck-rounded ck-p-4">
            <div class="ck-flex ck-justify-between">
                <code class="ck-font-bold">POST /api/auth/login</code>
                <span class="ck-px-2 ck-py-0 ck-rounded ck-bg-green-100 ck-text-green-800 ck-text-xs">Auth</span>
            </div>
            <p class="ck-text-sm ck-text-gray-500 ck-mt-2">Validates credentials and returns a signed JWT (24h).</p>
        </div>

        <div class="ck-border ck-rounded ck-p-4">
            <code class="ck-font-bold">GET /api/auth/me</code>
            <span class="ck-ml-2 ck-px-2 ck-py-0 ck-rounded ck-bg-green-100 ck-text-green-800 ck-text-xs">Protected</span>
            <p class="ck-text-sm ck-text-gray-500 ck-mt-2">Returns the currently authenticated user profile.</p>
        </div>

        <div class="ck-border ck-rounded ck-p-4">
            <code class="ck-font-bold">POST /api/tokens</code>
            <span class="ck-ml-2 ck-px-2 ck-py-0 ck-rounded ck-bg-green-100 ck-text-green-800 ck-text-xs">Protected</span>
            <p class="ck-text-sm ck-text-gray-500 ck-mt-2">Issue a personal access token. Only displayed once.</p>
        </div>

        <div class="ck-border ck-rounded ck-p-4">
            <code class="ck-font-bold">GET /api/tokens</code>
            <span class="ck-ml-2 ck-px-2 ck-py-0 ck-rounded ck-bg-green-100 ck-text-green-800 ck-text-xs">Protected</span>
            <p class="ck-text-sm ck-text-gray-500 ck-mt-2">List your issued API tokens (metadata only, never the secret).</p>
        </div>

        <div class="ck-border ck-rounded ck-p-4">
            <code class="ck-font-bold">All endpoints</code>
            <span class="ck-ml-2 ck-text-xs ck-text-gray-500">Rate-limited. When exceeded you will receive <code>429</code> with a <code>retry_after</code> value.</span>
        </div>
    </div>
</div>