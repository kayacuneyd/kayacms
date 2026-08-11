<?php
// Simple seeder script
$dbPath = __DIR__ . '/writable/db/cms.sqlite3';
$db = new PDO('sqlite:' . $dbPath);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Seeding database...\n";

// Insert admin role
$db->exec("INSERT OR IGNORE INTO roles (id, name, permissions, created_at, updated_at)
VALUES (1, 'admin', '[\"*\"]', datetime('now'), datetime('now'))");

// Insert editor role
$db->exec("INSERT OR IGNORE INTO roles (id, name, permissions, created_at, updated_at)
VALUES (2, 'editor', '[\"dashboard.view\",\"content.view\",\"content.create\",\"content.edit\",\"content.delete\",\"media.view\",\"media.upload\",\"media.edit\",\"media.delete\",\"taxonomy.view\",\"taxonomy.create\",\"taxonomy.edit\",\"taxonomy.delete\",\"menus.view\",\"menus.create\",\"menus.edit\",\"menus.delete\"]', datetime('now'), datetime('now'))");

// Insert contributor role
$db->exec("INSERT OR IGNORE INTO roles (id, name, permissions, created_at, updated_at)
VALUES (3, 'contributor', '[\"dashboard.view\",\"content.view\",\"content.create\",\"content.edit\",\"media.view\",\"media.upload\",\"taxonomy.view\"]', datetime('now'), datetime('now'))");

// Insert admin user (password: admin123)
$passwordHash = password_hash('admin123', PASSWORD_DEFAULT);
$db->exec("INSERT OR IGNORE INTO users (username, email, password_hash, role_id, status, created_at, updated_at)
VALUES ('admin', 'admin@kayacms.local', '$passwordHash', 1, 'active', datetime('now'), datetime('now'))");

// Insert default settings
$settings = [
    ['site_name', 'KayaCMS', 'general', 'string'],
    ['site_description', 'A modular headless CMS', 'general', 'string'],
    ['site_url', 'http://localhost:8080', 'general', 'string'],
    ['site_default_locale', 'tr', 'general', 'string'],
    ['site_active_locales', 'tr,en', 'general', 'string'],
    ['items_per_page', '10', 'general', 'integer'],
    ['admin_email', 'admin@kayacms.local', 'general', 'string'],
    ['cache_enabled', 'false', 'general', 'boolean'],
    ['cache_ttl', '3600', 'general', 'integer'],
    ['robots_txt', "User-agent: *\nDisallow: /admin/\nDisallow: /admin\nAllow: /\nSitemap: http://localhost:8080/sitemap.xml\n", 'general', 'string'],
    ['smtp_host', '', 'email', 'string'],
    ['smtp_user', '', 'email', 'string'],
    ['smtp_pass', '', 'email', 'string'],
    ['smtp_port', '587', 'email', 'integer'],
    ['smtp_crypto', 'tls', 'email', 'string'],
    ['enable_registration', 'true', 'users', 'boolean'],
    ['magic_link_enabled', 'true', 'users', 'boolean'],
    ['cookie_consent_enabled', 'true', 'privacy', 'boolean'],
    ['privacy_policy_url', '', 'privacy', 'string'],
    ['header_scripts', '', 'general', 'textarea'],
    ['footer_scripts', '', 'general', 'textarea'],
    ['cron_token', '', 'system', 'string'],
    ['cron_tasks', 'media:queue,backup:create', 'system', 'string'],
];

foreach ($settings as $setting) {
    $stmt = $db->prepare("INSERT OR IGNORE INTO settings (key, value, \"group\", type, created_at, updated_at)
    VALUES (?, ?, ?, ?, datetime('now'), datetime('now'))");
    $stmt->execute($setting);
}

// Insert default contact form
$fields = json_encode([
    ['name' => 'name', 'label' => 'Full Name', 'type' => 'text', 'required' => true, 'options' => []],
    ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true, 'options' => []],
    ['name' => 'message', 'label' => 'Message', 'type' => 'textarea', 'required' => true, 'options' => []],
], JSON_UNESCAPED_UNICODE);
$settingsJson = json_encode(['notify_email' => 'admin@kayacms.local'], JSON_UNESCAPED_UNICODE);

$db->exec("INSERT OR IGNORE INTO contact_forms (name, slug, fields, settings, is_active, created_at, updated_at)
VALUES ('General Contact', 'contact', '$fields', '$settingsJson', 1, datetime('now'), datetime('now'))");

// Insert default theme (active) + minimal theme (inactive)
$db->exec("INSERT OR IGNORE INTO themes (name, slug, is_active, created_at, updated_at)
VALUES ('Default', 'default', 1, datetime('now'), datetime('now'))");
$db->exec("INSERT OR IGNORE INTO themes (name, slug, is_active, created_at, updated_at)
VALUES ('Minimal', 'minimal', 0, datetime('now'), datetime('now'))");

// Ensure a sample published article exists
$count = (int) $db->query("SELECT COUNT(*) FROM content WHERE slug = 'welcome'")->fetchColumn();
if ($count === 0) {
    $stmt = $db->prepare("INSERT INTO content (content_type, title, slug, body, excerpt, status, author_id, meta_title, meta_description, published_at, created_at, updated_at)
    VALUES ('article', 'Welcome to KayaCMS', 'welcome', '<p>This is your first published article. Go to the admin panel to write more content.</p>', 'Your first article.', 'published', 1, 'Welcome', 'KayaCMS demo article', datetime('now'), datetime('now'), datetime('now'))");
    $stmt->execute();
}

// Backfill published_at for any published content missing it
$db->exec("UPDATE content SET published_at = datetime('now') WHERE status = 'published' AND published_at IS NULL");

// Sample taxonomy terms (categories + tags)
function insertTerm($db, $name, $slug, $type, $desc) {
    $chk = $db->prepare("SELECT COUNT(*) FROM terms WHERE slug = ?");
    $chk->execute([$slug]);
    if ((int)$chk->fetchColumn() > 0) return;
    $db->prepare("INSERT INTO terms (name, slug, taxonomy_type, parent_id, description, created_at, updated_at) VALUES (?,?,?,?,?, datetime('now'), datetime('now'))")
       ->execute([$name, $slug, $type, null, $desc]);
}
function attachTerm($db, $contentSlug, $termSlug) {
    $c = $db->prepare("SELECT id FROM content WHERE slug = ?"); $c->execute([$contentSlug]);
    $t = $db->prepare("SELECT id FROM terms WHERE slug = ?");  $t->execute([$termSlug]);
    $cid = $c->fetchColumn(); $tid = $t->fetchColumn();
    if (!$cid || !$tid) return;
    $chk = $db->prepare("SELECT COUNT(*) FROM term_relationships WHERE content_id=? AND term_id=?");
    $chk->execute([$cid, $tid]);
    if ((int)$chk->fetchColumn() === 0) {
        $db->prepare("INSERT INTO term_relationships (content_id, term_id, created_at) VALUES (?,?,datetime('now'))")->execute([$cid, $tid]);
    }
}
insertTerm($db, 'News', 'news', 'category', 'Latest news and updates.');
insertTerm($db, 'Tutorials', 'tutorials', 'category', 'Step-by-step guides.');
insertTerm($db, 'KayaCMS', 'kayacms', 'tag', 'Posts tagged KayaCMS.');
attachTerm($db, 'getting-started', 'tutorials');
attachTerm($db, 'getting-started', 'kayacms');
attachTerm($db, 'frontend-theming-guide', 'tutorials');
attachTerm($db, 'frontend-theming-guide', 'kayacms');
attachTerm($db, 'welcome', 'news');

echo "✅ Seeding complete!\n";
echo "Admin user created: admin / admin123\n";
