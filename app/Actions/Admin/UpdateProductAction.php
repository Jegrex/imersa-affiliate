<?php

namespace App\Actions\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductSpecification;
use App\Support\HtmlSanitization\HtmlSanitizer;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateProductAction
{
    protected HtmlSanitizer $sanitizer;

    public function __construct(HtmlSanitizer $sanitizer)
    {
        $this->sanitizer = $sanitizer;
    }

    /**
     * Update product with images and specifications atomically.
     * Implements consistent category ordering locks and rechecks invariants.
     *
     * @param Product $product
     * @param array $productData Whitelisted safe product data
     * @param string|null $rawDescriptionHtml
     * @param array|null $imagesData
     * @param array|null $specificationsData
     * @return Product
     * @throws ValidationException
     */
    public function execute(
        Product $product,
        array $productData,
        ?string $rawDescriptionHtml = null,
        ?array $imagesData = null,
        ?array $specificationsData = null
    ): Product {
        // Sanitize HTML and derive text before DB transaction
        if ($rawDescriptionHtml !== null) {
            $sanitized = $this->sanitizer->sanitize($rawDescriptionHtml);
            $productData['description_html'] = $sanitized['html'];
            $productData['description_text'] = $sanitized['text'];
        }

        return DB::transaction(function () use ($product, $productData, $imagesData, $specificationsData) {
            $oldCategoryId = $product->category_id ? (int) $product->category_id : null;
            $newCategoryId = !empty($productData['category_id']) ? (int) $productData['category_id'] : null;

            // Collect category IDs to lock in sorted ascending order (deadlock prevention)
            $categoryIdsToLock = array_values(array_unique(array_filter([$oldCategoryId, $newCategoryId])));
            sort($categoryIdsToLock);

            $lockedCategories = [];
            foreach ($categoryIdsToLock as $catId) {
                $cat = Category::where('id', $catId)->whereNull('deleted_at')->lockForUpdate()->first();
                if ($cat) {
                    $lockedCategories[$catId] = $cat;
                }
            }

            // Lock Product
            /** @var Product $lockedProduct */
            $lockedProduct = Product::where('id', $product->id)
                ->whereNull('deleted_at')
                ->lockForUpdate()
                ->firstOrFail();

            // Recheck Category invariant after lock
            if ($newCategoryId !== null) {
                $category = $lockedCategories[$newCategoryId] ?? null;
                if (!$category) {
                    throw ValidationException::withMessages([
                        'category_id' => 'Kategori yang dipilih tidak valid atau sudah dihapus.',
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

            // Update product
            $lockedProduct->update($productData);

            // Update images if provided (protecting import-provenance child rows)
            if ($imagesData !== null) {
                // Delete only manual images (where import_record_id is null)
                ProductImage::where('product_id', $lockedProduct->id)
                    ->whereNull('import_record_id')
                    ->delete();

                foreach ($imagesData as $index => $imageData) {
                    if (!empty($imageData['image_url'])) {
                        ProductImage::create([
                            'product_id' => $lockedProduct->id,
                            'image_url' => trim($imageData['image_url']),
                            'alt_text' => !empty($imageData['alt_text']) ? trim($imageData['alt_text']) : null,
                            'sort_order' => isset($imageData['sort_order']) ? (int) $imageData['sort_order'] : $index,
                        ]);
                    }
                }
            }

            // Update specifications if provided (protecting import-provenance child rows)
            if ($specificationsData !== null) {
                // Delete only manual specifications (where import_record_id is null)
                ProductSpecification::where('product_id', $lockedProduct->id)
                    ->whereNull('import_record_id')
                    ->delete();

                foreach ($specificationsData as $index => $specData) {
                    if (!empty($specData['name']) && isset($specData['value'])) {
                        ProductSpecification::create([
                            'product_id' => $lockedProduct->id,
                            'name' => trim($specData['name']),
                            'value' => trim($specData['value']),
                            'sort_order' => isset($specData['sort_order']) ? (int) $specData['sort_order'] : $index,
                        ]);
                    }
                }
            }

            return $lockedProduct->fresh(['category', 'images', 'specifications']);
        });
    }
}
