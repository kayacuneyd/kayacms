# KayaCMS Geliştirme Planı

Bu doküman KayaCMS'in mevcut mimarisini, tamamlanan özelliklerini ve gelecekteki geliştirme yol haritasını içerir.

## Proje Vizyonu

KayaCMS; hafif, modüler, performanslı ve çok dilli içerik yönetimi sunan modern bir CodeIgniter 4 tabanlı CMS'dir. Amaç, WordPress/WPML gibi ağır sistemlere alternatif, geliştirici ve içerik üreticisi dostu bir platform oluşturmaktır.

## Mevcut Mimari

- **Framework:** CodeIgniter 4
- **Veritabanı:** SQLite (geliştirme), MySQL/PostgreSQL destekli
- **Auth:** JWT (API) + Session (Admin)
- **Modüller:** User, Content, Media, Taxonomy, Menu, Setting, Theme
- **Frontend:** PHP view şablonları + CKCSS
- **Editor:** CKEditor 5
- **Test:** PHPUnit

## Tamamlanan Özellikler

1. Kullanıcı yönetimi ve authentication (JWT + Session)
2. Modüler yapı (User, Content, Media, Taxonomy, Menu, Setting, Theme)
3. Admin paneli CRUD işlemleri
4. Medya yükleme, thumbnail oluşturma ve silme
5. Frontend tema motoru (home, single, category, tag, page)
6. CKEditor 5 entegrasyonu
7. Arama (admin + API + frontend)
8. SEO (sitemap.xml, robots.txt, meta tags, RSS, breadcrumb)
9. Menü yönetimi (nested items)
10. RBAC — Rol ve yetki yönetimi
11. Audit Log — Aktivite günlüğü
12. İçerik sürümleme (Revisions) ve geri yükleme
13. Yorum sistemi (frontend form + admin moderation)
14. PHPUnit test altyapısı
15. Güvenlik: brute-force koruması (login attempt limit), 2FA (TOTP), güvenlik audit raporu
16. Dashboard widget'ları ve UX (aktivite widget, içerik durumu grafikleri, toast, filtreli listeleme)
17. API gelişmişlikleri: rate limiting, OpenAPI dokümantasyonu, API token yönetimi, webhook desteği
18. İçerik ilişkileri: ilgili içerikler önerisi, öne çıkan içerikler, içerik koleksiyonları
19. Yedekleme ve bakım: DB yedekleme aracı, otomatik zamanlama (CLI), bakım modu
20. v2 A: İç Hook/Event Sistemi (`app/Libraries/Hooks.php`)
21. v2 B: Markdown + Content Representations (`app/Libraries/ContentRenderer.php`)
22. v2 C: Custom Sections & Fields (`content.custom_data`, `content_type_schemas`, `Content\Libraries\CustomFields`, `admin/content/schemas`)
23. Faz 1-1: Theme Config sistemi (`Theme\Libraries\ThemeConfig`, `admin/themes/config`, `$theme_config`)
24. Faz 1-2: Content-type view override (`Home::resolveSingleView`, `single-{type}.php`)
25. Faz 1-3: Global header/footer scripts (`header_scripts`/`footer_scripts` ayarları, tema layout inject)
26. Faz 1-4: CKEditor paketleme (yerel `public/assets/vendor/ckeditor/ckeditor.js`, CDN referansı kaldırıldı)
27. Faz 1-5: Örnek "minimal" tema (`app/Views/themes/minimal/`; install/activate/config döngüsü doğrulandı)

## Geliştirme Yol Haritası

Aşağıdaki özellikler sırayla uygulanacaktır.

> Güncelleme (2026-08-09): Bu yol haritası tamamlandı. Maddelerin tamamı
> yukarıdaki "Tamamlanan Özellikler" listesine taşınmıştır; detaylar için
> `PROGRESS.md` bölümlerine bakın.

### 11. Yol Haritası v2 — Eksik Kirby Benzeri Özellikler

Kirby CMS ile yapılan karşılaştırma sonucu mevcut projede eksik/boşta kalan
özellikler aşağıda kayda alınmıştır. Öncelik sırası fayda/çaba oranına göre
belirlenmiştir (`🟢` = kolay & yüksek uyum, `🟡` = orta, `🔴` = büyük iş).

#### Öncelikli Özellikler (A–F, uygulama sırası)

1. **A. İç Hook / Event Sistemi (Eklenti Temeli)** — 🟢
   - **Hedef:** Modüller ve eklentiler için dahili `Hooks` API'si; CI4 `Events`
     ile entegre hafif bir filtre + aksiyon katmanı.
   - `Hooks::addFilter('content.title', fn ($title) => ...)`, `Hooks::addAction('content.saved', ...)`.
   - `Hooks::applyFilters($hook, $value, $args)` (değer döndürür),
     `Hooks::doAction($hook, $args)` (yan etki).
   - Aynı hook'lar frontend + admin + API'de ortak çalışır (webhook dışa
     dönük çağrı iken hook iç sistemin olay katmanıdır).
   - Admin'de kayıtlı hook'ları görüntüleme sayfası.

2. **B. Markdown + Content Representations** — 🟢
   - `body` alanına markdown desteği (hafif parser kütüphanesi).
   - İçerikten çoklu temsil: HTML, Markdown, JSON (API), meta veriler (OG/RSS).
   - Tek kaynak (`excerpt` veya `body`) → planlı dönüşüm.

3. **C. Custom Sections & Fields** — 🟢
   - Content tipi başına kullanıcı tanımlı alan/şema (JSON).
   - Admin formda dinamik alan render + endpoint erişimi.
   - `custom_data` kolonunda saklanır.

4. **D. Async Media Queue + Magic Link Girişi** — 🟢
   - ~~OAuth (Google/GitHub)~~ çekirdekten çıkarıldı (dağıtılabilir kurulum için
     dış kayıt/domain bağımlılığı yaratıyor; değer düşük). Yerine **Magic Link /
     şifresiz e-posta girişi** eklendi.
   - **D2 (Async Media Queue) tamamlandı:** Media işleme işleri queue tablosu +
     CLI worker; admin'de status paneli.
   - **D-Magic Link tamamlandı:** e-posta ile tek kullanımlık giriş bağlantısı
     (`magic_links` tablosu, `MagicLink` kütüphanesi, `/admin/magic-link` form
     + consume rotası, `magic_link_enabled` ayarı).

5. **E. GDPR Export + Virtual Pages** — 🟢
   - **GDPR Export tamamlandı:** `User\Libraries\GdprExport` (kullanıcının tüm
     kişisel verisi — profil, içerik, yorumlar, medya, bildirimler, activity/
     security logları, API token'ları, magic linkler, şifre sıfırlamaları,
     giriş denemeleri, contact submissions — JSON/CSV; hash'ler ve token'lar
     redact edilir) + `admin/gdpr` arama/export/erase arayüzü (`gdpr.*` izinleri).
   - **Cookie onay banner tamamlandı:** `cookie_consent_enabled` +
     `privacy_policy_url` ayarları, theme footer'da Accept/Decline banner'ı
     (365 gün `ck_consent` çerezi).
   - **Virtual pages tamamlandı:** `virtual_pages` tablosu (slug+handler+
     payload), `Content\Libraries\VirtualPage` (template | markdown | redirect),
     front-end catch-all rota (`Home::virtualPage`), admin
     `admin/virtual-pages` CRUD (`virtual_pages.*` izinleri).

6. **F. Multi-site Setup** — 🟡
   - `site` sütunu/alan bazlı içerik + domain→site map.
   - Modül/izin/ayarlar tenant bazında izole edilir.

#### İleri / Büyük İş (sırasıyla değerlendirilir)

- **H. Vue.js Admin SPA** — 🔴 Tüm admin SSR (view+CKCSS) SPA'ya geçirilmek;
  önce seçici ekranlar (ör. medya yönetimi) pilot olur.
- **I. Flat-file Foundation** — 🔴 SQLite mimarisiyle ters; dosya tabanlı bir
  "export/import flat-file" bridge olabilir (backup modülü üzerine), ama ana
  depo DB kalır. İkinci katman olarak eklenebilir.
- **J. Twig/Blade şablon motoru desteği** — 🔴 CI4 View swap edilebilir; ancak
  mevcut theme yardımcıları/i18n kırılır; tek motor PHP kalır, fayda düşük.

### Öncelikli Uygulama Detayları

**A. İç Hook Sistemi (önerilen sıra: A → B → C → D → E → F)**
- Dosyalar: `app/Libraries/Hooks.php` (statik API), örn.
  - `Hooks::addFilter(string $hook, callable $cb, int $priority = 10)`
  - `Hooks::applyFilters(string $hook, mixed $value, array $args = [])`
  - `Hooks::addAction(string $hook, callable $cb, int $priority = 10)`
  - `Hooks::doAction(string $hook, array $args = [])`
- Çağrı noktaları: content store/update/delete, comment submit/moderate,
  user create/update/delete, webhook dispatch.
- Test: `tests/feature/HooksTest.php` — filter uygulaması, action sırası,
  priority, bağımsız değişkenler.
- Eklenti platformu ileri adım: `modules/*/Config/plugins.php` ya da
  `composer` eklentisi → manifest keşfi (bu yol haritasının A maddesi ile
  temel atılır; tam plugin yöneticisi ileride).

### #11 Yol Haritası Durum Notu

Yukarıdaki v2 yol haritası geliştirme sırasında güncellenir; tamamlanan
maddeler "Tamamlanan Özellikler" bölümüne taşınır.

> Güncelleme (2026-08-10): A maddesi (İç Hook Sistemi) tamamlandı →
> `app/Libraries/Hooks.php` + `tests/HooksTest.php`. B maddesi (Markdown +
> Content Representations) tamamlandı → `app/Libraries/ContentRenderer.php`
> + `tests/ContentRendererTest.php` (sanitize, text, excerpt, feed, API
> `render` temsilleri, admin canlı önizleme). C maddesi (Custom Sections &
> Fields) tamamlandı → `content.custom_data` + `content_type_schemas`
> migration'ları, `Content\Libraries\CustomFields`, `SchemaAdminController`
> (`admin/content/schemas`), admin formda dinamik alan render'ı,
> `tests/CustomFieldsTest.php` (schema CRUD, collect/validate, custom_data
> persist + API erişimi). D2 (Async Media Queue) tamamlandı →
> `media_jobs` migration'ı + `Media\Libraries\MediaQueue` (enqueue/claim/work/
> retry; exponential backoff), `media:queue` CLI komutu, admin
> `admin/media/queue` status paneli, yüklemede thumbnail işi kuyruğa atanır,
> `tests/MediaQueueTest.php` (7 test). Latent bug'lar düzeltildi: `media.file_path`
> eksik migration'ı eklendi, admin `store()` `validateFile()` dönüş yapısına
> uyarlandı. OAuth çekirdekten çıkarıldı; **Magic Link / şifresiz e-posta girişi
> eklendi** → `magic_links` migration'ı + `User\Libraries\MagicLink`
> (issue/consume/tek kullanım), `AuthController::magicLink*`, `/admin/magic-link`
> form + `emails/magic_link` şablonu, `magic_link_enabled` ayarı,
> `tests/MagicLinkTest.php` (7 test). E maddesi (GDPR Export + Virtual Pages)
> tamamlandı → `User\Libraries\GdprExport` + `admin/gdpr` (JSON/CSV export,
> erasure) + `gdpr.*` izinleri; cookie onay banner'ı (`cookie_consent_enabled`,
> `privacy_policy_url`, theme footer); `virtual_pages` migration'ı +
> `Content\Libraries\VirtualPage` (template|markdown|redirect handler'ları,
> hafif markdown renderer) + front-end catch-all + `admin/virtual-pages` CRUD.
> `tests/GdprTest.php` (8), `tests/CookieConsentTest.php` (3),
> `tests/VirtualPageTest.php` (11). **Tamamı: 93 test / 299 assertion yeşil.**

### 12. Yol Haritası Faz 1–4 — Production-readiness, Shared Hosting ve Açık Kaynak

Yeni yön: KayaCMS artık "production-ready, theme-developable, open-source" bir
CMS olacak; hem açık kaynak dağıtım hem de kendi müşteri sitelerimizi üretmek
için kullanılacak. Faz 1 → 4 sırasıyla uygulanır.

#### Faz 1 — Tema Platformu
1. **Theme Config sistemi** ✅ tamamlandı: tema `config.php` şemasından
   dinamik admin formu, `themes.config` JSON sütunu, `$theme_config` → temalar.
2. **Content-type view override** ✅ tamamlandı: `single.php` →
   `single-{content_type}.php` fallback'i (`Home::resolveSingleView`); örnek
   `single-review.php` (custom_data.rating).
3. **Global header/footer script'leri** ✅ tamamlandı: `header_scripts`/
   `footer_scripts` ayarları (textarea) + theme layout'una verbatim inject;
   `seed.php`'de escape hatası düzeltildi.
4. **CKEditor paketleme** ✅ tamamlandı: classic build 41.3.1
   `public/assets/vendor/ckeditor/ckeditor.js` olarak yerelleştirildi; admin
   content form `base_url('assets/vendor/ckeditor/ckeditor.js')` kullanıyor,
   `cdn.ckeditor.com` referansı kaldırıldı (`tests/feature/CkeditorPackagingTest.php`).
5. **Örnek "minimal" tema** ✅ tamamlandı: ikinci tema
   (`app/Views/themes/minimal/`) — çıplak HTML/CSS (Georgia serif, tek sütun),
   kendi `config.php` şeması (`container_width`, `show_author`, `footer_text`);
   install/activate/config döngüsü `tests/feature/MinimalThemeTest.php` ile
   doğrulandı; `seed.php` + `ThemeSeeder` artık iki temayı kaydeder (minimal
   inaktif).
6. **"Landing" tema** ✅ tamamlandı (2026-08-12): koyu/modern tek-sayfa
   tanıtım teması (`app/Views/themes/landing/`). Tüm bölümler tema
   `config.php` şemasından yönetilebilir: hero (badge/headline/subheadline/
   CTA), özellik grid (`features` satır-satır `İkon|Başlık|Açıklama`), blog
   bölümü (`show_articles` toggle), CTA bloğu ve footer iletişim. Koyu palet
   (`#0b1020` zemin, brand renk `#6366f1` — config'den değiştirilebilir),
   sticky nav, gradient glow hero. `tests/feature/LandingThemeTest.php`
   (6 test): homepage render, config-driven hero, feature satır parsing, single
   render, admin config şeması, `show_articles` kapatma. `ThemeSeeder` üçüncü
   tema olarak `landing`'i kaydeder (inaktif). Bu tema "müşteri tanıtım
   sitesi" kalıbının ilk örneğidir — sonraki projelerde kopyalanarak özelleştirilir.

#### Faz 2 — Production / Shared Hosting ✅ (tamamlandı)
1. **Web-cron** ✅ tamamlandı: `Maintenance\Libraries\WebCron` (tek kaynak) +
   `GET cron/run/{token}` endpoint'i → medya queue + backup/prune; boş token =
   403 (güvenli varsayılan); Admin → Backup & Maintenance → Web Cron kartı;
   CLI `php spark webcron:token [--generate]`; `tests/feature/CronTest.php`.
2. **Deploy paketi** ✅ tamamlandı: `.env.example` (sır yok),
   `deploy/nginx-vhost.conf` (80→443 301, `public/` root, `writable|.env` deny),
   `deploy/fix-permissions.sh`, `deploy/DEPLOY.md` deploy checklist.
3. **Production güvenlik toggles** ✅ tamamlandı: `app.securityHardening` env
   switch — açıkken `csrf`+`invalidchars`+`honeypot` (before) + `secureheaders`
   (after); production'da varsayılan açık; `tests/unit/SecurityHardeningTest.php`.

#### Faz 3 — Açık Kaynak ✅ (tamamlandı)
1. **Composer paketi** ✅ tamamlandı: `composer.json` KayaCMS'e özel (name `kayacms/kayacms`, MIT, description, keywords, homepage/support/authors); PSR-4'e tüm modüller eklendi (`Content\`, `Theme\`, … → `modules/<Mod>/`); `composer validate` + lock senkron; `composer dump-autoload -o` (2590 sınıf).
2. **Open-source metadata** ✅ tamamlandı: `README.md` KayaCMS'e özel (özellik listesi, kurulum, deploy linki, tema geliştirme, API, hooks, test); `LICENSE` MIT (KayaCMS + CI Foundation); `app/Config/Version.php` semver tek kaynağı (`Version::current()` → `1.0.0`), admin layout footer'ında gösterilir (DashboardTest doğrular); `CHANGELOG.md` (1.0.0 release notes).
3. **Yayın hazırlığı** ✅ tamamlandı: `composer validate` temiz; semver kaynağı hazır (`app/Config/Version.php` → v1.0.0, git tag atılabilir); git remote `origin` → `github.com/kayacuneyd/kayacms` mevcut; sürüm tag elle atılır.
4. **v1.0.0 yayını** ✅ tamamlandı (2026-08-12): 3 mantıklı commit ile tüm v2
   çalışması `main`'e push edildi + `git tag v1.0.0` eklendi
   (`https://github.com/kayacuneyd/kayacms`). Öncesinde .gitignore temizliği:
   kullanıcı medyası (`public/assets/uploads/`), yedekler (`writable/backups/`),
   debug/phpunit artıkları ve `composer-setup.php`/`myroutes.php` repo dışına
   alındı; `writable/db/seed.sql` takipten çıkarıldı. Güvenlik taraması:
   `.env`, sqlite, API anahtarı, JWT sırrı repo+değil — yalnızca sırsız
   `.env.example`.

#### Faz 4 — Müşteri Sistemi
1. Site şablonu + dağıtım standardı (her müşteri için yeniden üretilebilir kurulum).
2. Sahiplik modeli (çok siteli örnek desen; F maddesi ile bağlantılı).

### 1. Çoklu Dil Desteği (i18n) — Pratik JSON Tabanlı

**Hedef:** Çevirmenlerin admin paneline girmesine gerek kalmadan JSON dosyası üzerinden çeviri yapabilmesi.

**Yaklaşım:**
- Her içerik/term/menu kaydına `locale` ve `translation_group_id` alanları eklenir.
- Tüm çeviriler aynı tabloda ayrı satırlar olarak saklanır.
- Ana kayıt ile çeviriler `translation_group_id` üzerinden ilişkilendirilir.

**JSON Export Formatı:**

```json
{
  "translation_group_id": "a1b2c3d4",
  "source_locale": "tr",
  "target_locale": "en",
  "content_type": "post",
  "fields": {
    "title": "Merhaba Dünya",
    "slug": "merhaba-dunya",
    "body": "<p>İçerik gövdesi</p>",
    "meta_title": "Merhaba Dünya | KayaCMS",
    "meta_description": "Açıklama metni"
  }
}
```

**Admin Arayüzü:**
- İçerik listesinde dil badgesi
- "Çeviri Ekle / Çevirileri Yönet" butonu
- JSON export/import sayfaları
- Default locale ve aktif dillerin settings üzerinden yönetimi

**Frontend:**
- Dil switcher
- URL prefix: `/tr/icerik/{slug}`, `/en/content/{slug}`
- Fallback mekanizması (çeviri yoksa default locale)
- Hreflang ve dil bazlı sitemap

### 2. İletişim Formu / Form Builder

- Dinamik form alanları (text, email, textarea, select, checkbox)
- Admin submissions listesi
- Form yanıtlarına durum atama (new, read, archived)
- E-posta bildirim entegrasyonu

### 3. Cache ve Performans

- Page cache (full page caching)
- Query cache
- Asset minification/bundling
- Redis/Memcached desteği

### 4. Gelişmiş Medya Kütüphanesi

- Klasör/dizin yapısı
- CKEditor medya seçici (görsel ekleme)
- Toplu yükleme
- Görsel düzenleme (crop, rotate, resize)

### 5. E-posta ve Bildirimler

- SMTP ayarlarının settings üzerinden yönetimi
- Şifre sıfırlama e-postası
- Yeni yorum/aktivite bildirimleri
- İletişim formu mail gönderimi

### 6. Güvenlik İyileştirmeleri

- Brute force koruması (login attempt limit)
- İki faktörlü doğrulama (2FA)
- IP bazlı kısıtlama
- Güvenlik audit raporu

### 7. Dashboard Widget'ları ve UX

- Son aktiviteler widget'ı
- İçerik durumu grafikleri
- Toast notifications
- DataTables ile gelişmiş listeleme (arama, sıralama, sayfalama)

### 8. API Gelişmişlikleri

- API rate limiting
- Swagger/OpenAPI dokümantasyonu
- API token yönetimi
- Webhook desteği

### 9. İçerik İlişkileri

- İlgili içerikler önerisi
- Öne çıkan içerikler
- İçerik koleksiyonları

### 10. Yedekleme ve Bakım

- Veritabanı yedekleme aracı
- Otomatik yedekleme zamanlama
- Bakım modu

## Geliştirme Kuralları

- Her yeni özellik kendi modülü içinde geliştirilmeli.
- Migration'lar `YYYY-MM-DD-XXXXXX_Description.php` formatında olmalı.
- Admin controller'ları `BaseAdminController`'dan türemeli.
- Yeni route'lar modül `Config/Routes.php` dosyalarında tanımlanmalı.
- Özellikler için PHPUnit testleri yazılmalı.
- View'lar CKCSS utility class'ları ile stilendirilmeli.

## Teknik Notlar

- `translation_group_id` UUID yerine kısa unique string (12 karakter) olabilir.
- Çeviri fallback'i önce `translation_group_id` içinde aynı dili arar, bulamazsa default locale döner.
- JSON import sırasında slug benzersizliği kontrol edilmeli (`{slug}-{locale}` formatı kullanılabilir).
