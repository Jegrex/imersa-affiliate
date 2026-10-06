<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\Catalog\CatalogService;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    protected CatalogService $catalogService;

    public function __construct(CatalogService $catalogService)
    {
        $this->catalogService = $catalogService;
    }

    public function __invoke(): View
    {
        $categories = $this->catalogService->getActiveCategories();
        $featuredProducts = $this->catalogService->getFeaturedProducts(8);

        return view('public.home', compact('categories', 'featuredProducts'));
    }
}
