<?php

namespace App\Services\Catalog;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class CatalogService
{
    /**
     * Get active categories for navigation and filter.
     *
     * @return Collection
     */
    public function getActiveCategories(): Collection
    {
        return Category::where('is_active', true)
            ->whereNull('deleted_at')
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get featured products for homepage.
     * As per contract/PO decision: no hidden is_featured column.
     * Temporary order: latest active products ordered by published_at / id desc.
     *
     * @param int $limit
     * @return Collection
     */
    public function getFeaturedProducts(int $limit = 8): Collection
    {
        return Product::catalogVisible()
            ->with(['category', 'images'])
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->limit($limit)
            ->get();
    }
}
