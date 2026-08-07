-- Insert admin role
INSERT INTO cms_roles (id, name, permissions, created_at, updated_at)
VALUES (1, 'admin', '["*"]', datetime('now'), datetime('now'));

-- Insert editor role
INSERT INTO cms_roles (id, name, permissions, created_at, updated_at)
VALUES (2, 'editor', '["content.create","content.edit","content.delete","media.upload"]', datetime('now'), datetime('now'));

-- Insert admin user (password: admin123)
INSERT INTO cms_users (username, email, password_hash, role_id, status, created_at, updated_at)
VALUES ('admin', 'admin@kayacms.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'active', datetime('now'), datetime('now'));

-- Insert default settings
INSERT INTO cms_settings (key, value, "group", type, created_at, updated_at)
VALUES
('site_name', 'KayaCMS', 'general', 'string', datetime('now'), datetime('now')),
('site_description', 'A modular headless CMS', 'general', 'string', datetime('now'), datetime('now')),
('site_url', 'http://localhost:8080', 'general', 'string', datetime('now'), datetime('now')),
('items_per_page', '10', 'general', 'integer', datetime('now'), datetime('now')),
('enable_registration', 'true', 'users', 'boolean', datetime('now'), datetime('now'));
