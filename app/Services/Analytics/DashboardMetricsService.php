<?php

namespace App\Services\Analytics;

use App\Models\AffiliateClick;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class DashboardMetricsService
{
    /**
     * Gather zero-safe metrics for admin dashboard.
     *
     * @return array
     */
    public function getMetrics(): array
    {
        $totalProducts = Product::whereNull('deleted_at')->count();
        $activeProducts = Product::where('is_active', true)->whereNull('deleted_at')->count();
        $totalCategories = Category::whereNull('deleted_at')->count();
        $totalClicks = AffiliateClick::count();

        // Breakdown with/without video
        $productsWithVideo = Product::whereNull('deleted_at')
            ->whereNotNull('review_video_url')
            ->where('review_video_url', '!=', '')
            ->count();

        $productsWithoutVideo = $totalProducts - $productsWithVideo;

        // Top popular products by affiliate clicks
        $popularProducts = Product::select('products.id', 'products.name', 'products.slug', 'products.is_active', 'products.affiliate_url_status')
            ->selectRaw('COUNT(affiliate_clicks.id) as clicks_count')
            ->leftJoin('affiliate_clicks', 'products.id', '=', 'affiliate_clicks.product_id')
            ->whereNull('products.deleted_at')
            ->groupBy('products.id', 'products.name', 'products.slug', 'products.is_active', 'products.affiliate_url_status')
            ->orderByDesc('clicks_count')
            ->limit(5)
            ->get();

        // Recent products
        $recentProducts = Product::with('category')
            ->withCount('clicks')
            ->whereNull('deleted_at')
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get();

        return [
            'total_products' => $totalProducts,
            'active_products' => $activeProducts,
            'total_categories' => $totalCategories,
            'total_clicks' => $totalClicks,
            'products_with_video' => $productsWithVideo,
            'products_without_video' => $productsWithoutVideo,
            'popular_products' => $popularProducts,
            'recent_products' => $recentProducts,
        ];
    }
}
