<?php

namespace App\Services\Analytics;

use App\Models\AffiliateClick;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class AnalyticsQueryService
{
    public const MAX_DAYS_RANGE = 366;

    /**
     * Query analytics metrics within an optional date range (max 366 days).
     *
     * @param string|null $fromDate
     * @param string|null $toDate
     * @return array
     * @throws ValidationException
     */
    public function query(?string $fromDate = null, ?string $toDate = null): array
    {
        $end = $toDate ? Carbon::parse($toDate)->endOfDay() : Carbon::now('UTC')->endOfDay();
        $start = $fromDate ? Carbon::parse($fromDate)->startOfDay() : (clone $end)->subDays(30)->startOfDay();

        if ($start->gt($end)) {
            throw ValidationException::withMessages([
                'from' => 'Tanggal awal tidak boleh melebihi tanggal akhir.',
            ]);
        }

        $daysDifference = $start->diffInDays($end);
        if ($daysDifference > self::MAX_DAYS_RANGE) {
            throw ValidationException::withMessages([
                'range' => 'Rentang periode maksimal adalah ' . self::MAX_DAYS_RANGE . ' hari (diberikan: ' . $daysDifference . ' hari).',
            ]);
        }

        $clicksQuery = AffiliateClick::whereBetween('clicked_at', [$start, $end]);
        $totalClicks = (clone $clicksQuery)->count();

        // Top products by clicks in period
        $topProducts = Product::select('products.id', 'products.name', 'products.slug', 'products.is_active')
            ->selectRaw('COUNT(affiliate_clicks.id) as period_clicks_count')
            ->join('affiliate_clicks', 'products.id', '=', 'affiliate_clicks.product_id')
            ->whereBetween('affiliate_clicks.clicked_at', [$start, $end])
            ->whereNull('products.deleted_at')
            ->groupBy('products.id', 'products.name', 'products.slug', 'products.is_active')
            ->orderByDesc('period_clicks_count')
            ->limit(10)
            ->get();

        // Daily clicks distribution
        $dailyClicks = AffiliateClick::selectRaw('DATE(clicked_at) as date, COUNT(*) as count')
            ->whereBetween('clicked_at', [$start, $end])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $productsWithVideo = Product::whereNull('deleted_at')
            ->whereNotNull('review_video_url')
            ->where('review_video_url', '!=', '')
            ->count();

        $totalProducts = Product::whereNull('deleted_at')->count();

        return [
            'from' => $start->format('Y-m-d'),
            'to' => $end->format('Y-m-d'),
            'total_clicks' => $totalClicks,
            'top_products' => $topProducts,
            'daily_clicks' => $dailyClicks,
            'products_with_video' => $productsWithVideo,
            'products_without_video' => $totalProducts - $productsWithVideo,
        ];
    }
}
