<USER_REQUEST>
tolong buatkan project di C:\xampp\htdocs\laravel12\afiliate imersa\laravel dengan judul imersa affiliate dengan prompt ini 0. Konteks Proyek (wajib dibaca sebelum mulai)
Sistem: Katalog produk elektronik berbasis affiliate milik PT Imersa Solusi Teknologi. Visitor browse → klik CTA → redirect ke Shopee. Server tidak menjual langsung, tidak memproxy media, tidak scraping data.
Stack wajib: Laravel monolith + Blade + Vite, MySQL 8.x/InnoDB. Tidak butuh microservices/React/Redis/queue/Docker untuk MVP.
Sumber kebenaran: referensi/docs/ARCHITECTURE.md, API_CONTRACT.md, DATABASE.md, BUSINESS_RULES.md di dalam paket. Dokumen 01–09 adalah ringkasan yang harus konsisten dengannya.
Status saat ini: TASK-013 (Product Management) berstatus WIP fase A, dijeda. Modul import CSV di-gate — jangan bangun endpoint normalisasi (admin.imports.records.normalize) sampai ada keputusan Product Owner (lihat OQ-01 di bagian 6).
Prinsip inti yang tidak boleh dilanggar:
products.is_active (visibilitas katalog) terpisah total dari affiliate_url_status (kelayakan tombol CTA). Produk bisa aktif tampil tanpa CTA jika affiliate URL belum approved.
Produk aktif wajib punya Category yang aktif & tidak soft-deleted, plus electronics_scope_confirmed=accepted dikirim di request (field ini tidak disimpan, hanya gate saat transisi ke aktif).
Semua field yang server-owned (status approval, provenance, actor, timestamp) tidak boleh bisa diubah lewat form edit produk generik — harus lewat action/endpoint terpisah dengan policy sendiri.
Delete = soft-delete + deactivate. Riwayat (klik, review, provenance) tidak pernah dihapus fisik.
Klik afiliasi dicatat (commit ke DB) sebelum redirect terjadi, bukan sesudah.
1. Pemetaan Layar UI/UX → Kebutuhan Backend
1.1 Beranda (/)

Yang terlihat di UI: hero, filter kategori cepat, grid "Produk Unggulan", section statis "Kenapa Memilih Kami", footer dengan daftar kategori.

Backend yang harus dibangun:

Route GET / → HomeController + CatalogService. Query hanya produk is_active = true, non-soft-deleted.
Endpoint/section "Produk Unggulan" belum punya field featured resmi (lihat OQ-06). Jangan diam-diam menambah kolom is_featured — tanyakan dulu atau gunakan urutan sementara berbasis published_at/terbaru sampai PO memutuskan mekanisme seleksi.
Kategori pada sidebar filter diambil dari categories yang is_active = true, urutkan pakai sort_order.
Semua kartu produk hanya menampilkan field yang memang publik: name, label, price_amount/price_currency (nullable-safe), shopee_rating, gambar utama (product_images urut sort_order). Jangan expose field internal (affiliate_url_status, description_text, dsb).
1.2 Katalog Produk (/catalog)

Yang terlihat di UI: search bar nama, filter kategori (Laptop, Smartphone, Aksesoris), grid produk dengan pagination tersirat, badge status ("Segera Hadir" / "Stok Terbatas" mirip graceful degradation saat data kosong).

Backend yang harus dibangun:

Route GET /catalog → CatalogController / CatalogQueryService, paginated, name search (LIKE/full-text sesuai kapasitas) + filter category_id.
Perlu keputusan (OQ-07): apakah filter kategori induk otomatis mencakup semua subkategori (descendants)? Implementasikan sesuai keputusan PO, jangan menebak — default sementara: hanya exact match category_id, catat sebagai asumsi di PR.
Perlu keputusan (OQ-04): page size dan response saat hasil kosong/di luar rentang halaman. Sementara ikuti standar Admin (15/halaman) kecuali PO menentukan lain untuk publik.
Graceful degradation di UI (kartu produk tanpa gambar/harga) → backend harus mengembalikan null apa adanya, bukan placeholder palsu; fallback visual jadi tanggung jawab Blade/view, bukan data dummy dari server.
1.3 Detail Produk (/products/{slug})

Yang terlihat di UI: galeri gambar, badge kategori, nama, rating + jumlah review, harga, spesifikasi, deskripsi, ulasan (avatar, nama, rating, isi), tombol "Beli Sekarang di Shopee".

Backend yang harus dibangun:

Route GET /products/{slug} → ProductController / ProductDetailService. Hanya produk aktif; selain itu 404 (bukan redirect diam-diam).
Spesifikasi: ambil dari product_specifications urut sort_order.
Deskripsi: render description_html yang sudah tersanitasi saat disimpan (jangan sanitasi ulang saat render, dan jangan render description_html mentah dari input). description_text tidak pernah dikirim ke view publik sebagai searchable/SEO field.
Review: hanya product_reviews.is_visible = true, dengan review_images terkait. Rating agregat (shopee_rating, shopee_review_count) di header produk bukan hasil AVG() dari tabel product_reviews — itu field approved terpisah dari Product.
Tombol "Beli Sekarang di Shopee" hanya tampil aktif jika affiliate_url_status = approved. Jika pending/rejected, tombol harus disabled atau disembunyikan — backend wajib mengirim status ini ke view, jangan biarkan Blade menebak dari ada/tidaknya affiliate_url.
Klik tombol → bukan link langsung ke Shopee, tapi POST /go/{product} (lihat 1.6).
1.4 Admin Login (/admin/login)

Yang terlihat di UI: form username/password sederhana, logo, tombol "Masuk ke Dashboard".

Backend yang harus dibangun:

GET/POST /admin/login dengan guard Admin khusus (terpisah dari guard publik — memang tidak ada akun publik di sistem ini).
Session regeneration setelah login sukses (cegah session fixation).
Login gagal → tetap di form dengan error umum (jangan bocorkan "email tidak ditemukan" vs "password salah" secara spesifik — cegah user enumeration).
Rate limiting/login throttling — dicatat di dokumen sebagai hardening yang perlu direview, sebaiknya diimplementasikan meski belum wajib kontrak.
POST /admin/logout harus invalidate + regenerate session, dan admin yang jadi is_active = false harus otomatis ter-logout di request berikutnya (cek middleware).
1.5 Dashboard Admin (/admin/dashboard)

Yang terlihat di UI: KPI cards (Pengunjung, CTR, Produk Terjual, Konversi), grafik statistik klik & konversi dengan filter periode (harian/mingguan/bulanan/tahunan), tabel "Total Klik Ditayangkan"/"Produk Terpopuler", tabel "Manajemen Produk Terkini".

Backend yang harus dibangun:

Route GET /admin/dashboard → DashboardMetricsService. Angka nol harus valid (bukan error/null saat data kosong) — tampilkan 0, bukan gagal render.
Sumber data KPI hanya dari affiliate_clicks (klik tercatat) dan products — sistem ini tidak punya data "pengunjung unik"/"konversi jual" riil karena transaksi terjadi di Shopee, bukan di aplikasi ini. Ini butuh klarifikasi ke PO: istilah "Pengunjung", "CTR", "Produk Terjual", "Konversi" di mockup UI kemungkinan besar melebihi data yang tersedia di skema (affiliate_clicks cuma product_id, clicked_at, target_url_snapshot). Rekomendasi:
"Pengunjung" → butuh page-view tracking tambahan (belum ada di skema) → tanyakan apakah perlu tabel baru atau cukup estimasi dari klik.
"CTR"/"Konversi" → hanya bisa dihitung sebagai klik / produk aktif ditampilkan, bukan konversi transaksi nyata (Shopee tidak mengirim data balik).
"Produk Terjual" → tidak ada sumber data ini di skema sekarang. Jangan buat angka palsu; tampilkan sebagai "Klik ke Shopee" atau field kosong sampai ada sumber data yang jelas.
Grafik periode → sesuai admin.analytics.index, max rentang 366 hari (lihat 1.7).
Tabel "Manajemen Produk Terkini" → query products terbaru diupdate, join categories, hitung affiliate_clicks count per produk.
1.6 Manajemen Produk Admin (/admin/products)

Yang terlihat di UI: tabel produk (nama, kategori, status badge, total klik, ikon edit/hapus), tombol "+ Tambah Produk", modal "Tambah/Edit Produk" dengan field: nama produk, kategori, harga, rating, link afiliasi, deskripsi, upload gambar.

Backend yang harus dibangun (paling kritikal):

GET /admin/products — list per status/kategori/halaman, 15 item per halaman (sesuai technical review TASK-013).
POST /admin/products → CreateProductAction, atomik: Product + images + specifications dalam satu transaksi.
PUT /admin/products/{product} → UpdateProductAction. Wajib jaga invariant:
is_active harus dikirim eksplisit (boolean) — omit = ditolak, bukan default diam-diam.
Jika hasil akhirnya is_active = true: kategori wajib existing + aktif + tidak soft-deleted, dan request harus menyertakan electronics_scope_confirmed = "accepted" (field ini tidak pernah disimpan ke DB, hanya validasi gate).
Field yang wajib diblokir dari mass-assignment via form ini: affiliate_url_status, affiliate_url_provenance, affiliate_url_source_field, affiliate_url_approved_by_admin_id, affiliate_url_approved_at, semua timestamp server-owned, dan import-provenance ID. Field-field ini hanya bisa diubah lewat endpoint approval terpisah (lihat di bawah).
slug unik (abaikan ID produk sendiri saat cek unique pada update), name boleh duplikat.
price_amount nonnegative DECIMAL(15,2); jika diisi, price_currency wajib (CHAR(3)) — jangan tebak default currency (OQ-08 masih open, tunggu daftar kode currency approved dari PO).
Upload gambar di UI sebenarnya adalah input URL eksternal tervalidasi, bukan file upload/blob — field product_images.image_url divalidasi lewat allowlist host image (lihat bagian 3). Sesuaikan UI copy "Upload Gambar Produk" dengan realita backend (input link, bukan file binary) — atau, kalau PO memang mau file upload asli, ini perubahan scope yang harus dicatat lewat FORM_KEPUTUSAN.md, bukan diasumsikan.
store_url, review_video_url juga divalidasi via allowlist purpose masing-masing (HTTPS-only, exact host, cek resolusi IP publik — anti-SSRF).
"Link afiliasi" di form → mengisi affiliate_url sebagai candidate, status otomatis pending. Approval terpisah lewat POST /admin/products/{product}/affiliate-approval dengan policy approveAffiliate sendiri — biasanya perlu peran/langkah admin kedua, jangan gabung ke form create/edit biasa (mencegah "approval injection" lewat form generik — ini ancaman yang eksplisit dicatat di dokumen keamanan).
"Rating" di form UI perlu diklarifikasi: apakah ini shopee_rating (agregat, di-set manual dari sumber approved) atau input rating individual review? Berdasarkan skema, ini field agregat manual, bukan hasil hitung dari tabel review.
DELETE /admin/products/{product} → DeactivateProductAction: soft-delete + is_active=false, riwayat (review/klik/gambar) tetap utuh, bukan hard delete.
Semua write butuh CSRF token, dan pakai pola Post/Redirect/Get dengan flash message sukses; validasi gagal balik ke form dengan old-input aman (ter-escape) dan pesan error per field.
Concurrency: saat reassignment kategori, lock Category lama & baru berurutan by ID sebelum lock Product, lalu re-check state setelah lock (cegah race condition data ganda/invalid).
1.7 Manajemen Kategori Admin (/admin/categories)

Yang terlihat di UI: daftar kategori (nama, kategori parent, status, jumlah produk, ikon edit/hapus), visual "Struktur Kategori Saat Ini", modal "Tambah Kategori" (nama, deskripsi, kategori induk opsional).

Backend yang harus dibangun:

GET /admin/categories, POST /admin/categories → CreateCategoryAction.
PUT /admin/categories/{category} → UpdateCategoryAction — wajib cegah self-reference dan cycle (kategori tidak boleh jadi parent dari dirinya sendiri atau leluhurnya sendiri, walau tidak langsung). Ini validasi eksplisit yang harus ada test-nya.
DELETE /admin/categories/{category} → DeactivateCategoryAction:
Inactivation ditolak jika masih ada produk aktif & non-deleted yang mereferensikan kategori ini.
Destroy tetap menjaga referensi controlled child/produk (tidak sembarang cascade delete).
Jumlah produk per kategori (ditampilkan di UI) → hitung dari products aktif non-deleted per category_id, jangan simpan sebagai counter cache kecuali ada alasan performa yang jelas (hindari data yang bisa out-of-sync).
Isi taxonomy final (daftar kategori/subkategori resmi) masih OQ-05 (non-blocking, later) — backend harus tetap generic/fleksibel, bukan hardcode daftar kategori tertentu.
1.8 Analytics Admin (/admin/analytics) — pelengkap Dashboard
GET /admin/analytics: total klik, per-produk, produk terpopuler, video count, filter periode maksimal 366 hari (batas hard di kontrak).
Timezone presentasi laporan masih OQ-10 (open) — storage tetap UTC, tapi tampilan ke Admin (WIB dsb.) perlu keputusan PO sebelum M10. Jangan asumsikan WIB tanpa konfirmasi.
2. Field yang HARUS Diblokir dari Input Client (Mass-Assignment Guard)

Terapkan whitelist eksplisit di setiap FormRequest — field berikut tidak boleh diubah lewat form generik apa pun, hanya lewat action/endpoint khusus:

affiliate_url_status, affiliate_url_provenance, affiliate_url_source_field, affiliate_url_approved_by_admin_id, affiliate_url_approved_at
import_record_id dan semua field provenance import lainnya
marketplace (server-controlled "shopee", bukan input client)
published_at saat create (dipertahankan apa adanya saat update — bukan mekanisme scheduling)
Semua created_at/updated_at/deleted_at
Target redirect afiliasi arbitrary (redirect hanya lewat Product ID internal, bukan URL bebas dari client)

Tulis test otomatis yang membuktikan field-field ini tidak berubah meski dikirim di payload request oleh actor jahat.

3. Validasi URL & Media (berlaku di semua form yang berisi link/gambar)
Allowlist host per-purpose (marketplace vs image vs video), disimpan di configuration, bukan tabel/enum database. Default kosong = tolak semua.
Wajib HTTPS, port 443, tanpa userinfo (user:pass@host), semua resolved IP (v4 & v6) harus alamat publik (tolak IP privat/loopback/link-local — cegah SSRF ke jaringan internal).
Normalisasi host/IDN pakai library standar, bukan regex buatan sendiri.
Panjang URL input & hasil ASCII maksimal 2048 byte.
Tidak ada auto-upgrade HTTP→HTTPS diam-diam — tolak langsung jika bukan HTTPS.
Lakukan validasi DNS sebelum lock database (jangan validasi di tengah transaksi yang sudah lock row).
4. Sanitasi HTML Deskripsi Produk
Pakai symfony/html-sanitizer:^7.4 (parser-based, bukan regex).
Allowed tags saja: p, br, strong, b, em, i, ul, ol, li, h2, h3, h4, blockquote. Tidak ada attributes, tidak ada link aktif.
Input mentah maksimal 100.000 byte UTF-8 sebelum sanitasi — oversize ditolak, bukan dipotong (truncate).
Sanitizer gagal → seluruh save gagal (tidak ada fallback simpan raw).
description_text diturunkan otomatis di server dari hasil sanitasi, untuk keperluan internal saja (bukan field pencarian).
5. Checklist Test Wajib (turunan dari dokumen keamanan §8)
 Admin nonaktif otomatis ter-logout / ditolak akses di request berikutnya.
 Deskripsi dengan payload XSS (script tag, event handler inline) ter-strip bersih.
 URL mengarah ke IP internal (127.0.0.1, 10.x, 169.254.x) ditolak walau host-nya terdaftar di allowlist.
 Approval afiliasi tidak bisa di-trigger lewat endpoint edit produk biasa.
 Dua request update produk bersamaan (race condition, 2 koneksi DB + barrier) tidak menghasilkan data korup — salah satu menang, invariant tetap valid setelah commit.
 Hapus produk/kategori tidak menghapus riwayat klik/review/provenance secara fisik.
 Payload sanitizer/URL yang oversize ditolak, bukan silent-truncate.
 Tabel affiliate_clicks tidak pernah menerima IP/user-agent meski ditambahkan di request (guard di level Action, bukan cuma di Form Request).
6. Keputusan yang WAJIB Diklarifikasi ke Product Owner Sebelum Coding Fitur Terkait
Fitur di UI/UX	Yang perlu dikonfirmasi	Dampak jika diabaikan
"Produk Unggulan" di Beranda	Mekanisme seleksi produk unggulan (manual pick, terbaru, rating tertinggi?)	Bisa jadi fitur "abu-abu" yang menambah field diam-diam (dilarang dokumen)
KPI "Pengunjung", "Konversi", "Produk Terjual" di Dashboard	Sumber data riil — skema saat ini tidak punya page-view atau data transaksi Shopee	Berisiko menampilkan angka palsu/menyesatkan ke Admin
Upload gambar produk (form Admin)	Apakah benar-benar file upload, atau input URL eksternal (sesuai skema saat ini)?	Mismatch UI vs backend — user akan kebingungan saat form tidak menerima file
Filter kategori di Katalog	Apakah pilih kategori induk otomatis mencakup subkategori?	Hasil pencarian bisa salah/kurang lengkap
Field "Rating" di form Tambah/Edit Produk	Rating agregat manual (shopee_rating) vs rating individual review	Salah simpan ke field yang salah
Kode mata uang (currency)	Daftar kode ISO yang di-approve, format tampilan harga	Tidak bisa validasi price_currency dengan benar
Timezone laporan Analytics	WIB atau UTC yang ditampilkan ke Admin	Laporan bisa salah baca oleh Admin
Modul Import CSV	Kriteria kelayakan "electronics" untuk baris hasil impor (OQ-01)	Blocker — jangan bangun endpoint normalize sebelum ini selesai
7. Definition of Done per Fitur

Setiap fitur backend dianggap selesai jika:

Route + Controller + FormRequest + Policy (jika perlu) + Action/Service sesuai nama di kontrak dokumen 04.
Field yang diblokir di bagian 2 terbukti tidak bisa diubah (ada test).
Transaksi atomik untuk operasi multi-tabel (produk+gambar+spek dalam satu commit).
Response error mengikuti pola: validasi → balik ke form dengan old-input aman; unauthorized → redirect login; policy denial → 403; not found → 404; DB failure → 5xx generik tanpa detail internal.
Tampilan di Blade match 1:1 dengan mockup UI yang sudah disetujui — termasuk state kosong/null (graceful degradation), bukan hanya "happy path" data lengkap.
</USER_REQUEST>
<ADDITIONAL_METADATA>
The current local time is: 2026-09-25T16:39:08+07:00.
</ADDITIONAL_METADATA>