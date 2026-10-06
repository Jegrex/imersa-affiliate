<?php

namespace App\Services\Catalog;

use App\Models\Product;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductDetailService
{
    /**
     * Retrieve active product detail with relations and CTA status.
     * Throws 404 if product or its category is missing/inactive/deleted.
     *
     * @param string $slug
     * @return array{product: Product, isCtaEligible: bool}
     * @throws NotFoundHttpException
     */
    public function getActiveProductBySlug(string $slug): array
    {
        /** @var Product|null $product */
        $product = Product::where('slug', $slug)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->whereHas('category', function ($q) {
                $q->where('is_active', true)->whereNull('deleted_at');
            })
            ->with([
                'category',
                'images' => fn ($q) => $q->orderBy('sort_order'),
                'specifications' => fn ($q) => $q->orderBy('sort_order'),
                'visibleReviews.images' => fn ($q) => $q->orderBy('sort_order'),
            ])
            ->first();

        if (!$product) {
            abort(404, 'Produk tidak ditemukan atau tidak tersedia.');
        }

        $isCtaEligible = $product->isCtaEligible();

        return [
            'product' => $product,
            'isCtaEligible' => $isCtaEligible,
        ];
    }
}
