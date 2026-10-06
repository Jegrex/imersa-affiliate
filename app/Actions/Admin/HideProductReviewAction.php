<?php

namespace App\Actions\Admin;

use App\Models\ProductReview;

class HideProductReviewAction
{
    /**
     * Hide review by setting is_visible = false.
     * Preserves review history and provenance without physical deletion.
     *
     * @param ProductReview $review
     * @return bool
     */
    public function execute(ProductReview $review): bool
    {
        $review->is_visible = false;
        return $review->save();
    }
}
