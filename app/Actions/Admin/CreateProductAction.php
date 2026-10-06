<?php

namespace App\Actions\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductSpecification;
use App\Support\HtmlSanitization\HtmlSanitizer;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateProductAction
{
    protected HtmlSanitizer $sanitizer;

    public function __construct(HtmlSanitizer $sanitizer)
    {
        $this->sanitizer = $sanitizer;
    }

    /**
     * Create product with images and specifications atomically.
     *
     * @param array $productData Whitelisted safe product data
     * @param string|null $rawDescriptionHtml
     * @param array $imagesData
     * @param array $specificationsData
     * @return Product
     * @throws ValidationException
     */
    public function execute(
        array $productData,
        ?string $rawDescriptionHtml = null,
        array $imagesData = [],
        array $specificationsData = []
    ): Product {
        // Sanitize HTML and derive text before DB transaction
        $sanitized = $this->sanitizer->sanitize($rawDescriptionHtml);
        $productData['description_html'] = $sanitized['html'];
        $productData['description_text'] = $sanitized['text'];

        return DB::transaction(function () use ($productData, $imagesData, $specificationsData) {
            // Lock category if provided
            if (!empty($productData['category_id'])) {
                /** @var Category|null $category */
                $category = Category::where('id', $productData['category_id'])
                    ->whereNull('deleted_at')
                    ->lockForUpdate()
                    ->first();

                if (!$category) {
                    throw ValidationException::withMessages([
                        'category_id' => 'Kategori yang dipilih tidak ditemukan.',
                    ]);
                }

                if ($productData['is_active'] && !$category->is_active) {
                    throw ValidationException::withMessages([
                        'category_id' => 'Produk aktif wajib menggunakan kategori yang aktif.',
                    ]);
                }
            } elseif ($productData['is_active']) {
                throw ValidationException::withMessages([
                    'category_id' => 'Produk aktif wajib memiliki kategori.',
                ]);
            }

            // Create product
            $product = Product::create($productData);

            // Create images
            foreach ($imagesData as $index => $imageData) {
                if (!empty($imageData['image_url'])) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_url' => trim($imageData['image_url']),
                        'alt_text' => !empty($imageData['alt_text']) ? trim($imageData['alt_text']) : null,
                        'sort_order' => isset($imageData['sort_order']) ? (int) $imageData['sort_order'] : $index,
                    ]);
                }
            }

            // Create specifications
            foreach ($specificationsData as $index => $specData) {
                if (!empty($specData['name']) && isset($specData['value'])) {
                    ProductSpecification::create([
                        'product_id' => $product->id,
                        'name' => trim($specData['name']),
                        'value' => trim($specData['value']),
                        'sort_order' => isset($specData['sort_order']) ? (int) $specData['sort_order'] : $index,
                    ]);
                }
            }

            return $product->fresh(['category', 'images', 'specifications']);
        });
    }
}
