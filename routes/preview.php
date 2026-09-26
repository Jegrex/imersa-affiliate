<?php

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

Route::prefix('preview')->name('preview.')->group(function () {
    $render = function (Request $request, string $page, ?string $key = null) {
        abort_unless(app()->environment(['local', 'testing']), 404);
        $data = require resource_path('demo/catalog.php');
        $empty = $request->query('state') === 'empty';
        $allProducts = $data['products'];
        $publicProducts = $allProducts->filter(fn ($p) => $p['is_active'] && $p['category']['active']);
        $data += ['page' => $page, 'empty' => $empty, 'product' => null, 'category' => null];
        $data['publicProducts'] = $empty ? collect() : $publicProducts;
        $data['allProducts'] = $allProducts;
        if (in_array($page, ['detail', 'product-form', 'reviews'])) {
            if ($key !== null) {
                $data['product'] = ($page === 'detail' ? $publicProducts : $allProducts)->firstWhere($page === 'detail' ? 'slug' : 'id', $key);
                if ($data['product'] === null) {
                    return response()->view('preview.unavailable', $data, 404);
                }
            }
        }
        if ($page === 'category-form' && $key !== null) {
            $data['category'] = $data['categories']->firstWhere('id', $key);
            if ($data['category'] === null) {
                return response()->view('preview.unavailable', $data, 404);
            }
        }
        if (in_array($page, ['catalog', 'products'])) {
            $validator = Validator::make($request->query(), [
                'q' => ['nullable', 'string', 'max:100'], 'category' => ['nullable', 'string', 'max:255'],
                'status' => ['nullable', 'in:active,draft'], 'page' => ['nullable', 'integer', 'min:1'],
            ]);
            if ($validator->fails()) {
                return response()->view('preview.query-error', $data + ['validationErrors' => $validator->errors()], 422);
            }
            $query = $validator->validated();
            $items = $empty ? collect() : ($page === 'catalog' ? $publicProducts : $allProducts);
            $q = trim($query['q'] ?? '');
            if ($q !== '') {
                $items = $items->filter(fn ($p) => str_contains(mb_strtolower($p['name']), mb_strtolower($q)));
            }
            if (! empty($query['category'])) {
                $category = $data['categories']->firstWhere('slug', $query['category']);
                // Presentation choice: include descendants; confirm with backend at integration.
                $ids = $category ? [$category['id']] : [];
                do {
                    $before = count($ids);
                    $ids = array_values(array_unique(array_merge($ids, $data['categories']->whereIn('parent_id', $ids)->pluck('id')->all())));
                } while (count($ids) > $before);
                $items = $items->whereIn('category_id', $ids);
            }
            if ($page === 'products' && ! empty($query['status'])) {
                $items = $items->where('is_active', $query['status'] === 'active');
            }
            $perPage = $page === 'catalog' ? 24 : 15;
            $current = min((int) ($query['page'] ?? 1), max(1, (int) ceil($items->count() / $perPage)));
            $data['listing'] = new LengthAwarePaginator($items->values()->forPage($current, $perPage), $items->count(), $perPage, $current, ['path' => $request->url(), 'query' => $request->except('page')]);
        }
        if (in_array($page, ['dashboard', 'analytics'])) {
            $validator = Validator::make($request->query(), ['from' => ['nullable', 'date_format:Y-m-d'], 'to' => ['nullable', 'date_format:Y-m-d']]);
            if ($validator->fails()) {
                return response()->view('preview.query-error', $data + ['validationErrors' => $validator->errors()], 422);
            }
            $from = $request->query('from') ?: '2026-09-01';
            $to = $request->query('to') ?: '2026-09-24';
            $rangeError = $to < $from || Carbon::parse($from)->diffInDays(Carbon::parse($to)) > 365;
            $events = $empty || $rangeError ? collect() : $data['clickEvents']->filter(fn ($e) => $e['date'] >= $from && $e['date'] <= $to);
            $data += compact('from', 'to', 'rangeError');
            $data['totalClicks'] = $events->count();
            $data['ranking'] = $allProducts->map(fn ($p) => array_merge($p, ['period_clicks' => $events->where('product_id', $p['id'])->count()]))->sortByDesc('period_clicks')->values();
            $data['chart'] = $events->groupBy('date')->map->count();
        }

        return view('preview.'.$page, $data);
    };
    Route::get('/', fn (Request $r) => $render($r, 'home'))->name('home');
    Route::get('/catalog', fn (Request $r) => $render($r, 'catalog'))->name('catalog');
    Route::get('/products/{slug}', fn (Request $r, string $slug) => $render($r, 'detail', $slug))->name('detail');
    Route::get('/admin/login', fn (Request $r) => $render($r, 'login'))->name('login');
    foreach (['dashboard', 'products', 'categories', 'analytics', 'imports'] as $page) {
        Route::get('/admin/'.$page, fn (Request $r) => $render($r, $page))->name($page);
    }
    Route::get('/admin/products/create', fn (Request $r) => $render($r, 'product-form'))->name('products.create');
    Route::get('/admin/products/{id}/edit', fn (Request $r, string $id) => $render($r, 'product-form', $id))->name('products.edit');
    // Keep previously shared preview URLs working after merging the affiliate input into the product form.
    Route::get('/admin/products/{id}/affiliate', function (string $id) {
        abort_unless(app()->environment(['local', 'testing']), 404);

        return redirect(route('preview.products.edit', $id).'#referensi-shopee');
    })->name('approval');
    Route::get('/admin/products/{id}/reviews', fn (Request $r, string $id) => $render($r, 'reviews', $id))->name('reviews');
    Route::get('/admin/categories/create', fn (Request $r) => $render($r, 'category-form'))->name('categories.create');
    Route::get('/admin/categories/{id}/edit', fn (Request $r, string $id) => $render($r, 'category-form', $id))->name('categories.edit');
});
