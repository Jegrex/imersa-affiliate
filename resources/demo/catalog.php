<?php

use Illuminate\Support\Str;

// Presentation fixtures only. No real customer, approval, or transaction data.
$categories = collect([
    ['id' => 1, 'name' => 'Laptop & PC', 'slug' => 'laptop-pc', 'parent_id' => null, 'active' => true, 'icon' => 'laptop', 'description' => 'Teman kerja, belajar, dan ide berikutnya.'],
    ['id' => 2, 'name' => 'Smartphone', 'slug' => 'smartphone', 'parent_id' => null, 'active' => true, 'icon' => 'phone', 'description' => 'Tetap terhubung, di mana pun harimu.'],
    ['id' => 3, 'name' => 'Audio', 'slug' => 'audio', 'parent_id' => null, 'active' => true, 'icon' => 'headphones', 'description' => 'Ruang untuk suara yang kamu sukai.'],
    ['id' => 4, 'name' => 'Aksesori', 'slug' => 'aksesori', 'parent_id' => null, 'active' => true, 'icon' => 'mouse', 'description' => 'Detail kecil untuk aktivitas yang lebih nyaman.'],
    ['id' => 5, 'name' => 'Laptop kerja', 'slug' => 'laptop-kerja', 'parent_id' => 1, 'active' => true, 'icon' => 'laptop', 'description' => 'Pilihan untuk produktivitas sehari-hari.'],
    ['id' => 6, 'name' => 'Display', 'slug' => 'display', 'parent_id' => null, 'active' => false, 'icon' => 'monitor', 'description' => 'Kategori belum diterbitkan.'],
]);
$rows = [
    [1, 'MacBook Air M2', 'Apple', 5, 17499000, 4.9, 128, 'macbook.webp', true, 'approved', 'Ringkas untuk setiap ide', 184],
    [2, 'ThinkPad X1 Carbon', 'Lenovo', 5, 22100000, 4.8, 64, 'thinkpad.webp', true, 'approved', null, 121],
    [3, 'Galaxy S23 Ultra', 'Samsung', 2, 19999000, 4.7, 96, 'galaxy.webp', true, 'approved', null, 96],
    [4, 'WH-1000XM5 Wireless', 'Sony', 3, 5199000, 4.9, 84, 'headphones.webp', true, 'approved', 'Ruang untuk fokus', 143],
    [5, 'IdeaPad Slim 5', 'Lenovo', 5, 12499000, 4.7, 32, 'ideapad.webp', true, 'approved', null, 61],
    [6, 'Galaxy A55 5G', 'Samsung', 2, null, 4.4, 21, 'phone.webp', true, 'pending', null, 0],
    [7, 'Live Pro 2 TWS', 'JBL', 3, 1599000, null, null, 'earbuds.webp', true, 'approved', null, 32],
    [8, 'MX Master 3S', 'Logitech', 4, 1749000, 4.6, 17, 'mouse.webp', true, 'rejected', null, 9],
    [9, 'ROG Zephyrus G14 OLED', 'Asus', 1, 28999000, null, null, 'rog.webp', false, 'pending', null, 0],
    [10, 'Monitor ruang kerja', 'Contoh', 6, null, null, null, null, false, 'pending', null, 0],
];
$products = collect($rows)->map(function ($row) use ($categories) {
    [$id, $name, $brand, $categoryId, $price, $rating, $reviewCount, $image, $active, $approval, $label, $clicks] = $row;

    return [
        'id' => $id, 'name' => $name, 'slug' => Str::slug($name), 'brand' => $brand,
        'category_id' => $categoryId, 'category' => $categories->firstWhere('id', $categoryId),
        'price_amount' => $price, 'price_currency' => $price === null ? null : 'IDR',
        'shopee_rating' => $rating, 'shopee_review_count' => $reviewCount,
        'image' => $image, 'is_active' => $active, 'approval' => $approval, 'label' => $label, 'clicks' => $clicks,
        'description' => 'Kenali '.$name.' melalui informasi produk, spesifikasi, dan referensi yang tersedia. Isi pada halaman pratinjau ini merupakan data contoh, bukan informasi penawaran yang sudah diverifikasi.',
        'specifications' => [['name' => 'Merek', 'value' => $brand], ['name' => 'Model', 'value' => $name]],
        'store_name' => '', 'store_url' => '', 'affiliate_url' => '', 'video_url' => null,
    ];
});
$reviews = collect([
    ['id' => 1, 'product_id' => 1, 'name' => 'Pengguna contoh A', 'content' => 'Ringkas untuk dibawa bekerja. Ini contoh tampilan referensi ulasan.', 'rating' => 5, 'date' => '12 September 2026', 'visible' => true, 'source' => 'Referensi Shopee — data contoh'],
    ['id' => 2, 'product_id' => 1, 'name' => 'Pengguna contoh B', 'content' => 'Tampilan nyaman digunakan untuk aktivitas sehari-hari. Data simulasi.', 'rating' => null, 'date' => null, 'visible' => false, 'source' => 'Referensi Shopee — data contoh'],
]);
$clickEvents = collect();
foreach ($products as $product) {
    for ($i = 0; $i < $product['clicks']; $i++) {
        $clickEvents->push(['product_id' => $product['id'], 'date' => sprintf('2026-09-%02d', 1 + $i % 24)]);
    }
}

return compact('categories', 'products', 'reviews', 'clickEvents');
