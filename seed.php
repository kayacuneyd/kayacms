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
VALUES (2, 'editor', '[\"content.create\",\"content.edit\",\"content.delete\",\"media.upload\"]', datetime('now'), datetime('now'))");

// Insert admin user (password: admin123)
$passwordHash = password_hash('admin123', PASSWORD_DEFAULT);
$db->exec("INSERT OR IGNORE INTO users (username, email, password_hash, role_id, status, created_at, updated_at)
VALUES ('admin', 'admin@kayacms.local', '$passwordHash', 1, 'active', datetime('now'), datetime('now'))");

// Insert default settings
$settings = [
    ['site_name', 'KayaCMS', 'general', 'string'],
    ['site_description', 'A modular headless CMS', 'general', 'string'],
    ['site_url', 'http://localhost:8080', 'general', 'string'],
    ['items_per_page', '10', 'general', 'integer'],
    ['enable_registration', 'true', 'users', 'boolean'],
];

foreach ($settings as $setting) {
    $stmt = $db->prepare("INSERT OR IGNORE INTO settings (key, value, \"group\", type, created_at, updated_at)
    VALUES (?, ?, ?, ?, datetime('now'), datetime('now'))");
    $stmt->execute($setting);
}

echo "✅ Seeding complete!\n";
echo "Admin user created: admin / admin123\n";
