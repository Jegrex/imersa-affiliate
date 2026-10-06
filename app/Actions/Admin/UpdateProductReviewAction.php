<?php

namespace App\Actions\Admin;

use App\Models\ProductReview;
use App\Models\ReviewImage;
use Illuminate\Support\Facades\DB;

class UpdateProductReviewAction
{
    public function execute(ProductReview $review, array $reviewData, ?array $imagesData = null): ProductReview
    {
        return DB::transaction(function () use ($review, $reviewData, $imagesData) {
            $review->update($reviewData);

            if ($imagesData !== null) {
                // Delete existing manual review images
                ReviewImage::where('product_review_id', $review->id)->delete();

                foreach ($imagesData as $index => $img) {
                    if (!empty($img['image_url'])) {
                        ReviewImage::create([
                            'product_review_id' => $review->id,
                            'image_url' => trim($img['image_url']),
                            'sort_order' => isset($img['sort_order']) ? (int) $img['sort_order'] : $index,
                        ]);
                    }
                }
            }

            return $review->fresh('images');
        });
    }
}
