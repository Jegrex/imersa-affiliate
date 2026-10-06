# 03 — ERD dan Kamus Data

Rancangan berisi **11 tabel dan 17 foreign key**. MySQL 8.x/InnoDB, BIGINT UNSIGNED untuk ID, utf8mb4 untuk text aplikasi, dan UTC timestamps. Diagram dipisah menjadi panel katalog, impor, dan atribusi Admin untuk menjaga keterbacaan; entity yang ditampilkan ulang adalah referensi tabel yang sama, bukan tabel tambahan.

![ERD Imersa](diagram/erd.svg)

Sumber diagram lengkap: [erd.mmd](diagram/erd.mmd). Kardinalitas berikut melengkapi panel gambar. Semua FK diindex dan ON DELETE RESTRICT; soft-delete bukan pelanggaran FK fisik sehingga application guards tetap diperlukan.

## Relasi lengkap

| ID | Child FK → parent PK | Parent per child | Child per parent | ON DELETE |
| --- | --- | --- | --- | --- |
| R01 | `categories.parent_id` → `categories.id` | 0..1 | 0..N | RESTRICT |
| R02 | `categories.created_by_admin_id` → `admins.id` | 0..1 | 0..N | RESTRICT |
| R03 | `products.category_id` → `categories.id` | 0..1 | 0..N | RESTRICT |
| R04 | `products.affiliate_url_approved_by_admin_id` → `admins.id` | 0..1 | 0..N | RESTRICT |
| R05 | `product_images.product_id` → `products.id` | 1 | 0..N | RESTRICT |
| R06 | `product_images.import_record_id` → `import_records.id` | 0..1 | 0..N | RESTRICT |
| R07 | `product_specifications.product_id` → `products.id` | 1 | 0..N | RESTRICT |
| R08 | `product_specifications.import_record_id` → `import_records.id` | 0..1 | 0..N | RESTRICT |
| R09 | `product_reviews.product_id` → `products.id` | 1 | 0..N | RESTRICT |
| R10 | `product_reviews.import_record_id` → `import_records.id` | 0..1 | 0..N | RESTRICT |
| R11 | `review_images.product_review_id` → `product_reviews.id` | 1 | 0..N | RESTRICT |
| R12 | `affiliate_clicks.product_id` → `products.id` | 1 | 0..N | RESTRICT |
| R13 | `import_batches.imported_by_admin_id` → `admins.id` | 0..1 | 0..N | RESTRICT |
| R14 | `import_records.import_batch_id` → `import_batches.id` | 1 | 0..N | RESTRICT |
| R15 | `product_import_links.product_id` → `products.id` | 1 | 0..N | RESTRICT |
| R16 | `product_import_links.import_record_id` → `import_records.id` | 1 | 0..1 | RESTRICT |
| R17 | `product_import_links.linked_by_admin_id` → `admins.id` | 0..1 | 0..N | RESTRICT |

## Kamus field lengkap

Jenis dan nullability ditranskripsikan dari approved DATABASE.md. `DATETIME` di sini adalah logical contract; migrations Laravel dan MySQL tests memverifikasi physical representation. Default tidak menggantikan request validation.

### `admins`

Akun internal dan otorisasi. Tidak ada public users. Admin yang direferensikan sejarah dinonaktifkan, bukan dihapus fisik.

| Field | Tipe | Nullability | Makna / constraints |
| --- | --- | --- | --- |
| `id` | BIGINT UNSIGNED | Wajib | Identitas internal; primary key auto increment. |
| `name` | VARCHAR(255) | Wajib | Nama tampilan; bukan classifier atau izin deduplikasi. |
| `email` | VARCHAR(255) | Wajib | Identitas login Admin, unik. |
| `password` | VARCHAR(255) | Wajib | Hash password, tidak menyimpan plaintext. |
| `role` | VARCHAR(50) | Wajib | Role otorisasi; MVP hanya admin. Default: `'admin'`. |
| `is_active` | BOOLEAN | Wajib | Status aktif; lihat invariant khusus Product/Category. Default: `true`. |
| `email_verified_at` | DATETIME | Boleh NULL | Waktu verifikasi email jika tersedia. |
| `remember_token` | VARCHAR(100) | Boleh NULL | Token remember-session Laravel, bersifat rahasia. |
| `created_at`, `updated_at` | DATETIME | Wajib | Waktu pencatatan UTC. Waktu perubahan UTC. |

**Index:** UNIQUE(email); INDEX(is_active, role).

### `categories`

Taxonomy elektronik fleksibel; root/child memakai tabel yang sama. Inactivation dan destroy mengikuti aturan berbeda.

| Field | Tipe | Nullability | Makna / constraints |
| --- | --- | --- | --- |
| `id` | BIGINT UNSIGNED | Wajib | Identitas internal; primary key auto increment. |
| `parent_id` | BIGINT UNSIGNED | Boleh NULL | Parent Category; NULL berarti root; tidak boleh self/cycle. FK → `categories.id`. |
| `name` | VARCHAR(255) | Wajib | Nama tampilan; bukan classifier atau izin deduplikasi. |
| `slug` | VARCHAR(255) | Wajib | Identifier stabil untuk URL; unik termasuk record soft-deleted. |
| `description` | TEXT | Boleh NULL | Keterangan opsional Category. |
| `is_active` | BOOLEAN | Wajib | Status aktif; lihat invariant khusus Product/Category. Default: `true`. |
| `sort_order` | INT UNSIGNED | Wajib | Urutan tampilan, integer nonnegative. Default: `0`. |
| `created_by_admin_id` | BIGINT UNSIGNED | Boleh NULL | Referensi pembuat; bukan riwayat confirmation elektronik. FK → `admins.id`. |
| `created_at`, `updated_at`, `deleted_at` | DATETIME | Hanya deleted_at boleh NULL | Waktu pencatatan UTC. Waktu perubahan UTC. Waktu soft-delete; NULL berarti belum dihapus. |

**Index:** UNIQUE(slug); INDEX(parent_id, is_active, sort_order).

### `products`

Canonical Product. Catalog visibility berbeda dari affiliate CTA eligibility. Normal delete adalah deactivate + soft-delete.

| Field | Tipe | Nullability | Makna / constraints |
| --- | --- | --- | --- |
| `id` | BIGINT UNSIGNED | Wajib | Identitas internal; primary key auto increment. |
| `category_id` | BIGINT UNSIGNED | Boleh NULL | Category opsional pada draft; active Product wajib Category eligible. FK → `categories.id`. |
| `marketplace` | VARCHAR(50) | Wajib | Server menetapkan shopee untuk MVP. Default: `'shopee'`. |
| `name` | VARCHAR(255) | Wajib | Nama tampilan; bukan classifier atau izin deduplikasi. |
| `slug` | VARCHAR(255) | Wajib | Identifier stabil untuk URL; unik termasuk record soft-deleted. |
| `label` | VARCHAR(100) | Boleh NULL | Label tampilan opsional. |
| `store_name` | VARCHAR(255) | Boleh NULL | Nama toko dari sumber/admin yang sah. |
| `store_url` | VARCHAR(2048) | Boleh NULL | URL toko, marketplace-purpose HTTPS allowlist. |
| `description_html` | LONGTEXT | Boleh NULL | Hanya sanitized approved HTML server-side. |
| `description_text` | LONGTEXT | Boleh NULL | Plain text turunan server, internal dan tidak searchable. |
| `price_amount` | DECIMAL(15,2) | Boleh NULL | Harga opsional >=0; tidak diisi dari scraping. |
| `price_currency` | CHAR(3) | Boleh NULL | Kode currency; required jika amount tersedia, approved source. |
| `shopee_rating` | DECIMAL(3,2) | Boleh NULL | Aggregate Shopee rating nullable 0.00–5.00; bukan rata-rata sampel review. |
| `shopee_review_count` | INT UNSIGNED | Boleh NULL | Aggregate review count dari approved source; nonnegative. |
| `review_video_url` | VARCHAR(2048) | Boleh NULL | URL video/media opsional, purpose allowlist. |
| `affiliate_url` | VARCHAR(2048) | Boleh NULL | Candidate/approved target; CTA hanya saat approval lengkap. |
| `affiliate_url_status` | ENUM(`pending`,`approved`,`rejected`) | Wajib | Status approval affiliate, terpisah dari Product aktif. Default: `pending`. |
| `affiliate_url_provenance` | ENUM(`import_candidate`,`internal_manual`,`approved_other`) | Boleh NULL | Asal URL; import_candidate bukan approval. |
| `affiliate_url_source_field` | VARCHAR(64) | Boleh NULL | Field sumber; LINKORDER untuk candidate CSV yang disetujui. |
| `affiliate_url_approved_by_admin_id` | BIGINT UNSIGNED | Boleh NULL | Admin yang memberi approval affiliate melalui workflow khusus. FK → `admins.id`. |
| `affiliate_url_approved_at` | DATETIME | Boleh NULL | Waktu approval affiliate, atomik dengan status/provenance. |
| `is_active` | BOOLEAN | Wajib | Status aktif; lihat invariant khusus Product/Category. Default: `false`. |
| `published_at` | DATETIME | Boleh NULL | Waktu publikasi opsional; tidak diisi otomatis pada task manual Product. |
| `created_at`, `updated_at`, `deleted_at` | DATETIME | Hanya deleted_at boleh NULL | Waktu pencatatan UTC. Waktu perubahan UTC. Waktu soft-delete; NULL berarti belum dihapus. |

**Index:** UNIQUE(slug); INDEX(is_active, marketplace, category_id, published_at); INDEX(name); INDEX(affiliate_url_status, is_active).

### `product_images`

Gambar external Product, dengan provenance nullable untuk data manual.

| Field | Tipe | Nullability | Makna / constraints |
| --- | --- | --- | --- |
| `id` | BIGINT UNSIGNED | Wajib | Identitas internal; primary key auto increment. |
| `product_id` | BIGINT UNSIGNED | Wajib | Parent Product; FK wajib menjaga sejarah. FK → `products.id`. |
| `import_record_id` | BIGINT UNSIGNED | Boleh NULL | Sumber raw row; nullable pada child manual, required pada link provenance. FK → `import_records.id`. |
| `image_url` | VARCHAR(2048) | Wajib | External image URL tervalidasi; bukan upload/blob. |
| `alt_text` | VARCHAR(255) | Boleh NULL | Teks alternatif gambar. |
| `sort_order` | INT UNSIGNED | Wajib | Urutan tampilan, integer nonnegative. Default: `0`. |
| `created_at`, `updated_at` | DATETIME | Wajib | Waktu pencatatan UTC. Waktu perubahan UTC. |

**Index:** UNIQUE(product_id, image_url); INDEX(product_id, sort_order); INDEX(import_record_id).

`image_url` memakai CHARACTER SET ascii COLLATE ascii_bin dalam physical MySQL untuk composite UNIQUE 2048-byte URL. Normalisasi harus mempertahankan URL valid; jangan truncate atau mengganti encoding/index diam-diam. Perbandingan URL pada index ini case-sensitive.

### `product_specifications`

Spesifikasi terstruktur name/value dari Admin atau approved transformation.

| Field | Tipe | Nullability | Makna / constraints |
| --- | --- | --- | --- |
| `id` | BIGINT UNSIGNED | Wajib | Identitas internal; primary key auto increment. |
| `product_id` | BIGINT UNSIGNED | Wajib | Parent Product; FK wajib menjaga sejarah. FK → `products.id`. |
| `import_record_id` | BIGINT UNSIGNED | Boleh NULL | Sumber raw row; nullable pada child manual, required pada link provenance. FK → `import_records.id`. |
| `name` | VARCHAR(255) | Wajib | Nama tampilan; bukan classifier atau izin deduplikasi. |
| `value` | TEXT | Wajib | Nilai spesifikasi; bukan HTML trusted. |
| `sort_order` | INT UNSIGNED | Wajib | Urutan tampilan, integer nonnegative. Default: `0`. |
| `created_at`, `updated_at` | DATETIME | Wajib | Waktu pencatatan UTC. Waktu perubahan UTC. |

**Index:** UNIQUE(product_id, name); INDEX(product_id, sort_order); INDEX(import_record_id).

### `product_reviews`

Referensi review asal Shopee; tidak ada internal review submission. Hide memakai is_visible, tanpa deleted_at.

| Field | Tipe | Nullability | Makna / constraints |
| --- | --- | --- | --- |
| `id` | BIGINT UNSIGNED | Wajib | Identitas internal; primary key auto increment. |
| `product_id` | BIGINT UNSIGNED | Wajib | Parent Product; FK wajib menjaga sejarah. FK → `products.id`. |
| `import_record_id` | BIGINT UNSIGNED | Boleh NULL | Sumber raw row; nullable pada child manual, required pada link provenance. FK → `import_records.id`. |
| `provenance` | ENUM(`shopee_import`,`shopee_manual_reference`) | Wajib | Asal review Shopee yang disetujui. |
| `reviewer_name` | VARCHAR(255) | Boleh NULL | Identitas reviewer jika tersedia; bukan akun aplikasi. |
| `avatar_url` | VARCHAR(2048) | Boleh NULL | External avatar URL; image-purpose allowlist. |
| `content` | TEXT | Boleh NULL | Isi referensi review yang tersedia. |
| `rating_value` | DECIMAL(2,1) | Boleh NULL | Rating individual review nullable 1–5; DECIMAL(2,1), bukan aggregate. |
| `reviewed_at` | DATETIME | Boleh NULL | Tanggal review dari sumber jika tersedia. |
| `source_reference` | VARCHAR(255) | Boleh NULL | Identifier referensi approved source jika tersedia. |
| `is_visible` | BOOLEAN | Wajib | Visibility review; hide menyetel false, data tidak dihapus. Default: `false`. |
| `created_at`, `updated_at` | DATETIME | Wajib | Waktu pencatatan UTC. Waktu perubahan UTC. |

**Index:** INDEX(product_id, is_visible, reviewed_at); INDEX(import_record_id); source_reference index opsional, tidak wajib unik.

### `review_images`

Foto eksternal review jika tersedia.

| Field | Tipe | Nullability | Makna / constraints |
| --- | --- | --- | --- |
| `id` | BIGINT UNSIGNED | Wajib | Identitas internal; primary key auto increment. |
| `product_review_id` | BIGINT UNSIGNED | Wajib | Parent review untuk foto referensi. FK → `product_reviews.id`. |
| `image_url` | VARCHAR(2048) | Wajib | External image URL tervalidasi; bukan upload/blob. |
| `sort_order` | INT UNSIGNED | Wajib | Urutan tampilan, integer nonnegative. Default: `0`. |
| `created_at`, `updated_at` | DATETIME | Wajib | Waktu pencatatan UTC. Waktu perubahan UTC. |

**Index:** UNIQUE(product_review_id, image_url); INDEX(product_review_id, sort_order).

`image_url` memakai CHARACTER SET ascii COLLATE ascii_bin dalam physical MySQL untuk composite UNIQUE 2048-byte URL. Normalisasi harus mempertahankan URL valid; jangan truncate atau mengganti encoding/index diam-diam. Perbandingan URL pada index ini case-sensitive.

### `affiliate_clicks`

Audit append-only minimal. Tidak ada updated_at, IP, user agent, atau public account.

| Field | Tipe | Nullability | Makna / constraints |
| --- | --- | --- | --- |
| `id` | BIGINT UNSIGNED | Wajib | Identitas internal; primary key auto increment. |
| `product_id` | BIGINT UNSIGNED | Wajib | Parent Product; FK wajib menjaga sejarah. FK → `products.id`. |
| `clicked_at` | DATETIME(6) | Wajib | Waktu klik UTC dengan precision mikrodetik. |
| `target_url_snapshot` | VARCHAR(2048) | Wajib | URL approved persis yang dipakai redirect setelah commit; immutable. |
| `created_at` | DATETIME(6) | Wajib | Waktu pencatatan UTC. |

**Index:** INDEX(product_id, clicked_at); INDEX(clicked_at).

### `import_batches`

Registrasi immutable sumber file. Raw bytes tetap disimpan pada protected storage.

| Field | Tipe | Nullability | Makna / constraints |
| --- | --- | --- | --- |
| `id` | BIGINT UNSIGNED | Wajib | Identitas internal; primary key auto increment. |
| `source_name` | VARCHAR(255) | Wajib | Nama/label sumber CSV. |
| `source_filename` | VARCHAR(512) | Wajib | Nama file sumber asli. |
| `source_checksum_sha256` | CHAR(64) | Wajib | SHA-256 authoritative dari bytes file server; immutable dan unik per batch. |
| `source_storage_reference` | VARCHAR(1024) | Boleh NULL | Referensi raw file di protected storage; bukan credential. |
| `column_count` | SMALLINT UNSIGNED | Wajib | Jumlah kolom; sumber mentor saat ini 54. |
| `row_count` | INT UNSIGNED | Wajib | Jumlah baris; sumber mentor yang dianalisis 101. |
| `imported_at` | DATETIME | Wajib | Waktu registrasi sumber. |
| `imported_by_admin_id` | BIGINT UNSIGNED | Boleh NULL | Admin registrasi batch jika tersedia. FK → `admins.id`. |
| `notes` | TEXT | Boleh NULL | Catatan operator/review; link ambigu memerlukan penjelasan. |
| `created_at` | DATETIME | Wajib | Waktu pencatatan UTC. |

**Index:** UNIQUE(source_checksum_sha256); INDEX(imported_at).

### `import_records`

Raw row evidence; payload/checksum immutable, workflow findings/state terpisah.

| Field | Tipe | Nullability | Makna / constraints |
| --- | --- | --- | --- |
| `id` | BIGINT UNSIGNED | Wajib | Identitas internal; primary key auto increment. |
| `import_batch_id` | BIGINT UNSIGNED | Wajib | Batch asal raw row. FK → `import_batches.id`. |
| `source_row_number` | INT UNSIGNED | Wajib | Nomor fisik baris dalam CSV; unik bersama batch. |
| `source_reference_no` | VARCHAR(100) | Boleh NULL | Nilai asli NO.; tidak dianggap global unique. |
| `source_filename_slug` | VARCHAR(512) | Boleh NULL | Nilai asli FILENAME; bukan identitas Product yang pasti. |
| `raw_payload` | JSON | Wajib | Semua header/nilai row asli, immutable dan tidak publik. |
| `record_checksum_sha256` | CHAR(64) | Wajib | Fingerprint raw row; tidak direwrite saat normalisasi. |
| `validation_status` | ENUM(`pending`,`valid`,`warning`,`invalid`,`skipped`) | Wajib | Data-quality state; terpisah dari matching dan bukan keputusan scope rejection baru. Default: `pending`. |
| `validation_errors` | JSON | Boleh NULL | Temuan terstruktur hasil validasi, bukan perbaikan raw data. |
| `match_status` | ENUM(`unprocessed`,`new`,`exact_source_match`,`candidate_match`,`conflict`,`rejected`) | Wajib | Identity-resolution state; sama nama tidak otomatis match. Default: `unprocessed`. |
| `created_at` | DATETIME | Wajib | Waktu pencatatan UTC. |

**Index:** UNIQUE(import_batch_id, source_row_number); INDEX(import_batch_id, validation_status); INDEX(import_batch_id, match_status); INDEX(source_reference_no); INDEX(source_filename_slug); INDEX(record_checksum_sha256).

### `product_import_links`

Jembatan provenance: satu raw row maksimal satu Product, satu Product boleh memiliki banyak sumber approved.

| Field | Tipe | Nullability | Makna / constraints |
| --- | --- | --- | --- |
| `id` | BIGINT UNSIGNED | Wajib | Identitas internal; primary key auto increment. |
| `product_id` | BIGINT UNSIGNED | Wajib | Parent Product; FK wajib menjaga sejarah. FK → `products.id`. |
| `import_record_id` | BIGINT UNSIGNED | Wajib | Sumber raw row; nullable pada child manual, required pada link provenance. FK → `import_records.id`. |
| `link_method` | ENUM(`created_from_record`,`manual_approved_match`,`approved_source_key_match`) | Wajib | Metode linking yang approved, tidak pernah name-only auto match. |
| `linked_by_admin_id` | BIGINT UNSIGNED | Boleh NULL | Actor linking; wajib untuk manual/source-key matching pada validasi aplikasi. FK → `admins.id`. |
| `linked_at` | DATETIME | Wajib | Waktu approval linking. |
| `notes` | TEXT | Boleh NULL | Catatan operator/review; link ambigu memerlukan penjelasan. |
| `created_at` | DATETIME | Wajib | Waktu pencatatan UTC. |

**Index:** UNIQUE(import_record_id); UNIQUE(product_id, import_record_id); INDEX(product_id, linked_at).

## Constraint dan lifecycle lintas tabel

- `price_amount >= 0` saat tersedia; `price_currency` required bila amount non-null. `shopee_rating` nullable 0.00–5.00 dan `shopee_review_count` nonnegative. `rating_value` individual nullable 1–5; DECIMAL(2,1) tidak boleh didokumentasikan sebagai integer-only tanpa keputusan baru.
- Active Product memerlukan Category existing/active/non-soft-deleted; FK saja tidak menjamin status. Setiap manual resulting-active request memerlukan `electronics_scope_confirmed` request-only. Tidak ada field `is_electronic`, `product_domain`, electronics enum, confirmation actor/time, atau audit table.
- Affiliate approved state memerlukan URL valid dan provenance/source/approver/time konsisten melalui dedicated workflow. Inactive/draft berbeda dari pending/rejected affiliate status. `import_candidate` tidak otomatis approved.
- Product destroy deactivate + soft-delete dan mempertahankan media/review/click/provenance. Category inactivation menolak active non-deleted references; Category destroy mempertahankan controlled child/Product restrictions. Review destroy hanya hide. Admin bersejarah deactivate.
- Click history append-only: snapshot tersimpan = exact target redirect setelah commit. Tidak menghapus/mengedit click melalui management. Raw payload, raw bytes dan checksums immutable; validation/matching findings tidak menjadi izin memperbaiki sumber.
- Satu `import_record_id` maksimal satu `product_import_links` row. Beberapa source rows hanya boleh dikaitkan ke Product sama setelah approved review. `linked_by_admin_id` wajib untuk manual/source-key match dan notes wajib bila ambiguity manual. Nama Product sama tidak memberi izin merge.

## Mapping sumber CSV

Sumber mentor yang dianalisis memiliki 101 row dan 54 kolom; angka ini profil sumber, bukan schema database. Pipeline normalization **tetap gated** sampai imported-row electronics eligibility diputuskan.

| Sumber | Target sesudah normalization disetujui | Aturan |
| --- | --- | --- |
| NO., FILENAME | `source_reference_no`, `source_filename_slug` | Metadata asli, bukan identitas unik Product. |
| NAMAPRODUK | `products.name` | Required candidate; tidak name-only merge. |
| NAMATOKO, LINKTOKO | `store_name`, `store_url` | Nullable, URL wajib approved purpose. |
| LINKORDER | `affiliate_url` candidate | `import_candidate`, source LINKORDER; separate Admin approval sebelum CTA. |
| DESKRIPSIPRODUK | `description_html`, derived `description_text` | Sanitize sebelum normalized storage; raw tetap utuh. |
| FOTO1–3 | `product_images.image_url` | Provenance import_record_id dipertahankan. |
| AVATAR1–3, REVIEWER1–3, ULASAN1–3 | Avatar/name/content referensi review | Source Shopee required; tidak mengarang rating/tanggal. |
| JUDUL, DESKRIPSI, TAUTAN, KEYWORD1–3, SECTION1–2, TITLE1–9, LINK1–9, IMG1–9 | `raw_payload` saja | Bukan CTA/search/structured-fact sources; tidak dinormalisasi di MVP. |

Harga, Category/subcategory, aggregate rating/count, individual rating/tanggal/foto review dan video tidak memiliki kolom terstruktur yang memadai dalam sumber saat ini. Tetap NULL atau dipasok approved source/Admin; tidak scraping. Category NULL hanya mendukung draft, bukan aktivasi uncategorized.

Referensi exact approved schema dan seluruh constraint: [DATABASE.md](referensi/docs/DATABASE.md). Semua field di atas tercakup dalam snapshot; tidak ada migration yang dibuat/dijalankan oleh paket ini.
