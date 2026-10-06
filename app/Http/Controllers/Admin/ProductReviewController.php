<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\CreateProductReviewAction;
use App\Actions\Admin\HideProductReviewAction;
use App\Actions\Admin\UpdateProductReviewAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductReviewRequest;
use App\Http\Requests\Admin\UpdateProductReviewRequest;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ProductReviewController extends Controller
{
    public function index(Product $product): View
    {
        Gate::forUser(Auth::guard('admin')->user())->authorize('viewAny', ProductReview::class);

        $reviews = ProductReview::with('images')
            ->where('product_id', $product->id)
            ->orderByDesc('id')
            ->paginate(15);

        return view('admin.reviews.index', compact('product', 'reviews'));
    }

    public function store(StoreProductReviewRequest $request, Product $product, CreateProductReviewAction $action): RedirectResponse
    {
        $reviewData = [
            'provenance' => $request->input('provenance'),
            'reviewer_name' => $request->filled('reviewer_name') ? trim($request->input('reviewer_name')) : null,
            'avatar_url' => $request->filled('avatar_url') ? trim($request->input('avatar_url')) : null,
            'content' => $request->filled('content') ? trim($request->input('content')) : null,
            'rating_value' => $request->filled('rating_value') ? $request->input('rating_value') : null,
            'reviewed_at' => $request->filled('reviewed_at') ? $request->input('reviewed_at') : null,
            'source_reference' => $request->filled('source_reference') ? trim($request->input('source_reference')) : null,
            'is_visible' => $request->boolean('is_visible'),
        ];
        $imagesData = $request->input('images', []);

        $action->execute($product, $reviewData, $imagesData);

        return redirect()->route('admin.reviews.index', $product)->with('success', 'Ulasan produk berhasil ditambahkan.');
    }

    public function update(UpdateProductReviewRequest $request, ProductReview $review, UpdateProductReviewAction $action): RedirectResponse
    {
        $reviewData = [
            'provenance' => $request->input('provenance'),
            'reviewer_name' => $request->filled('reviewer_name') ? trim($request->input('reviewer_name')) : null,
            'avatar_url' => $request->filled('avatar_url') ? trim($request->input('avatar_url')) : null,
            'content' => $request->filled('content') ? trim($request->input('content')) : null,
            'rating_value' => $request->filled('rating_value') ? $request->input('rating_value') : null,
            'reviewed_at' => $request->filled('reviewed_at') ? $request->input('reviewed_at') : null,
            'source_reference' => $request->filled('source_reference') ? trim($request->input('source_reference')) : null,
            'is_visible' => $request->boolean('is_visible'),
        ];
        $imagesData = $request->has('images') ? $request->input('images', []) : null;

        $action->execute($review, $reviewData, $imagesData);

        return redirect()->route('admin.reviews.index', $review->product_id)->with('success', 'Ulasan produk berhasil diperbarui.');
    }

    public function destroy(ProductReview $review, HideProductReviewAction $action): RedirectResponse
    {
        Gate::forUser(Auth::guard('admin')->user())->authorize('delete', $review);

        $action->execute($review);

        return redirect()->route('admin.reviews.index', $review->product_id)->with('success', 'Ulasan berhasil disembunyikan (is_visible = false).');
    }
}
