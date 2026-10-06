<?php

namespace App\Actions\Admin;

use App\Models\Admin;
use App\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReviewAffiliateUrlAction
{
    /**
     * Atomically approve or reject a candidate affiliate URL.
     *
     * @param Product $product
     * @param Admin $admin
     * @param string $action 'approve' | 'reject'
     * @param string|null $affiliateUrl
     * @param string|null $provenance
     * @param string|null $sourceField
     * @return Product
     */
    public function execute(
        Product $product,
        Admin $admin,
        string $action,
        ?string $affiliateUrl = null,
        ?string $provenance = null,
        ?string $sourceField = null
    ): Product {
        return DB::transaction(function () use ($product, $admin, $action, $affiliateUrl, $provenance, $sourceField) {
            /** @var Product $lockedProduct */
            $lockedProduct = Product::where('id', $product->id)->lockForUpdate()->firstOrFail();

            if ($action === 'approve') {
                $lockedProduct->update([
                    'affiliate_url' => trim($affiliateUrl),
                    'affiliate_url_status' => 'approved',
                    'affiliate_url_provenance' => $provenance ?: ($lockedProduct->affiliate_url_provenance ?: 'internal_manual'),
                    'affiliate_url_source_field' => $sourceField ?: $lockedProduct->affiliate_url_source_field,
                    'affiliate_url_approved_by_admin_id' => $admin->id,
                    'affiliate_url_approved_at' => Carbon::now('UTC'),
                ]);
            } else {
                $lockedProduct->update([
                    'affiliate_url_status' => 'rejected',
                    'affiliate_url_approved_by_admin_id' => null,
                    'affiliate_url_approved_at' => null,
                ]);
            }

            return $lockedProduct->fresh();
        });
    }
}
