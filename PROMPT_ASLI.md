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

Route GET / → HomeController + CatalogService. Query hanya produk is_active = true, non
<truncated 13332 bytes>
iabaikan
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