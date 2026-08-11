# KayaCMS İlerleme Raporu

Bu doküman projenin son durumunu ve tamamlanan geliştirmeleri takip eder.

## Son Güncelleme

**Tarih:** 2026-08-11
**Durum:** v1 + v2 yol haritaları tamamlandı. Yeni **Faz 1–4 planı** başladı (production-readiness + shared hosting + açık kaynak). **Faz 1-1 (Theme Config), Faz 1-2 (content-type view override), Faz 1-3 (header/footer script'leri) tamamlandı** — 109 test / 353 assertion tamamı yeşil. Sırada Faz 1-4 (CKEditor paketleme), 1-5 (minimal tema), sonra Faz 2 (web-cron + deploy), Faz 3 (açık kaynak), Faz 4 (müşteri sistemi). Ayrıntılar için `DEVELOPMENT.md #12`.

## Faz 1-2 & 1-3 — Content-type Override + Global Scriptler

- **Content-type view override:** `Home::resolveSingleView($contentType)` — `themes/{theme}/single-{content_type}.php` varsa onu, yoksa `single.php`'yi kullanır (show + page). Örnek `single-review.php` (Review rozeti + `custom_data.rating`). Test `ContentTypeOverrideTest.php` (5 test); `ContentModel::validationRules` custom tipleri reddettiği için testte builder ile insert yapılıyor.
- **Header/footer scripts:** `header_scripts` + `footer_scripts` ayarları (textarea), `settings/form.php` textarea render, theme header/footer'a verbatim inject. Test `ScriptsInjectionTest.php` (4 test).
- **Latent bug:** `seed.php` editor rolü JSON'unda çift tırnaklar PHP double-quote içinde escape edilmemişti → `\"` ile düzeltildi; `php seed.php` çalışır, dev DB re-seed edildi.
- **Sonuç:** 109 test / 353 assertion — tamamı yeşil.

## Faz 1-1 — Theme Config Sistemi

- `Theme\Libraries\ThemeConfig` (`modules/Theme/Libraries/ThemeConfig.php`): tema `config.php` şemasından (key/label/type/default/options; text/toggle/textarea/select) dinamik field tanımları; `resolve()` default+saved merge, `save()` yalnızca şemadaki anahtarları `themes.config` JSON'a yazar (toggle normalize, QueryCache forget), `saved()`/`schema()` yardımcıları.
- `app/Views/themes/default/config.php`: örnek şema (brand_color, show_search, show_featured, footer_text, header_layout).
- Admin: `ThemeAdminController::config/saveConfig`, rotalar `admin/themes/config/{id}` (GET form / POST save), view `app/Views/admin/themes/config.php`, themes index'te "Configure" butonu.
- Frontend: `Home::resolveTheme` → `$theme_config` tüm theme view'larına; default tema header (brand_color CSS, header_layout), index (show_search, show_featured), footer (footer_text) kullanıyor.
- Test: `tests/feature/ThemeConfigTest.php` (7 test): şema okuma, resolve merge, save whitelist, admin form, admin save, frontend render.
- **Sonuç:** 100 test / 327 assertion — tamamı yeşil.
## Düzeltilen Hatalar

### 1. `Undefined variable $canRestore`
- **Dosya:** `modules/Content/Controllers/Admin/ContentAdminController.php`
- **Neden:** `revisions()` metodu view'a `$canRestore` değişkenini göndermiyordu.
- **Çözüm:** View'a `$data['canRestore'] = $this->can('content.edit');` eklendi.

### 2. `Undefined variable $contentOptions`
- **Dosya:** `modules/Menu/Controllers/Admin/MenuAdminController.php`
- **Neden:** `edit()` metodu `contentOptions` değişkenini view'a göndermiyordu.
- **Çözüm:** Admin'deki "Link to Content" seçimi için published içerik listesi view'a eklendi.

### 3. `Updates are not allowed unless they contain a "where" or "like" clause`
- **Dosyalar:** `modules/Setting/Models/SettingModel.php`, `modules/Setting/Controllers/Admin/SettingAdminController.php`
- **Neden:** `setSetting()` ve `update()` metotlarında `id` kontrolü eksik veya hatalı değişken kullanımı vardı.
- **Çözüm:** `id` varlığı ve boş olmama kontrolü eklendi; `logActivity()` çağrısı düzeltildi.

### 4. `An invalid form control with name='file' is not focusable`
- **Dosya:** `app/Views/admin/media/form.php`
- **Neden:** Gizli (`ck-hidden`) `<input type="file">` üzerinde `required` attribute'ü vardı; tarayıcı validasyonu odaklanamıyordu.
- **Çözüm:** HTML `required` attribute'ü kaldırıldı; JavaScript ile form submit öncesi dosya seçimi kontrol ediliyor.

## Tamamlanan Son Özellikler

### 1. RBAC — Rol ve Yetki Yönetimi
- `RoleModel`, `RoleEntity`, `Permission` kütüphanesi oluşturuldu.
- `PermissionFilter` ile admin route'ları yetkilendirildi.
- Admin'de Roles ve Users CRUD sayfaları eklendi.
- Editor rolü ile sınırlandırılmış kullanıcı test edildi.
- Content yönetebilen ama users/settings erişemeyen editor rolü çalışıyor.

### 2. Audit Log — Aktivite Günlüğü
- `activity_logs` tablosu ve `ActivityLogModel` oluşturuldu.
- `ActivityLog` kütüphanesi ile CRUD işlemleri otomatik loglanıyor.
- Content, Media, User, Menu, Settings işlemlerinde log kaydı tutuluyor.
- Admin'de `admin/activity-logs` listeleme sayfası eklendi.

### 3. İçerik Sürümleme (Revisions)
- `content_revisions` tablosu oluşturuldu.
- Her güncellemede önceki durum otomatik snapshot olarak kaydediliyor.
- `admin/content/revisions/{id}` ile geçmiş görüntülenebiliyor.
- `admin/content/restore/{id}/{revision_id}` ile önceki versiyona dönülebiliyor.
- Restore işlemi önce mevcut durumu yeni bir revisyon olarak saklıyor.

### 4. RSS + Breadcrumb SEO
- `/feed.xml` ve `/rss.xml` endpoint'leri valid RSS 2.0 üretiyor.
- Breadcrumb navigasyonu eklendi (home, category, single, page, tag).
- OpenGraph meta tag'leri eklendi.
- RSS `<link rel="alternate">` header'a eklendi.

### 5. PHPUnit Test Altyapısı
- `phpunit.xml` ve `tests/bootstrap.php` oluşturuldu.
- Feature testler: `AuthTest`, `ContentTest`
- Unit test örneği: `ExampleTest`
- **Sonuç: 10 test, 17 assertion — tamamı geçti.**

### 6. Yorum Sistemi
- `comments` tablosu ve `CommentModel` oluşturuldu.
- Frontend yorum formu içerik detay sayfasına eklendi.
- Admin moderation paneli: onaylama, spam, silme.
- Onaylı yorumlar frontend'de görünür.

### 7. Çoklu Dil Desteği (i18n) — Pratik JSON Tabanlı
- `content`, `terms`, `menus`, `menu_items` tablolarına `locale` ve `translation_group_id` alanları eklendi.
- `localized_url()` ve `current_locale()` helper fonksiyonları eklendi (`app/Helpers/i18n_helper.php`).
- Admin'de içerik locale filtresi, dil badgesi ve "Translations" yönetim sayfası eklendi.
- JSON export/import ile çeviri dosyası indirme/yükleme özelliği eklendi.
- Frontend'de dil switcher ve hreflang/x-default desteği eklendi.
- URL prefix desteği: `/`, `/en/`, `/en/content/...`, `/tr/icerik/...`
- Default locale'e otomatik fallback mekanizması eklendi.
- Dil bazlı sitemap (`/sitemap.xml`, `/en/sitemap.xml`) ve RSS (`/feed.xml`, `/en/feed.xml`) desteği eklendi.

### 8. İletişim Formu / Form Builder
- `contact_forms` ve `contact_submissions` tabloları oluşturuldu.
- Admin'de dinamik form alanları ile form oluşturma/düzenleme.
- Text, email, textarea, select, checkbox alan tipleri destekleniyor.
- Admin submissions listesi, detay görüntüleme ve durum yönetimi (new, read, archived).
- Frontend `/contact` ve `/contact/{slug}` sayfaları.
- CSRF koruması ve başarılı gönderim mesajı.
- E-posta bildirim entegrasyonu (SMTP ayarları varsa).
- `seed.php` içinde varsayılan "General Contact" formu eklendi.

### 9. Cache ve Performans
- `PageCache` kütüphanesi ile frontend HTML sayfaları dosya tabanlı cache'leniyor.
- `QueryCache` kütüphanesi ile sık kullanılan sorgular cache'leniyor (`SettingModel`, `ThemeModel`).
- Cache ayarları `settings` tablosundan yönetiliyor: `cache_enabled`, `cache_ttl`.
- Admin'de `admin/cache` sayfası ile cache temizleme (page, query, all).
- Ayar güncellemelerinde query cache otomatik invalid ediliyor.

### 5. `POST /admin/themes/activate/1` 500 — `Updates are not allowed unless they contain a "where" or "like" clause`
- **Dosya:** `modules/Theme/Models/ThemeModel.php`
- **Neden:** Tüm temaları pasif yapmak için `$this->update(null, ...)` veya `$this->set(...)->update()` kullanımı CI4'te toplu güncelleme için yetersiz kaldı.
- **Çözüm:** `$this->builder()->update(['is_active' => 0])` ile query builder üzerinden toplu güncelleme yapıldı.

### 9. İçerik İlişkileri
- `content_collections`, `content_collection_items`, `content_relations` tabloları ve `content.is_featured` sütunu.
- Admin `admin/collections` CRUD + her koleksiyona içerik attach/detach yönetimi.
- İçerik formunda featured checkbox + çoklu "ilgili içerik" seçici; listede ★ toggle.
- Frontend: ana sayfa "Featured Content", içerik detayında "Related Content" bölümleri.
- `tests/feature/CollectionRelationTest.php` — 5 test / 32 toplam test yeşil.

### 10. Yedekleme ve Bakım
- `Maintenance` modülü ve `backups` tablosu (migration).
- `BackupManager` kütüphanesi: SQLite `VACUUM INTO` ile `writable/backups/` altına snapshot yedekleri; listeleme/download/silme/retention.
- CLI: `php spark backup:create [--keep=N]` — cron ile otomatik planlama (SecurityLog kaydı).
- Bakım modu: `maintenance` global filter; `maintenance_enabled` ayarı açıkken public istekler 503 bakım sayfası döner; `/admin/*` erişilebilir kalır.
- Admin `admin/maintenance` : yedek oluşturma/download/silme, bakımı aç/kapat, retention ayarı.
- `tests/feature/BackupMaintenanceTest.php` — 4 test.

### 11. v2 A — İç Hook / Event Sistemi
- `app/Libraries/Hooks.php`: WordPress tipi filtre + aksiyon katmanı (priority, stabil usort, `registered`/`reset`).
- Dispatcher çağrı noktaları: `content.created/updated/deleted`, `comment.created`, `user.created/updated/deleted`, webhook dispatch'leri.
- Admin `admin/hooks` sayfası (dokümante event listesi + çalışma zamanında kayıtlı hook'lar).
- `tests/feature/HooksTest.php` — 5 test.

### 12. v2 B — Markdown + Content Representations
- `app/Libraries/ContentRenderer.php`: tek kaynak → çoklu temsil (güvenli HTML, metin, excerpt) `content.render.*` hook'larıyla; DOM allowlist sanitizer.
- Entegrasyon: `Home::feed()` description, tema view'ları (single body render + tüm excerpt'ler), API `render` (html/text/excerpt).
- Admin: `admin/content/preview` AJAX endpoint + "Preview Rendered HTML" butonu (canlı önizleme).
- `tests/feature/ContentRendererTest.php` — 7 test.

### 13. v2 C — Custom Sections & Fields
- `content.custom_data` (TEXT nullable JSON) + `content_type_schemas` tablosu (migration'lar dev/test DB'lerine uygulandı).
- `ContentTypeSchemaModel`: `getSchema`/`setSchema` (upsert)/`allWithTypes`.
- `Content\Libraries\CustomFields`: schema okuma, POST'tan `custom[...]` toplama (`collect`), şemaya göre doğrulama (`validate`, required/email/url/select) ve default merge. Tipler: text, textarea, number, email, url, select, checkbox, date, datetime, toggle.
- `ContentEntity` → `custom_data` cast `?json-array` (JSON encode/decode); `ContentModel::allowedFields` + API create/update'ta `custom_data` JSON encode.
- Admin `admin/content/schemas` CRUD: SchemaAdminController + rotalar + sidebar "Custom Fields" linki + liste/edit view'ları (dinamik alan editörü, JS ile satır ekle/kaldır).
- Admin content formu: seçilen content_type'a göre JS ile dinamik custom field render; `prepareContentData()` ile `custom_data`'nın kaydı; `validateCustomFields` ile store/update öncesi doğrulama.
- API: `custom_data` yanıtta decode edilmiş dizi olarak döner.
- Düzeltilen latent hatalar: `Webhook::dispatch` static olmayan metoda static çağrıydı → `new Webhook()`; `create()` `$targetLocale` göndermiyordu.
- `tests/feature/CustomFieldsTest.php` — 9 test.

### 14. v2 D2 — Async Media Queue
- `media_jobs` tablosu (type, media_id, payload, status pending/processing/done/failed, attempts, max_attempts, available_at, error, result) + `media.file_path` eksik migration'ı eklendi (latent bug — model/controller sütunu kullanıyordu ama tabloda yoktu).
- `Media\Libraries\MediaQueue`: `enqueue` (delay = available_at), `claim` (worker-lock: pending→processing, rakip worker satır etkilenmezse atlanır), `work`/`process`, `markDone`, `markFailed` (exponential backoff 30 * 2^(n-1); max_attempts sonrası kalıcı failed), `retry`, `stats`, `recent`, `countPendingFor`.
- Job tipleri: `thumbnail` (MediaHelper::createThumbnail → `media.thumbnail_path`), `resize` (ImageProcessor::resize → width/height güncelle + thumbnail yeniden).
- CLI: `media:queue [--limit=N] [--type=TYPE]` (`MediaQueueCommand`, cron'a uygun); failed işlerde SecurityLog::warning.
- Admin: upload `store()` artık thumbnail'i satır içi yapmıyor, kuyruğa atıyor; `admin/media/queue` status paneli (stat kartları + Run Queue Now + retry), sidebar "Media Queue" linki.
- Düzeltilen latent hatalar: admin `store()` `validateFile()` dönüş yapısını (dizi) yanlış okuyordu → `['success']` yerine boşluk kontrolü.
- `tests/feature/MediaQueueTest.php` — 7 test (enqueue/claim/lock/work retry backoff/upload enqueue/admin panel). CI4 feature testlerinde dosya yüklemesi için `$_FILES` superglobal'i `is_uploaded_file()`'da takıldığından mock `UploadedFile` + `FileCollection` enjeksiyonu kullanıldı.

### 15. v2 D — Magic Link / Şifresiz E-posta Girişi
- `magic_links` tablosu (user_id, token, expires_at, used_at; user_id+token+expires_at indeksleri) + `MagicLinkModel` (`validToken`, `invalidateForUser`).
- `User\Libraries\MagicLink`: `issue` (önceki linkleri tek kullanımlık yapar — son link kazanır), `linkFor` (admin/magic-link/{token}), `consume` (tek kullanım, used_at işaretlenir), `isEnabled` (`magic_link_enabled` ayarı, varsayılan true).
- `AuthController`: `magicLinkForm` (GET form), `magicLinkRequest` (POST — valid_email + LoginThrottle; **anti-enumiration**: var/olmaya kullanıcı aynı yanıt), `magicLinkConsume` (token → kullanıcı aktif mi + TOTP challenge + session set + SecurityLog/ActivityLog).
- Login sayfasına "Passwordless sign-in" bağlantısı + `/admin/magic-link` form view + `app/Views/emails/magic_link.php` e-posta şablonu (Mailer::sendView).
- Ayarlar: `magic_link_enabled` boolean (`SettingSeeder` + `seed.php`'ye eklendi).
- Düzeltilen latent hata: `magic_links` migration'ı ilk haliyle `updated_at` içermiyordu, model timestamps kullandığı için insert patlıyordu → migration'a `updated_at` eklendi (dev DB rollback+re-migrate).
- `tests/feature/MagicLinkTest.php` — 7 test (form render, link oluşturma + süre, anti-enumiration, consume+tek kullanım, geçersiz token, issue revoke, ayar kapatma). Not: toggle testi `magic_link_enabled=false`'yi paylaşılan test DB'sine yazar → setUp her testte true'ya çeker.

## Canlı Test Sonuçları

| Endpoint | HTTP Kodu | Durum |
|---|---|---|
| `GET /` | 200 | ✅ |
| `GET /en/` | 200 | ✅ |
| `GET /search?q=...` | 200 | ✅ |
| `GET /sitemap.xml` | 200 | ✅ |
| `GET /robots.txt` | 200 | ✅ |
| `GET /feed.xml` | 200 | ✅ |
| `GET /contact/contact` | 200 | ✅ |
| `POST /contact/contact/submit` | 303 | ✅ |
| `GET /admin/contact-forms` | 200 | ✅ |
| `GET /admin/cache` | 200 | ✅ |
| `POST /admin/cache/clear-all` | 303 | ✅ |
| `POST /admin/themes/activate/1` | 303 | ✅ |
| `POST /admin/auth/attempt` | 303 | ✅ |
| `GET /admin/dashboard` | 200 | ✅ |
| `vendor/bin/phpunit` | — | ✅ 10/10 |

## İstatistikler

- **Toplam route:** 145+
- **Modül sayısı:** 9
- **Migration sayısı:** 30+
- **Test sayısı:** 93 (299 assertion — tamamı yeşil)
- **Aktif diller:** tr, en

## Sıradaki Adımlar

1. **v2 yol haritası** (DEVELOPMENT.md #11) sırayla: A (Hook), B (Markdown + Representations), C (Custom Sections & Fields), D2 (Async Media Queue), **D-Magic Link (şifresiz giriş)** ve **E (GDPR Export + Virtual Pages)** tamamlandı → sırada uçtan uca canlı regresyon.
2. **Uçtan uca canlı regresyon testi** (seed + admin panel + frontend + bakım modu/backup CLI + `media:queue` + magic-link + GDPR export + virtual pages).

## Notlar

- Sunucu `tmux` oturumunda `php spark serve --port 8080` ile çalışıyor.
- Varsayılan admin girişi: `admin@kayacms.local` / `admin123`
- SQLite veritabanı: `writable/db/kayacms.db`
- Çeviri JSON formatı: `translation-{slug}-{locale}.json`
