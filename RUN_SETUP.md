# KayaCMS Setup Instructions

## Implementation Complete! 🎉

All modules have been successfully created following the vertical slice architecture. Here's what was built:

### ✅ Completed Components

#### Core Infrastructure
- `app/Core/BaseController.php` - Standardized API responses
- `app/Core/BaseModel.php` - Common model behaviors
- Auto-discovery enabled in `app/Config/Modules.php`
- Auth filter aliases registered in `app/Config/Filters.php`

#### Feature Modules (All 7 modules)
1. **User Module** - JWT authentication, roles, filters
2. **Content Module** - Full CRUD with pagination, search, scopes
3. **Taxonomy Module** - Hierarchical categories/tags
4. **Media Module** - File upload/management with validation
5. **Menu Module** - Menu builder with tree structure
6. **Setting Module** - Key-value configuration store
7. **Theme Module** - Theme management

#### Frontend (Build-free)
- **CKCSS** - ~400 utility classes, zero build step (`public/assets/css/ckcss.css`)
- **Vanilla JS Admin Panel** - SPA-like interface with routing
  - API client with JWT injection
  - Hash-based router
  - Auth management
  - Content management UI

#### Developer Tools
- `app/Commands/ModuleCreate.php` - Spark command for scaffolding new modules

---

## 🚀 Next Steps

### 1. Run Migrations

```bash
C:\php\php.exe spark migrate --all
```

This will create all database tables:
- `cms_users` & `cms_roles`
- `cms_content`
- `cms_terms` & `cms_term_relationships`
- `cms_media`
- `cms_menus` & `cms_menu_items`
- `cms_settings`
- `cms_themes`

### 2. Seed Default Data

```bash
C:\php\php.exe spark db:seed User\\Database\\Seeds\\UserSeeder
C:\php\php.exe spark db:seed Setting\\Database\\Seeds\\SettingSeeder
```

This creates:
- Admin user: `admin` / `admin123`
- Editor role
- Default settings

### 3. Start Development Server

```bash
C:\php\php.exe spark serve --port 8080
```

### 4. Access the CMS

- **Admin Panel**: http://localhost:8080/admin
- **API Base**: http://localhost:8080/api

**Login credentials:**
- Username: `admin`
- Password: `admin123`

---

## 📚 API Endpoints

### Authentication
- `POST /api/auth/login` - Get JWT token
- `POST /api/auth/register` - Register new user
- `GET /api/auth/me` - Get current user (requires auth)

### Content
- `GET /api/content` - List content (supports pagination, filtering)
- `GET /api/content/{id}` - Get single content
- `POST /api/content` - Create content (requires auth)
- `PUT /api/content/{id}` - Update content (requires auth)
- `DELETE /api/content/{id}` - Delete content (requires auth)

### Taxonomy
- `GET /api/terms?type=category&tree=true` - Get category tree
- `POST /api/terms` - Create term (requires auth)

### Media
- `GET /api/media` - List media files
- `POST /api/media/upload` - Upload file (requires auth)

### Menus, Settings, Themes
- Similar RESTful patterns for all modules

---

## 🛠️ Create New Module

Use the Spark command to scaffold a new feature module:

```bash
C:\php\php.exe spark module:create Product
```

This creates the complete vertical slice structure:
- Controllers (API & Admin)
- Models & Entities
- Migrations & Seeds
- Routes configuration
- All necessary directories

Then add to `app/Config/Autoload.php`:
```php
'Product' => ROOTPATH . 'modules/Product',
```

---

## 📁 Project Structure

```
kayacms/
├── app/
│   ├── Core/               # Shared kernel
│   └── Commands/           # Spark CLI commands
├── modules/                # Feature modules (vertical slices)
│   ├── User/
│   ├── Content/
│   ├── Taxonomy/
│   ├── Media/
│   ├── Menu/
│   ├── Setting/
│   └── Theme/
├── public/
│   └── assets/
│       ├── css/ckcss.css   # Build-free utility CSS
│       ├── js/             # Vanilla JS admin panel
│       └── uploads/        # Media files
└── writable/
    └── db/                 # SQLite database
```

---

## 🎨 Frontend Development

The admin panel uses **CKCSS** (build-free utility CSS):
- No webpack, vite, or build tools required
- All styles are runtime utilities
- Custom `ck-*` naming convention
- ~400 utility classes available

Vanilla JavaScript with:
- Hash-based SPA router
- Centralized API client with JWT
- Modular feature files

---

## ✨ Features

### Vertical Slice Architecture
- Each module is completely self-contained
- Delete a module = delete its directory
- No cross-module dependencies
- Auto-discovery of routes, migrations, seeds

### Zero Build Frontend
- Direct file serving
- No compilation step
- Easy to customize
- Fast development cycle

### SQLite Database
- Zero configuration
- Portable
- Easy backup (copy .db file)
- Perfect for development

---

## 🔐 Security Notes

**⚠️ IMPORTANT**: Before deploying to production:

1. Change JWT secret in `.env`:
   ```
   jwt.secret = 'YOUR_SECURE_RANDOM_SECRET_HERE'
   ```

2. Change admin password after first login

3. Enable HTTPS in production

4. Configure CORS properly for your frontend domain

---

## 📖 Documentation

- CodeIgniter 4 Docs: https://codeigniter.com/user_guide/
- CKCSS classes are in `public/assets/css/ckcss.css`
- Each module has its own `Config/Routes.php` for endpoints

---

## 🎯 Implementation Checklist

✅ Core infrastructure (BaseController, BaseModel)
✅ Auto-discovery configuration
✅ User module with JWT authentication
✅ Content module with full CRUD
✅ Taxonomy module (categories/tags)
✅ Media module (file uploads)
✅ Menu module (navigation builder)
✅ Setting module (configuration)
✅ Theme module
✅ CKCSS utility framework (~400 classes)
✅ Vanilla JS admin panel
✅ Module generator Spark command

**Next**: Run migrations, seed data, and start the server!
