<?php

namespace App\Actions\Admin;

use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ReviewImage;
use Illuminate\Support\Facades\DB;

class CreateProductReviewAction
{
    public function execute(Product $product, array $reviewData, array $imagesData = []): ProductReview
    {
        return DB::transaction(function () use ($product, $reviewData, $imagesData) {
            $reviewData['product_id'] = $product->id;

            $review = ProductReview::create($reviewData);

            foreach ($imagesData as $index => $img) {
                if (!empty($img['image_url'])) {
                    ReviewImage::create([
                        'product_review_id' => $review->id,
                        'image_url' => trim($img['image_url']),
                        'sort_order' => isset($img['sort_order']) ? (int) $img['sort_order'] : $index,
                    ]);
                }
            }

            return $review->fresh('images');
        });
    }
}
