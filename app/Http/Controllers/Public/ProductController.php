<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\Catalog\ProductDetailService;
use Illuminate\Contracts\View\View;

class ProductController extends Controller
{
    protected ProductDetailService $detailService;

    public function __construct(ProductDetailService $detailService)
    {
        $this->detailService = $detailService;
    }

    public function show(string $slug): View
    {
        $data = $this->detailService->getActiveProductBySlug($slug);

        return view('public.product-detail', [
            'product' => $data['product'],
            'isCtaEligible' => $data['isCtaEligible'],
        ]);
    }
}
