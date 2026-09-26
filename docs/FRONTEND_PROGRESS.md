# Frontend Imersa — preview awal

Tanggal: 24 September 2026. Acuan kebutuhan: PRD versi 1.0 yang diberikan pengguna. Referensi visual: `ui_ux/imersa solusi.pdf` pada checkout utama. File referensi asli tidak diubah.

## Lokasi dan cara menjalankan

Branch `codex/frontend-prd` berada di checkout `.worktrees/frontend-prd` karena checkout utama `main` masih memiliki merge yang belum ditutup. Jangan menganggap file preview sudah berada di `main`.

Jalankan dari checkout frontend:

```powershell
composer install
npm ci
npm run build
php artisan serve --host=127.0.0.1 --port=8011
```

Gunakan environment lokal dengan key sendiri dan file session/cache. Pada setup baru, salin `.env.example` ke `.env` dan jalankan `php artisan key:generate` sekali. Tidak perlu migrasi database untuk preview. Instalasi lokal sesi ini memakai salinan vendor dan junction node_modules dari checkout utama; instalasi baru bisa mengikuti perintah di atas.

Buka `/preview` untuk publik dan `/preview/admin/dashboard` untuk Admin. Semua route preview hanya didaftarkan pada environment `local`/`testing`, dengan pemeriksaan ulang saat request. Route `/` bawaan tetap tersedia. Route, fixture, serta JavaScript preview tidak menyimpan data, tidak login, tidak memberi persetujuan nyata, dan tidak mengirim pengunjung ke Shopee.

## Cakupan yang tersedia

- Home, kartu kategori, produk pilihan contoh, footer.
- Katalog dengan pencarian nama, kategori/subkategori, pagination, reset dan hasil kosong.
- Detail dengan gambar, informasi/specs, harga/rating nullable, ulasan terlihat saja, dan kondisi CTA tersedia/tidak tersedia.
- Login berbasis email. Password sengaja belum diaktifkan; link dashboard adalah akses ke contoh UI, bukan autentikasi.
- Dashboard: inventaris dan peringkat klik contoh.
- Produk: daftar/filter, halaman create/edit, input URL gambar/video, tambah/hapus baris spesifikasi/gambar, draft/aktif dan checkbox elektronik baru pada setiap percobaan.
- Kategori: hierarki, create/edit, status, dan contoh penolakan karena masih digunakan produk aktif.
- Referensi Shopee pada create/edit produk: nama toko, URL toko, rating Shopee, jumlah ulasan Shopee, URL affiliate. Form affiliate terpisah dihapus sesuai revisi pengguna 25 September 2026.
- Review: daftar, tampil/sembunyi contoh, pengisian/edit form, foto via URL.
- Analytics: tanggal, total/per-product/ranking, grafik dengan tabel alternatif, inventaris video dan kondisi kosong.
- Review impor: contoh batch, escaped raw evidence, matching dan normalisasi yang belum tersedia.
- Layout responsif, navigasi mobile, focus keyboard, dialog native, error summary dan error terkait input.

Semua aksi mutasi hanya memperlihatkan hasil pemeriksaan/simulasi. Tidak ada perubahan list setelah save/delete karena backend belum tersedia. Tanpa JavaScript, POST preview gagal dengan 405 dan tidak menaruh input dalam query URL.

## Revisi terhadap PDF

- Username menjadi email; tidak membuat reset password yang belum ada kontraknya.
- Upload gambar menjadi input URL.
- Form produk penuh menggantikan modal kecil.
- Checkbox elektronik selalu fresh. Input URL affiliate digabung ke Referensi Shopee atas revisi pengguna; tidak ada approval otomatis dari form frontend.
- CTR, konversi dan kesehatan link tanpa sumber dihilangkan.
- Teks jaminan transaksi/harga terbaik diganti penjelasan yang sesuai PRD.
- Akses detail diperjelas melalui nama produk dan tombol Lihat produk.
- Review management, referensi affiliate, impor gated, empty/error states, mobile ditambahkan.

## Keputusan presentasi sementara

- Kategori induk mencakup descendants pada data contoh; sepakati dengan backend sebelum penerimaan final.
- Halaman katalog di luar rentang memakai halaman terakhir yang tersedia pada preview.
- Katalog 24 item/halaman, Admin 15; seed kecil belum menampilkan navigasi multipage.
- IDR merupakan mata uang fixture; daftar currency production belum disepakati.
- Periode contoh analytics UTC dan inklusif kedua tanggal, maksimum 366 hari.
- Produk pilihan Home adalah urutan fixture, bukan aturan ranking atau field featured baru.
- Aset gambar diekstrak dari PDF pengguna untuk preview; bukan sumber produk/verifikasi penawaran production.

## Pekerjaan berikutnya sebelum frontend dinyatakan selesai

- Revisi UI 25 September 2026: pengguna meminta lima field Referensi Shopee dalam satu form produk. URL lama `/preview/admin/products/{id}/affiliate` mengarah ke bagian tersebut. Field asal/sumber dan approve/reject dihapus dari UI. Perubahan ini belum menentukan atau mengimplementasikan mekanisme validasi/persetujuan backend; kontrak input `affiliate_url` perlu disepakati saat integrasi.

- Review visual bersama pengguna, termasuk penyesuaian kedekatan ke PDF.
- Tombol Portal Admin masih tersedia pada navigasi publik. Pemindahan akses ke alamat login khusus sudah direkomendasikan, tetapi belum diterapkan pada checkpoint ini.
- Lengkapi contoh galeri beberapa gambar, video tersedia/gagal, foto ulasan dan keadaan media gagal untuk peninjauan visual.
- Lengkapi contoh keadaan pengiriman form, gagal login, sesi berakhir, akses ditolak, serta validasi server sebelum integrasi.
- Periksa pagination dengan data beberapa halaman dan layout dengan teks panjang.
- Sepakati DTO/nama field collections, child IDs, explicit removals, data import-linked yang tidak boleh diedit, dan flash/server errors.
- Ganti fixture dengan view data backend; pindahkan route preview ke route resmi dengan Admin guard/policy. Jangan memakai preview sebagai akses Admin production.
- Hubungkan POST/PUT/DELETE, validasi server, sanitization, URL allowlist, persetujuan, sesi dan click-before-redirect. Pemeriksaan JavaScript tidak membuktikan aturan keamanan.
- Ganti contoh CTA dengan POST CSRF ke action affiliate. Tidak ada raw target URL dari browser.
- Pastikan sumber media, data produk/review, channel perusahaan dan aturan pilihan Home disetujui.
- UAT terpadu, browser target, aksesibilitas lengkap, dan acceptance tim; impor menunggu keputusan lingkup elektronik.

## Verifikasi sesi

- `npm run build`: berhasil.
- Feature/unit suite: seluruh route preview, nama-only search, draft tersembunyi, data nullable, hidden review, query invalid, escaping, tanpa mutasi, period analytics, dan blokir production.
- Browser: navigasi dan form aktivasi; percobaan tanpa checkbox ditolak, dengan checkbox menampilkan simulasi dan mereset checkbox.
- Viewport kecil: halaman utama publik/Admin diperiksa untuk overflow dan gambar gagal.
- Tidak ada perubahan backend database atau dokumen PRD/PDF asli. Belum ada human UAT, penggabungan ke main atau deployment. Checkpoint frontend disiapkan untuk dikirim ke branch `codex/frontend-prd` atas permintaan pengguna pada 26 September 2026; frontend belum dinyatakan selesai.
