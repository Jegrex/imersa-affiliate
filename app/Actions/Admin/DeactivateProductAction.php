<?php

namespace App\Actions\Admin;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class DeactivateProductAction
{
    /**
     * Deactivate and soft-delete a product while preserving all history (clicks, reviews, media).
     *
     * @param Product $product
     * @return bool
     */
    public function execute(Product $product): bool
    {
        return DB::transaction(function () use ($product) {
            /** @var Product $lockedProduct */
            $lockedProduct = Product::where('id', $product->id)->lockForUpdate()->firstOrFail();

            // Set is_active to false first
            $lockedProduct->is_active = false;
            $lockedProduct->save();

            // Soft-delete the product
            return (bool) $lockedProduct->delete();
        });
    }
}
