// Content page template with proper integration
function renderContentPage() {
    return `
        <div class="ck-bg-white ck-p-6 ck-rounded-lg ck-shadow">
            <div class="ck-flex ck-justify-between ck-items-center ck-mb-6">
                <h3 class="ck-text-xl ck-font-bold">All Content</h3>
                <a href="#/content/new" class="ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded hover:ck-bg-blue-700 ck-inline-block">
                    + New Content
                </a>
            </div>
            <table class="ck-w-full" id="content-list-table">
                <thead class="ck-bg-gray-50">
                    <tr>
                        <th class="ck-px-4 ck-py-3 ck-text-left ck-text-sm ck-font-medium ck-text-gray-600">Title</th>
                        <th class="ck-px-4 ck-py-3 ck-text-left ck-text-sm ck-font-medium ck-text-gray-600">Type</th>
                        <th class="ck-px-4 ck-py-3 ck-text-left ck-text-sm ck-font-medium ck-text-gray-600">Status</th>
                        <th class="ck-px-4 ck-py-3 ck-text-left ck-text-sm ck-font-medium ck-text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="4" class="ck-px-4 ck-py-8 ck-text-center ck-text-gray-500">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    `;
}

// Content editor page template (full page, not modal)
function renderContentEditor(contentId = null) {
    return `
        <div class="ck-bg-white ck-rounded-lg ck-shadow">
            <div class="ck-p-6 ck-border-b ck-border-gray-200">
                <div class="ck-flex ck-justify-between ck-items-center">
                    <h3 class="ck-text-xl ck-font-bold">${contentId ? 'Edit Content' : 'New Content'}</h3>
                    <a href="#/content" class="ck-text-gray-600 hover:ck-text-gray-800">
                        ← Back to Content List
                    </a>
                </div>
            </div>

            <form id="content-form" class="ck-p-6">
                <input type="hidden" id="content-id" value="${contentId || ''}">

                <div class="ck-grid ck-grid-cols-3 ck-gap-6">
                    <!-- Main Content Area (2/3) -->
                    <div class="ck-col-span-2 ck-space-y-6">
                        <div>
                            <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Title</label>
                            <input
                                type="text"
                                id="content-title"
                                required
                                class="ck-w-full ck-px-4 ck-py-3 ck-border ck-border-gray-300 ck-rounded-md focus:ck-outline-none focus:ck-ring-2 focus:ck-ring-blue-500 ck-text-lg"
                                placeholder="Enter content title"
                            >
                        </div>

                        <div>
                            <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Content</label>
                            <div id="editorjs" class="ck-bg-white"></div>
                        </div>
                    </div>

                    <!-- Sidebar (1/3) -->
                    <div class="ck-space-y-6">
                        <div class="ck-bg-gray-50 ck-p-4 ck-rounded-lg">
                            <h4 class="ck-font-medium ck-mb-4">Publishing</h4>

                            <div class="ck-mb-4">
                                <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Status</label>
                                <select
                                    id="content-status"
                                    class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md focus:ck-outline-none focus:ck-ring-2 focus:ck-ring-blue-500"
                                >
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                    <option value="archived">Archived</option>
                                </select>
                            </div>

                            <div class="ck-flex ck-gap-2">
                                <button
                                    type="submit"
                                    class="ck-flex-1 ck-px-4 ck-py-2 ck-bg-blue-600 ck-text-white ck-rounded hover:ck-bg-blue-700 ck-font-medium"
                                >
                                    ${contentId ? 'Update' : 'Publish'}
                                </button>
                                <a
                                    href="#/content"
                                    class="ck-px-4 ck-py-2 ck-bg-gray-200 ck-text-gray-700 ck-rounded hover:ck-bg-gray-300 ck-text-center"
                                >
                                    Cancel
                                </a>
                            </div>
                        </div>

                        <div class="ck-bg-gray-50 ck-p-4 ck-rounded-lg">
                            <h4 class="ck-font-medium ck-mb-4">Settings</h4>

                            <div class="ck-mb-4">
                                <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Slug</label>
                                <input
                                    type="text"
                                    id="content-slug"
                                    required
                                    class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md focus:ck-outline-none focus:ck-ring-2 focus:ck-ring-blue-500"
                                    placeholder="content-slug"
                                >
                            </div>

                            <div class="ck-mb-4">
                                <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Content Type</label>
                                <select
                                    id="content-type"
                                    class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md focus:ck-outline-none focus:ck-ring-2 focus:ck-ring-blue-500"
                                >
                                    <option value="article">Article</option>
                                    <option value="page">Page</option>
                                    <option value="product">Product</option>
                                </select>
                            </div>

                            <div>
                                <label class="ck-block ck-text-sm ck-font-medium ck-text-gray-700 ck-mb-2">Excerpt</label>
                                <textarea
                                    id="content-excerpt"
                                    rows="3"
                                    class="ck-w-full ck-px-3 ck-py-2 ck-border ck-border-gray-300 ck-rounded-md focus:ck-outline-none focus:ck-ring-2 focus:ck-ring-blue-500 ck-text-sm"
                                    placeholder="Short description..."
                                ></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    `;
}

// Dashboard with real stats
function renderDashboard() {
    return `
        <div class="ck-grid ck-grid-cols-3 ck-gap-6 ck-mb-8">
            <div class="ck-bg-white ck-p-6 ck-rounded-lg ck-shadow">
                <div class="ck-text-3xl ck-font-bold ck-text-blue-600 stat-content">0</div>
                <div class="ck-text-gray-600 ck-mt-2">Total Content</div>
            </div>
            <div class="ck-bg-white ck-p-6 ck-rounded-lg ck-shadow">
                <div class="ck-text-3xl ck-font-bold ck-text-green-600 stat-content">0</div>
                <div class="ck-text-gray-600 ck-mt-2">Media Files</div>
            </div>
            <div class="ck-bg-white ck-p-6 ck-rounded-lg ck-shadow">
                <div class="ck-text-3xl ck-font-bold ck-text-purple-600 stat-content">0</div>
                <div class="ck-text-gray-600 ck-mt-2">Categories</div>
            </div>
        </div>
        <div class="ck-bg-white ck-p-6 ck-rounded-lg ck-shadow">
            <h3 class="ck-text-xl ck-font-bold ck-mb-4">Welcome to KayaCMS</h3>
            <p class="ck-text-gray-600">Your modular headless CMS is ready to use. Use the sidebar to navigate through different sections.</p>
        </div>
    `;
}
