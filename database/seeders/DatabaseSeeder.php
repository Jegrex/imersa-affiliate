<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\AffiliateClick;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductReview;
use App\Models\ProductSpecification;
use App\Models\ReviewImage;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Default Admin
        $admin = Admin::updateOrCreate(
            ['email' => 'admin@imersa.co.id'],
            [
                'name' => 'Admin Imersa',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => Carbon::now('UTC'),
            ]
        );

        // 2. Create Electronic Categories
        $laptopCat = Category::updateOrCreate(
            ['slug' => 'laptop-komputer'],
            [
                'name' => 'Laptop & Komputer',
                'description' => 'Katalog perangkat laptop, PC, dan periferal komputasi.',
                'is_active' => true,
                'sort_order' => 1,
                'created_by_admin_id' => $admin->id,
            ]
        );

        $phoneCat = Category::updateOrCreate(
            ['slug' => 'smartphone-tablet'],
            [
                'name' => 'Smartphone & Tablet',
                'description' => 'Smartphone terkini dan tablet canggih untuk produktivitas.',
                'is_active' => true,
                'sort_order' => 2,
                'created_by_admin_id' => $admin->id,
            ]
        );

        $audioCat = Category::updateOrCreate(
            ['slug' => 'audio-speaker'],
            [
                'name' => 'Audio & Headphone',
                'description' => 'Perangkat audio resolusi tinggi, earphone, dan TWS.',
                'is_active' => true,
                'sort_order' => 3,
                'created_by_admin_id' => $admin->id,
            ]
        );

        $accCat = Category::updateOrCreate(
            ['slug' => 'aksesoris-laptop'],
            [
                'name' => 'Aksesoris Laptop',
                'parent_id' => $laptopCat->id,
                'description' => 'Keyboard, mouse, docking, dan perlengkapan laptop.',
                'is_active' => true,
                'sort_order' => 4,
                'created_by_admin_id' => $admin->id,
            ]
        );

        // 3. Create Sample Electronic Products
        $now = Carbon::now('UTC');

        $p1 = Product::updateOrCreate(
            ['slug' => 'asus-zenbook-14-oled'],
            [
                'name' => 'ASUS Zenbook 14 OLED UX3405',
                'category_id' => $laptopCat->id,
                'is_active' => true,
                'label' => 'Rekomendasi Utama',
                'store_name' => 'ASUS Official Store',
                'store_url' => 'https://shopee.co.id/asus.official',
                'description_html' => '<p>ASUS Zenbook 14 OLED adalah laptop ultraportabel premium bertenaga AI dengan prosesor Intel Core Ultra. Dilengkapi layar OLED 3K 120Hz yang memukau dan baterai tahan lama hingga 15 jam.</p><p>Sangat cocok untuk profesional muda, kreator konten, dan mobilitas tinggi.</p>',
                'description_text' => 'ASUS Zenbook 14 OLED adalah laptop ultraportabel premium bertenaga AI dengan prosesor Intel Core Ultra. Dilengkapi layar OLED 3K 120Hz yang memukau dan baterai tahan lama hingga 15 jam. Sangat cocok untuk profesional muda, kreator konten, dan mobilitas tinggi.',
                'price_amount' => 17999000.00,
                'price_currency' => 'IDR',
                'shopee_rating' => 4.90,
                'shopee_review_count' => 342,
                'review_video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'affiliate_url' => 'https://shopee.co.id/universal-link/asus-zenbook-14',
                'affiliate_url_status' => 'approved',
                'affiliate_url_provenance' => 'internal_manual',
                'affiliate_url_approved_by_admin_id' => $admin->id,
                'affiliate_url_approved_at' => $now,
                'published_at' => $now,
                'marketplace' => 'shopee',
            ]
        );

        ProductImage::updateOrCreate(
            ['product_id' => $p1->id, 'sort_order' => 0],
            [
                'image_url' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=800&q=80',
                'alt_text' => 'ASUS Zenbook 14 OLED Tampilan Depan',
            ]
        );
        ProductImage::updateOrCreate(
            ['product_id' => $p1->id, 'sort_order' => 1],
            [
                'image_url' => 'https://images.unsplash.com/photo-1541807084-5c52b6b3adef?auto=format&fit=crop&w=800&q=80',
                'alt_text' => 'ASUS Zenbook 14 OLED Samping',
            ]
        );

        ProductSpecification::updateOrCreate(
            ['product_id' => $p1->id, 'name' => 'Prosesor'],
            ['value' => 'Intel Core Ultra 7 155H with Intel AI Boost NPU', 'sort_order' => 0]
        );
        ProductSpecification::updateOrCreate(
            ['product_id' => $p1->id, 'name' => 'RAM & Storage'],
            ['value' => '32GB LPDDR5X, 1TB M.2 NVMe PCIe 4.0 SSD', 'sort_order' => 1]
        );
        ProductSpecification::updateOrCreate(
            ['product_id' => $p1->id, 'name' => 'Layar'],
            ['value' => '14.0" 3K (2880 x 1800) OLED 16:10 120Hz 500nits HDR', 'sort_order' => 2]
        );

        // Product 2: Smartphone
        $p2 = Product::updateOrCreate(
            ['slug' => 'samsung-galaxy-s24-ultra'],
            [
                'name' => 'Samsung Galaxy S24 Ultra 5G',
                'category_id' => $phoneCat->id,
                'is_active' => true,
                'label' => 'Flagship Terlaris',
                'store_name' => 'Samsung Official Shop',
                'store_url' => 'https://shopee.co.id/samsung.official',
                'description_html' => '<p>Era baru mobile AI hadir bersama Galaxy AI. Dilengkapi frame titanium kokoh, kamera 200MP dengan ProVisual engine, dan layar Dynamic AMOLED 2X flat yang sangat jernih di bawah sinar matahari langsung.</p>',
                'description_text' => 'Era baru mobile AI hadir bersama Galaxy AI. Dilengkapi frame titanium kokoh, kamera 200MP dengan ProVisual engine, dan layar Dynamic AMOLED 2X flat yang sangat jernih di bawah sinar matahari langsung.',
                'price_amount' => 21999000.00,
                'price_currency' => 'IDR',
                'shopee_rating' => 4.95,
                'shopee_review_count' => 1250,
                'review_video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'affiliate_url' => 'https://shopee.co.id/universal-link/samsung-s24-ultra',
                'affiliate_url_status' => 'approved',
                'affiliate_url_provenance' => 'internal_manual',
                'affiliate_url_approved_by_admin_id' => $admin->id,
                'affiliate_url_approved_at' => $now,
                'published_at' => $now,
                'marketplace' => 'shopee',
            ]
        );

        ProductImage::updateOrCreate(
            ['product_id' => $p2->id, 'sort_order' => 0],
            [
                'image_url' => 'https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?auto=format&fit=crop&w=800&q=80',
                'alt_text' => 'Samsung Galaxy S24 Ultra',
            ]
        );

        ProductSpecification::updateOrCreate(
            ['product_id' => $p2->id, 'name' => 'Chipset'],
            ['value' => 'Snapdragon 8 Gen 3 for Galaxy (4nm)', 'sort_order' => 0]
        );
        ProductSpecification::updateOrCreate(
            ['product_id' => $p2->id, 'name' => 'Kamera Utama'],
            ['value' => '200MP OIS + 50MP Periscope Telephoto (5x) + 10MP Telephoto (3x) + 12MP Ultra-wide', 'sort_order' => 1]
        );

        // Product 3: Audio TWS
        $p3 = Product::updateOrCreate(
            ['slug' => 'sony-wf-1000xm5-noise-cancelling'],
            [
                'name' => 'Sony WF-1000XM5 True Wireless Earbuds',
                'category_id' => $audioCat->id,
                'is_active' => true,
                'label' => 'Best Noise Cancelling',
                'store_name' => 'Sony Audio Indonesia',
                'store_url' => 'https://shopee.co.id/sony.indonesia',
                'description_html' => '<p>Earbuds dengan teknologi peredam bising terbaik di kelasnya. Menghadirkan kualitas suara premium yang imersif dan kualitas panggilan telepon yang sangat jernih.</p>',
                'description_text' => 'Earbuds dengan teknologi peredam bising terbaik di kelasnya. Menghadirkan kualitas suara premium yang imersif dan kualitas panggilan telepon yang sangat jernih.',
                'price_amount' => 4399000.00,
                'price_currency' => 'IDR',
                'shopee_rating' => 4.88,
                'shopee_review_count' => 620,
                'review_video_url' => null,
                'affiliate_url' => 'https://shopee.co.id/universal-link/sony-wf1000xm5',
                'affiliate_url_status' => 'approved',
                'affiliate_url_provenance' => 'internal_manual',
                'affiliate_url_approved_by_admin_id' => $admin->id,
                'affiliate_url_approved_at' => $now,
                'published_at' => $now,
                'marketplace' => 'shopee',
            ]
        );

        ProductImage::updateOrCreate(
            ['product_id' => $p3->id, 'sort_order' => 0],
            [
                'image_url' => 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?auto=format&fit=crop&w=800&q=80',
                'alt_text' => 'Sony WF-1000XM5 TWS',
            ]
        );

        ProductSpecification::updateOrCreate(
            ['product_id' => $p3->id, 'name' => 'Fitur Utama'],
            ['value' => 'Integrated Processor V2 & HD Noise Cancelling Processor QN2e', 'sort_order' => 0]
        );

        // Product 4: Draft product (inactive, affiliate pending)
        $p4 = Product::updateOrCreate(
            ['slug' => 'logitech-mx-master-3s-wireless'],
            [
                'name' => 'Logitech MX Master 3S Wireless Performance Mouse',
                'category_id' => $accCat->id,
                'is_active' => false,
                'label' => 'Produk Baru',
                'store_name' => 'Logitech Official Store',
                'store_url' => 'https://shopee.co.id/logitech.official',
                'description_html' => '<p>Mouse produktivitas dengan sensor 8.000 DPI yang dapat bekerja di atas kaca dan tombol Quiet Clicks.</p>',
                'description_text' => 'Mouse produktivitas dengan sensor 8.000 DPI yang dapat bekerja di atas kaca dan tombol Quiet Clicks.',
                'price_amount' => 1649000.00,
                'price_currency' => 'IDR',
                'shopee_rating' => 4.90,
                'shopee_review_count' => 840,
                'review_video_url' => null,
                'affiliate_url' => 'https://shopee.co.id/universal-link/logitech-mx-master-3s',
                'affiliate_url_status' => 'pending',
                'affiliate_url_provenance' => 'internal_manual',
                'affiliate_url_approved_by_admin_id' => null,
                'affiliate_url_approved_at' => null,
                'published_at' => null,
                'marketplace' => 'shopee',
            ]
        );

        ProductImage::updateOrCreate(
            ['product_id' => $p4->id, 'sort_order' => 0],
            [
                'image_url' => 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?auto=format&fit=crop&w=800&q=80',
                'alt_text' => 'Logitech MX Master 3S Mouse',
            ]
        );

        // 4. Sample Reviews
        $r1 = ProductReview::updateOrCreate(
            ['product_id' => $p1->id, 'reviewer_name' => 'Budi Santoso'],
            [
                'provenance' => 'shopee_manual_reference',
                'avatar_url' => 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=150&q=80',
                'content' => 'Layar OLED-nya juara banget! Baterai awet dipakai ngoding dan rendering seharian tidak drop. Rekomendasi buat developer.',
                'rating_value' => 5,
                'reviewed_at' => $now->copy()->subDays(3),
                'source_reference' => 'Review Shopee Verified',
                'is_visible' => true,
            ]
        );

        ReviewImage::updateOrCreate(
            ['product_review_id' => $r1->id, 'sort_order' => 0],
            [
                'image_url' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=400&q=80',
            ]
        );

        ProductReview::updateOrCreate(
            ['product_id' => $p2->id, 'reviewer_name' => 'Rina Wijaya'],
            [
                'provenance' => 'shopee_manual_reference',
                'avatar_url' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=150&q=80',
                'content' => 'Fitur AI live translate sangat membantu tugas kantor dan meeting internasional. Kamera zoom 5x tajam luar biasa.',
                'rating_value' => 5,
                'reviewed_at' => $now->copy()->subDays(5),
                'source_reference' => 'Review Shopee Verified',
                'is_visible' => true,
            ]
        );

        // 5. Seed some affiliate clicks for Analytics & Dashboard
        for ($i = 0; $i < 25; $i++) {
            AffiliateClick::create([
                'product_id' => $p1->id,
                'clicked_at' => $now->copy()->subHours(rand(1, 168)),
                'target_url_snapshot' => $p1->affiliate_url,
            ]);
        }

        for ($i = 0; $i < 42; $i++) {
            AffiliateClick::create([
                'product_id' => $p2->id,
                'clicked_at' => $now->copy()->subHours(rand(1, 168)),
                'target_url_snapshot' => $p2->affiliate_url,
            ]);
        }

        for ($i = 0; $i < 18; $i++) {
            AffiliateClick::create([
                'product_id' => $p3->id,
                'clicked_at' => $now->copy()->subHours(rand(1, 168)),
                'target_url_snapshot' => $p3->affiliate_url,
            ]);
        }
    }
}
