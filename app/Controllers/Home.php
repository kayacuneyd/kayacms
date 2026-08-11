<?php

namespace App\Controllers;

use App\Libraries\PageCache;
use Content\Models\CommentModel;
use Content\Models\ContentModel;
use Content\Models\VirtualPageModel;
use Taxonomy\Models\TermModel;
use Taxonomy\Models\TermRelationshipModel;
use Menu\Models\MenuModel;
use Menu\Models\MenuItemModel;
use Setting\Models\SettingModel;
use Theme\Models\ThemeModel;

class Home extends BaseController
{
    protected ContentModel $contentModel;
    protected TermModel $termModel;
    protected TermRelationshipModel $termRelModel;
    protected MenuModel $menuModel;
    protected MenuItemModel $menuItemModel;
    protected SettingModel $settingModel;
    protected CommentModel $commentModel;
    protected ThemeModel $themeModel;

    protected string $theme = 'default';
    protected array $themeConfig = [];
    protected array $settings = [];
    protected array $menus = [];
    protected string $locale = 'tr';
    protected string $defaultLocale = 'tr';
    protected array $activeLocales = ['tr'];

    public function __construct()
    {
        $this->contentModel   = new ContentModel();
        $this->commentModel   = new CommentModel();
        $this->termModel      = new TermModel();
        $this->termRelModel   = new TermRelationshipModel();
        $this->menuModel      = new MenuModel();
        $this->menuItemModel  = new MenuItemModel();
        $this->settingModel   = new SettingModel();
        $this->themeModel     = new ThemeModel();

        $this->resolveTheme();
        $this->settings = $this->settingModel->getByGroup('general');
        foreach ($this->settingModel->getByGroup('privacy') as $key => $value) {
            $this->settings[$key] = $value;
        }
        $this->defaultLocale = $this->settings['site_default_locale'] ?? 'tr';
        $this->activeLocales = array_filter(array_map('trim', explode(',', $this->settings['site_active_locales'] ?? 'tr')));
        $this->locale = $this->detectLocale();
        $this->menus    = $this->buildMenus();
    }

    protected function detectLocale(): string
    {
        $uri = service('uri');
        $segments = $uri->getSegments();

        if (!empty($segments[0]) && in_array($segments[0], $this->activeLocales, true)) {
            return $segments[0];
        }

        return $this->defaultLocale;
    }

    public function index(): string
    {
        $perPage = (int) ($this->settings['items_per_page'] ?? 10);
        if ($perPage < 1) {
            $perPage = 10;
        }
        $page = max(1, (int) ($this->request->getGet('page') ?? 1));

        $this->contentModel->select('content.*')
            ->where('content.content_type', 'article')
            ->where('content.locale', $this->locale)
            ->published()
            ->orderBy('content.published_at', 'DESC');

        $total    = (int) $this->contentModel->countAllResults(false);
        $articles = $this->contentModel->findAll($perPage, ($page - 1) * $perPage);

        $featured = $this->contentModel
            ->where('content.content_type', 'article')
            ->where('content.locale', $this->locale)
            ->where('content.is_featured', 1)
            ->published()
            ->orderBy('content.published_at', 'DESC')
            ->findAll(5);

        $data = $this->layoutData('Home', $this->settings['site_description'] ?? null, [
            'articles'   => $articles,
            'featured'   => $featured,
            'pagination' => [
                'current_page' => $page,
                'total_pages'  => (int) ceil($total / $perPage),
                'per_page'     => $perPage,
                'total_items'  => $total,
                'base_url'     => localized_url('/'),
            ],
            'page_heading'    => 'Latest Articles',
            'page_subheading' => $this->settings['site_description'] ?? '',
            'canonical_url'   => localized_url('/'),
            'breadcrumbs'     => [
                ['label' => 'Home', 'url' => localized_url('/')],
            ],
        ]);

        return $this->renderCached("themes/{$this->theme}/index", $data);
    }

    public function show(string $slug = null)
    {
        $item = $this->contentModel
            ->where('slug', $slug)
            ->where('locale', $this->locale)
            ->where('status', 'published')
            ->where('content.published_at <=', date('Y-m-d H:i:s'))
            ->first();

        // Fallback to default locale
        if (! $item && $this->locale !== $this->defaultLocale) {
            $item = $this->contentModel
                ->where('slug', $slug)
                ->where('locale', $this->defaultLocale)
                ->where('status', 'published')
                ->where('content.published_at <=', date('Y-m-d H:i:s'))
                ->first();
        }

        if (! $item) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $approvedComments = $this->commentModel->getApprovedByContent($item->id);
        $comments         = $this->commentModel->buildTree($approvedComments);

        $translations = $this->contentModel->translations($item->translation_group_id);
        $localizedLinks = $this->buildLocalizedLinks($item, 'content');

        $relatedIds = (new \Content\Models\ContentRelationModel())->relatedIds((int) $item->id);
        $related = [];
        if ($relatedIds) {
            $related = $this->contentModel
                ->where('content.locale', $this->locale)
                ->where('content.status', 'published')
                ->published()
                ->whereIn('id', $relatedIds)
                ->orderBy('content.published_at', 'DESC')
                ->findAll();
        }

        $data = $this->layoutData(
            $item->title,
            $item->meta_description ?? null,
            [
                'item'           => $item,
                'comments'       => $comments,
                'related'        => $related,
                'translations'   => $translations,
                'localizedLinks' => $localizedLinks,
                'canonical_url'  => $item->canonical_url ?: localized_url('/content/' . $item->slug),
                'og_type'        => 'article',
                'breadcrumbs'    => [
                    ['label' => 'Home', 'url' => localized_url('/')],
                    ['label' => ucfirst($item->content_type), 'url' => null],
                    ['label' => $item->title, 'url' => null],
                ],
            ]
        );

        return $this->renderCached($this->resolveSingleView((string) $item->content_type), $data);
    }

    public function page(string $slug = null)
    {
        $item = $this->contentModel
            ->where('slug', $slug)
            ->where('locale', $this->locale)
            ->where('content_type', 'page')
            ->where('status', 'published')
            ->where('content.published_at <=', date('Y-m-d H:i:s'))
            ->first();

        if (! $item && $this->locale !== $this->defaultLocale) {
            $item = $this->contentModel
                ->where('slug', $slug)
                ->where('locale', $this->defaultLocale)
                ->where('content_type', 'page')
                ->where('status', 'published')
                ->where('content.published_at <=', date('Y-m-d H:i:s'))
                ->first();
        }

        if (! $item) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $localizedLinks = $this->buildLocalizedLinks($item, 'page');

        $data = $this->layoutData(
            $item->title,
            $item->meta_description ?? null,
            [
                'item'           => $item,
                'localizedLinks' => $localizedLinks,
                'canonical_url'  => $item->canonical_url ?: localized_url('/page/' . $item->slug),
                'breadcrumbs'    => [
                    ['label' => 'Home', 'url' => localized_url('/')],
                    ['label' => $item->title, 'url' => null],
                ],
            ]
        );

        return $this->renderCached($this->resolveSingleView('page'), $data);
    }

    protected function resolveSingleView(string $contentType): string
    {
        $contentType = preg_replace('/[^a-z0-9-_]/', '', strtolower($contentType));
        $specific    = "themes/{$this->theme}/single-{$contentType}";

        if ($contentType !== '' && is_file(APPPATH . 'Views/' . $specific . '.php')) {
            return $specific;
        }

        return "themes/{$this->theme}/single";
    }

    public function virtualPage(string $slug)
    {
        $slug = trim($slug, '/');

        $page = null;

        $page = $this->resolveVirtualPage($slug);

        if (! $page) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if ($page['type'] === 'redirect') {
            return redirect()->to((string) $page['url']);
        }

        if ($page['type'] === 'template' && str_starts_with((string) $page['view'], 'themes/')) {
            return $this->renderCached(
                (string) $page['view'],
                $this->layoutData($page['title'], null, [
                    'breadcrumbs' => [
                        ['label' => 'Home', 'url' => localized_url('/')],
                        ['label' => $page['title'], 'url' => null],
                    ],
                ])
            );
        }

        $body = $page['type'] === 'markdown'
            ? (new \Content\Libraries\VirtualPage())->renderMarkdown((string) $page['body'])
            : \App\Libraries\ContentRenderer::render((string) $page['body']);

        $data = $this->layoutData($page['title'], null, [
            'virtual_title'  => $page['title'],
            'virtual_body'   => $body,
            'virtual_slug'   => $slug,
            'canonical_url'  => localized_url('/' . $slug),
            'breadcrumbs'    => [
                ['label' => 'Home', 'url' => localized_url('/')],
                ['label' => $page['title'], 'url' => null],
            ],
        ]);

        return $this->renderCached("themes/{$this->theme}/virtual", $data);
    }

    public function category(string $slug): string
    {
        return $this->termPage($slug, 'category');
    }

    public function tag(string $slug): string
    {
        return $this->termPage($slug, 'tag');
    }

    public function search(): string
    {
        $q = trim((string) ($this->request->getGet('q') ?? ''));

        $this->contentModel->select('content.*')
            ->where('content.content_type', 'article')
            ->where('content.locale', $this->locale)
            ->published();

        if ($q !== '') {
            $this->contentModel->groupStart()
                ->like('content.title', $q)
                ->orLike('content.body', $q)
                ->orLike('content.excerpt', $q)
                ->groupEnd();
        }

        $articles = $this->contentModel->orderBy('content.published_at', 'DESC')->findAll();

        $data = $this->layoutData(
            $q ? "Search: {$q}" : 'Search',
            null,
            [
                'articles'      => $articles,
                'query'         => $q,
                'canonical_url' => localized_url('/search'),
                'breadcrumbs'   => [
                    ['label' => 'Home', 'url' => localized_url('/')],
                    ['label' => 'Search', 'url' => null],
                ],
            ]
        );

        return $this->renderCached("themes/{$this->theme}/search", $data);
    }

    public function sitemap()
    {
        $this->contentModel->select('content.*')
            ->where('content.status', 'published')
            ->where('content.locale', $this->locale)
            ->where('content.published_at <=', date('Y-m-d H:i:s'))
            ->orderBy('content.updated_at', 'DESC');

        $articles = $this->contentModel->findAll();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        $xml .= '  <url>' . "\n";
        $xml .= '    <loc>' . localized_url('/') . '</loc>' . "\n";
        $xml .= '    <changefreq>daily</changefreq>' . "\n";
        $xml .= '    <priority>1.0</priority>' . "\n";
        $xml .= '  </url>' . "\n";

        foreach ($articles as $article) {
            $loc = $article->content_type === 'page'
                ? localized_url('/page/' . $article->slug)
                : localized_url('/content/' . $article->slug);

            $lastmod = $article->updated_at ? date('Y-m-d', strtotime($article->updated_at)) : date('Y-m-d');

            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . $loc . '</loc>' . "\n";
            $xml .= '    <lastmod>' . $lastmod . '</lastmod>' . "\n";
            $xml .= '    <changefreq>weekly</changefreq>' . "\n";
            $xml .= '    <priority>0.8</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        $terms = $this->termModel->where('locale', $this->locale)->findAll();
        foreach ($terms as $term) {
            $prefix = $term['taxonomy_type'] === 'tag' ? 'tag' : 'category';
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . localized_url('/' . $prefix . '/' . $term['slug']) . '</loc>' . "\n";
            $xml .= '    <changefreq>weekly</changefreq>' . "\n";
            $xml .= '    <priority>0.6</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        $xml .= '</urlset>';

        return $this->response->setStatusCode(200)
            ->setContentType('application/xml')
            ->setBody($xml);
    }

    public function robots()
    {
        $robots = $this->settings['robots_txt'] ?? "User-agent: *\nAllow: /\n";
        return $this->response->setStatusCode(200)
            ->setContentType('text/plain')
            ->setBody($robots);
    }

    public function feed(string $type = 'rss')
    {
        $this->contentModel->select('content.*')
            ->where('content.status', 'published')
            ->where('content.locale', $this->locale)
            ->where('content.content_type', 'article')
            ->where('content.published_at <=', date('Y-m-d H:i:s'))
            ->orderBy('content.published_at', 'DESC');

        $articles = $this->contentModel->findAll(20, 0);
        $siteName = $this->settings['site_name'] ?? 'KayaCMS';
        $siteDescription = $this->settings['site_description'] ?? '';

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">' . "\n";
        $xml .= '  <channel>' . "\n";
        $xml .= '    <title>' . esc($siteName) . '</title>' . "\n";
        $xml .= '    <link>' . localized_url('/') . '</link>' . "\n";
        $xml .= '    <description>' . esc($siteDescription) . '</description>' . "\n";
        $xml .= '    <language>' . $this->locale . '</language>' . "\n";
        $xml .= '    <lastBuildDate>' . date(DATE_RSS) . '</lastBuildDate>' . "\n";
        $xml .= '    <atom:link href="' . localized_url('/feed.xml') . '" rel="self" type="application/rss+xml" />' . "\n";

        foreach ($articles as $article) {
            $link = localized_url('/content/' . $article->slug);
            $guid = $link;
            $description = esc(\App\Libraries\ContentRenderer::excerpt($article->body ?? null, $article->excerpt ?? null, 200));
            $pubDate = $article->published_at ? date(DATE_RSS, strtotime($article->published_at)) : date(DATE_RSS);

            $xml .= '    <item>' . "\n";
            $xml .= '      <title>' . esc($article->title) . '</title>' . "\n";
            $xml .= '      <link>' . $link . '</link>' . "\n";
            $xml .= '      <guid isPermaLink="true">' . $guid . '</guid>' . "\n";
            $xml .= '      <description>' . $description . '</description>' . "\n";
            $xml .= '      <pubDate>' . $pubDate . '</pubDate>' . "\n";
            $xml .= '    </item>' . "\n";
        }

        $xml .= '  </channel>' . "\n";
        $xml .= '</rss>';

        return $this->response->setStatusCode(200)
            ->setContentType('application/rss+xml')
            ->setBody($xml);
    }

    protected function termPage(string $slug, string $type): string
    {
        $term = $this->termModel->where('slug', $slug)
            ->where('taxonomy_type', $type)
            ->where('locale', $this->locale)
            ->first();

        if (! $term && $this->locale !== $this->defaultLocale) {
            $term = $this->termModel->where('slug', $slug)
                ->where('taxonomy_type', $type)
                ->where('locale', $this->defaultLocale)
                ->first();
        }

        if (! $term) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $this->contentModel
            ->select('content.*')
            ->join('term_relationships', 'term_relationships.content_id = content.id')
            ->join('terms', 'terms.id = term_relationships.term_id')
            ->where('terms.id', $term['id'])
            ->where('content.locale', $this->locale)
            ->published()
            ->orderBy('content.published_at', 'DESC');

        $rows = $this->contentModel->findAll();
        $articles = array_map(static fn ($e) => $e->toArray(), $rows);

        $prefix = $type === 'tag' ? 'tag' : 'category';

        $data = $this->layoutData(
            ucfirst($type) . ' — ' . $term['name'],
            $term['description'] ?? null,
            [
                'term'          => $term,
                'articles'      => $articles,
                'canonical_url' => localized_url('/' . $prefix . '/' . $term['slug']),
                'breadcrumbs'   => [
                    ['label' => 'Home', 'url' => localized_url('/')],
                    ['label' => ucfirst($type) . 's', 'url' => null],
                    ['label' => $term['name'], 'url' => null],
                ],
            ]
        );

        return $this->renderCached("themes/{$this->theme}/category", $data);
    }

    protected function resolveVirtualPage(string $slug): ?array
    {
        $vpModel = new VirtualPageModel();
        $page    = $vpModel->findBySlug($slug);

        if ($page) {
            $payload = $vpModel->payloadArray($page);

            return [
                'type'  => (string) ($page['handler'] ?? 'template'),
                'title' => (string) ($page['title'] ?? ucfirst($slug)),
                'view'  => (string) ($payload['view'] ?? ''),
                'data'  => is_array($payload['data'] ?? null) ? $payload['data'] : [],
                'body'  => (string) ($payload['body'] ?? ''),
                'url'   => (string) ($payload['url'] ?? ''),
            ];
        }

        return null;
    }

    protected function resolveTheme(): void
    {
        $active = $this->themeModel->getActive();
        $slug   = $active['slug'] ?? 'default';
        $slug   = preg_replace('/[^a-z0-9-_]/', '', $slug ?: 'default');

        $this->theme = is_dir(APPPATH . 'Views/themes/' . $slug) ? $slug : 'default';
        $this->themeConfig = (new \Theme\Libraries\ThemeConfig())->resolve(is_array($active) ? $active : null);
    }

    protected function buildMenus(): array
    {
        $menus = [];
        foreach ($this->menuModel->where('locale', $this->locale)->findAll() as $menu) {
            $menus[$menu['location']] = $this->menuItemModel->getMenuTree($menu['id'], $this->locale);
        }

        // Fallback to default locale menus if none found for current locale
        if (empty($menus)) {
            foreach ($this->menuModel->where('locale', $this->defaultLocale)->findAll() as $menu) {
                $menus[$menu['location']] = $this->menuItemModel->getMenuTree($menu['id'], $this->defaultLocale);
            }
        }

        return $menus;
    }

    protected function layoutData(string $title, ?string $metaDescription, array $extra): array
    {
        $siteName = $this->settings['site_name'] ?? 'KayaCMS';
        $fullTitle = $title === $siteName ? $siteName : $title . ' — ' . $siteName;

        return array_merge([
            'settings'         => $this->settings,
            'theme_config'     => $this->themeConfig,
            'menus'            => $this->menus,
            'theme'            => $this->theme,
            'locale'           => $this->locale,
            'defaultLocale'    => $this->defaultLocale,
            'activeLocales'    => $this->activeLocales,
            'title'            => $fullTitle,
            'page_title'       => $title,
            'meta_description' => $metaDescription ?: ($this->settings['site_description'] ?? ''),
            'canonical_url'    => $extra['canonical_url'] ?? base_url(current_url()),
            'og_type'          => $extra['og_type'] ?? 'website',
        ], $extra);
    }

    protected function renderCached(string $view, array $data): string
    {
        $pageCache = PageCache::instance();
        $uri       = service('uri')->getPath();

        if ($cached = $pageCache->get($uri, $this->locale)) {
            return $cached;
        }

        $html = view($view, $data);
        $pageCache->save($uri, $html, $this->locale);
        return $html;
    }

    /**
     * Generate URL with locale prefix
     */
    public function localizedUrl(string $path): string
    {
        $base = rtrim(base_url('/'), '/');
        $prefix = $this->locale === $this->defaultLocale ? '' : '/' . $this->locale;
        $path = '/' . ltrim($path, '/');
        return $base . $prefix . $path;
    }

    /**
     * Build alternate language links for hreflang
     */
    protected function buildLocalizedLinks($item, string $routeType): array
    {
        $links = [];
        foreach ($this->activeLocales as $loc) {
            $prefix = $loc === $this->defaultLocale ? '' : '/' . $loc;
            $base = rtrim(base_url('/'), '/');

            // Try to find translation slug for this locale
            $slug = $item->slug;
            if ($loc !== $item->locale && !empty($item->translation_group_id)) {
                $translation = $this->contentModel
                    ->where('translation_group_id', $item->translation_group_id)
                    ->where('locale', $loc)
                    ->where('status', 'published')
                    ->first();
                if ($translation) {
                    $slug = $translation->slug;
                } else {
                    // No translation published in this locale
                    continue;
                }
            }

            if ($routeType === 'page') {
                $links[$loc] = $base . $prefix . '/page/' . $slug;
            } else {
                $links[$loc] = $base . $prefix . '/content/' . $slug;
            }
        }

        return $links;
    }
}
