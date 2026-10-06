# 04 — Arsitektur dan Kontrak Aplikasi

## Bentuk sistem

Laravel monolith dengan Blade dan Vite, menggunakan MySQL 8.x/InnoDB. Visitor dan Admin memakai browser. Laravel memiliki data katalog, approval, provenance, dan klik; Shopee menangani transaksi pembelian setelah redirect. Browser mengambil media dari provider yang disetujui; server aplikasi tidak menjadi media proxy. Model ini tidak membutuhkan microservices, React, queue, Redis, Elasticsearch, atau Docker sebagai syarat MVP.

```text
Browser Visitor / Admin
  → HTTPS → Laravel routes + middleware
  → Controller → FormRequest / Policy
  → Action (mutasi + transaksi) atau Service (query)
  → Eloquent / MySQL (PK, FK, CHECK, UNIQUE, RESTRICT)
  ← Blade / redirect / controlled error

Browser → approved external media
Browser → Shopee hanya setelah click transaction commit
Raw CSV → protected evidence storage + import tables (normalization gated)
```

## Pembagian modul dan file

| Modul/lapisan | Tanggung jawab | Area file yang dibangun manusia |
| --- | --- | --- |
| Routing/middleware | HTTP method, binding ID/slug, auth aktif, CSRF. | `routes/web.php`, middleware configuration |
| Controllers | Bind, validate, authorize, panggil use case, bentuk response. | `app/Http/Controllers/{Public,Admin,Affiliate}/` |
| Form Requests | Field validation, reference/ownership check, errors. | `app/Http/Requests/{Public,Admin}/` |
| Policies | Domain permission dan separate `approveAffiliate`. | `app/Policies/` |
| Actions | State transition, transaksi, lock order, rollback. | `app/Actions/{Admin,Affiliate,Import}/` |
| Services | Catalog/detail/dashboard/analytics query dan reusable support. | `app/Services/{Catalog,Analytics,Media}/` |
| Security support | URL per purpose, resolver interface, parser-based HTML sanitizer. | `app/Support/{UrlValidation,HtmlSanitization}/`, configuration |
| Models | Relasi, casts, scopes, immutable record protections. | `app/Models/` |
| Views/assets | Escaping, forms, pagination, nullable/media fallback, responsive. | `resources/views/{public,admin}/`, `resources/{css,js}/` |
| Database/tests | Approved schema dan bukti perilaku; bukan aturan di template. | `database/migrations/`, `tests/` |

Nama Action/Service di registry merupakan kontrak pembagian tanggung jawab. Struktur class internal dapat direview tim selama tidak mengubah route, security boundary, atau hasil use case. Jangan menambah generic repository layer tanpa kebutuhan yang terbukti.

## Registry route/form

Ini kontrak aplikasi Blade/form, bukan rancangan public JSON REST API. Semua Admin route selain login memerlukan konteks Admin; logout tetap membersihkan sesi akun yang menjadi inactive. Semua write menggunakan CSRF.

| Method dan path | Route name | Owner / hasil |
| --- | --- | --- |
| GET `/` | `home` | `HomeController` / `CatalogService`; selected active reads. |
| GET `/catalog` | `catalog.index` | `CatalogController` / `CatalogQueryService`; paginated name search/filter. |
| GET `/products/{slug}` | `products.show` | `ProductController` / `ProductDetailService`; active detail atau 404. |
| POST `/go/{product}` | `affiliate.redirect` | `RecordAffiliateClickAndRedirectAction`; click commit lalu redirect. |
| GET `/admin/login` | `admin.login` | Form login. |
| POST `/admin/login` | `admin.login.store` | Dedicated Admin guard; session regeneration. |
| POST `/admin/logout` | `admin.logout` | Logout, invalidate/regenerate session. |
| GET `/admin/dashboard` | `admin.dashboard` | `DashboardMetricsService`; angka nol valid. |
| GET `/admin/products` | `admin.products.index` | List status/Category/page. |
| GET `/admin/products/create` | `admin.products.create` | Form; Category active/inactive ditandai. |
| POST `/admin/products` | `admin.products.store` | `CreateProductAction`; atomic Product/images/specifications. |
| GET `/admin/products/{product}/edit` | `admin.products.edit` | Form atau 404. |
| PUT `/admin/products/{product}` | `admin.products.update` | `UpdateProductAction`; invariant dan transaksi. |
| DELETE `/admin/products/{product}` | `admin.products.destroy` | `DeactivateProductAction`; soft-delete + inactive, history utuh. |
| POST `/admin/products/{product}/affiliate-approval` | `admin.products.affiliate-approval.store` | `ReviewAffiliateUrlAction`; policy `approveAffiliate`. |
| GET `/admin/categories` | `admin.categories.index` | Category list/form management. |
| POST `/admin/categories` | `admin.categories.store` | `CreateCategoryAction`. |
| PUT `/admin/categories/{category}` | `admin.categories.update` | `UpdateCategoryAction`; no-cycle dan lifecycle guard. |
| DELETE `/admin/categories/{category}` | `admin.categories.destroy` | `DeactivateCategoryAction`; controlled-reference restrictions. |
| GET `/admin/products/{product}/reviews` | `admin.reviews.index` | Referensi review Product. |
| POST `/admin/products/{product}/reviews` | `admin.reviews.store` | `CreateProductReviewAction`; review/images atomic. |
| PUT `/admin/reviews/{review}` | `admin.reviews.update` | `UpdateProductReviewAction`. |
| DELETE `/admin/reviews/{review}` | `admin.reviews.destroy` | `HideProductReviewAction`; `is_visible = false`. |
| GET `/admin/analytics` | `admin.analytics.index` | Total/per-product/popular/video counts; period max 366 hari. |
| GET `/admin/imports` | `admin.imports.index` | Rancangan gated; batch list. |
| GET `/admin/imports/{batch}` | `admin.imports.show` | Raw evidence hanya untuk Admin. |
| POST `/admin/imports` | `admin.imports.store` | `RegisterImportBatchAction`; authoritative server checksum. |
| POST `/admin/imports/{batch}/records/{record}/match` | `admin.imports.records.match` | `ResolveImportMatchAction`; manual resolution. |
| POST `/admin/imports/{batch}/records/{record}/normalize` | `admin.imports.records.normalize` | `NormalizeImportRecordAction`; **belum boleh dibangun sebelum gate**. |

Route impor di atas merupakan kontrak rancangan, bukan bukti endpoint tersedia atau keputusan scope review telah selesai. Registrasi file CSV tidak sama dengan upload media Product; larangan media upload tidak menghapus kontrak registrasi CSV yang terkontrol.

## Field manual Product

| Field | Request contract |
| --- | --- |
| `name`, `slug` | Wajib; max 255; slug unik, name tidak unik. Slug update mengabaikan ID Product sendiri pada unique check. |
| `is_active` | Boolean eksplisit pada create/update sesuai technical review TASK-013; omitted intent ditolak. |
| `category_id` | Draft nullable; supplied ID existing/non-deleted; resulting active harus active/non-deleted. |
| `electronics_scope_confirmed` | Request-only `accepted` untuk setiap resulting-active; tidak mass-assign/persist; checkbox fresh. |
| `label`, `store_name` | Nullable max 100/255. |
| `store_url`, `review_video_url` | Nullable; HTTPS, max 2048 input dan normalized ASCII bytes, purpose-specific validation. |
| `description_html` | Nullable untrusted input; max 100000 byte UTF-8 sebelum sanitizer; oversize ditolak, bukan truncate. |
| `description_text` | Server-derived dari sanitized description, tidak berasal dari client dan tidak searchable. |
| `price_amount` | Nullable DECIMAL(15,2), nonnegative; periksa batas schema dan precision. |
| `price_currency` | Required bila amount tersedia; CHAR(3), ISO-style approved code; bukan izin menebak currency. |
| `shopee_rating` | Nullable aggregate 0.00–5.00, sumber approved; bukan rata-rata `product_reviews`. |
| `shopee_review_count` | Nullable nonnegative unsigned integer; bukan jumlah limited sample. |
| `marketplace` | Server-controlled `shopee`; input client tidak menentukan domain marketplace. |
| `published_at` | Dipertahankan saat update, null saat create pada technical review; bukan mekanisme scheduling baru. |
| `images` | External URL, nullable alt max255, order nonnegative; ID child harus milik Product. |
| `specifications` | name max255, value TEXT, order nonnegative; unique name per Product sesuai collation. |

Bentuk collection yang telah direview pada task memakai child IDs dan explicit removal; implementer harus menjaga kontrak pada snapshot task, bukan menebak replacement-all. Collection yang tidak dikirim tidak berarti hapus semuanya. Hanya child manual tanpa `import_record_id` dapat diubah/dihapus melalui batas generic edit yang diizinkan; source-linked evidence tidak direwrite. Seluruh Product dan child write atomik. Detail nama removal fields harus mengikuti task contract, dan diverifikasi sebelum membuat form.

**Protected input:** affiliate approval/status/provenance/source/actor/time, arbitrary redirect target, client trusted HTML flag, import IDs/provenance, timestamps yang server-owned, dan classification fields tidak dapat mengubah state melalui generic Product edit. Tolak/abaikan hanya sesuai whitelist request yang eksplisit dan uji bahwa data tidak berubah; jangan mass-assign seluruh request.

## URL dan HTML trust boundary

| Purpose | Field | Aturan |
| --- | --- | --- |
| Marketplace | `store_url`, `affiliate_url` | Exact approved marketplace hosts; affiliate tetap approval terpisah. |
| Image | `product_images.image_url`, `product_reviews.avatar_url`, `review_images.image_url` | Exact approved media providers; image indexed URLs disimpan ASCII sesuai schema. |
| Video | `review_video_url` | Exact approved video/media providers; tanpa arbitrary embed HTML. |

Allowlist berupa application configuration, bukan database taxonomy/enum. Default kosong menolak URL. Exact production values wajib diberikan PT Imersa. Normalisasi host/IDN dengan library, HTTPS/port443, tanpa userinfo, exact host comparison, semua resolved IPv4/IPv6 harus public; ambiguous/DNS failure ditolak. Host terdaftar tidak membebaskan address checks. Lakukan DNS validation sebelum database locks; tidak fetch media, follow redirects, atau scraping. URL input dan hasil ASCII tidak boleh melebihi 2048 byte. Tidak otomatis mengubah HTTP legacy menjadi HTTPS.

Sanitizer menggunakan parser server-side, technical review memilih `symfony/html-sanitizer:^7.4` dengan dependency lock yang kompatibel. Allowed tags: `p`, `br`, `strong`, `b`, `em`, `i`, `ul`, `ol`, `li`, `h2`, `h3`, `h4`, `blockquote`. Pilihan TASK-013 tidak mempertahankan attributes atau active links; safe text dapat dipertahankan. Dangerous subtrees/inline events/scriptable content tidak boleh executable. Sanitizer failure menggagalkan seluruh save. Tidak ada regex sanitizer buatan sendiri.

Blade escape text, old input, errors, URL attributes, dan raw evidence. Hanya output sanitizer yang boleh masuk HTML rendering sink. `description_text` diturunkan server-side, tetap internal dan bukan searchable field. Raw imported HTML tetap immutable sampai approved normalization.

## Transaksi dan concurrency

| Operasi | Unit atomik dan kondisi commit |
| --- | --- |
| Product create/update | Product + child manual changes. Lock Category old/new secara urutan ID konsisten, lalu lock/reload Product. Recheck Category/confirmation intent, ownership, dan state sebelum write. |
| Product reassignment | Jika reload menunjukkan preliminary Category telah berubah, restart terbatas dengan lock set benar; tidak lanjut memakai snapshot lama. |
| Category inactive/destroy | Memakai Category lock yang sama; cek referensi dengan current/locking read sebelum perubahan; tidak melewati aturan destroy. |
| Affiliate approval | URL/status/provenance/source/approver/time berubah bersama atau seluruhnya rollback. |
| CTA | Final validate setelah lock/reload; copy URL, insert click, commit; HTTP redirect di luar transaksi memakai exact snapshot. |
| Review | Review + review images atomik; hide tidak menghapus provenance. |
| Import (gated) | Raw registration konsisten; normalization + link provenance satu unit per record setelah gate disetujui. |

Deadlock/timeout ditangani rollback dan bounded retry atau controlled failure. Database FK tidak dapat menjamin `Category.is_active`; FormRequest sebelum transaksi juga tidak cukup. Uji dua koneksi MySQL dengan barrier, kedua urutan pemenang, dan recheck invariant setelah commit.

## Response dan errors

Successful write mengikuti Post/Redirect/Get dengan flash success. Validation kembali ke form dengan safe old input dan field errors; gagal bukan draft tersimpan. Unauthorized Admin menuju login; policy denial 403; absent/unavailable resource 404; DB failure controlled 5xx tanpa stacktrace/raw payload/secrets. CTA failure tidak memiliki redirect eksternal. CSRF wajib diuji dengan middleware benar-benar aktif.

Sumber: `ARCHITECTURE.md` §§3–15, `API_CONTRACT.md` §§1–15 dan technical review/task contract TASK-013. Snapshot exact request collections serta route registry tersedia sebagai referensi dalam paket.
