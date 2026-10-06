<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\Catalog\CatalogQueryService;
use App\Services\Catalog\CatalogService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    protected CatalogQueryService $queryService;
    protected CatalogService $catalogService;

    public function __construct(CatalogQueryService $queryService, CatalogService $catalogService)
    {
        $this->queryService = $queryService;
        $this->catalogService = $catalogService;
    }

    public function index(Request $request): View
    {
        $searchQuery = $request->query('q');
        $categoryParam = $request->query('category');

        $products = $this->queryService->query($searchQuery, $categoryParam);
        $categories = $this->catalogService->getActiveCategories();

        return view('public.catalog', compact('products', 'categories', 'searchQuery', 'categoryParam'));
    }
}
