<?php

namespace App\Services\Catalog;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CatalogQueryService
{
    public const PER_PAGE = 15;

    /**
     * Search and filter products with pagination.
     * Name search only on products.name (per contract).
     * Exact match category filter.
     *
     * @param string|null $query
     * @param string|null $categorySlugOrId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function query(?string $query = null, ?string $categorySlugOrId = null, int $perPage = self::PER_PAGE): LengthAwarePaginator
    {
        $productsQuery = Product::catalogVisible()->with(['category', 'images']);

        // Search strictly on normalized products.name only
        if ($query !== null && trim($query) !== '') {
            $searchTerm = trim($query);
            $productsQuery->where('name', 'LIKE', '%' . $searchTerm . '%');
        }

        // Category filter: exact match on slug or ID
        if ($categorySlugOrId !== null && trim($categorySlugOrId) !== '') {
            $catFilter = trim($categorySlugOrId);
            if (is_numeric($catFilter)) {
                $productsQuery->where('category_id', (int) $catFilter);
            } else {
                $productsQuery->whereHas('category', function ($q) use ($catFilter) {
                    $q->where('slug', $catFilter);
                });
            }
        }

        return $productsQuery->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->paginate($perPage)
            ->withQueryString();
    }
}
