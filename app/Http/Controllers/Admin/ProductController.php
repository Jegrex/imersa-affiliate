<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\CreateProductAction;
use App\Actions\Admin\DeactivateProductAction;
use App\Actions\Admin\UpdateProductAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        Gate::forUser(Auth::guard('admin')->user())->authorize('viewAny', Product::class);

        $query = Product::with('category')->withCount('clicks')->whereNull('deleted_at');

        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . trim($request->input('q')) . '%');
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->input('category'));
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($status === 'approved') {
                $query->where('affiliate_url_status', 'approved');
            } elseif ($status === 'pending') {
                $query->where('affiliate_url_status', 'pending');
            } elseif ($status === 'rejected') {
                $query->where('affiliate_url_status', 'rejected');
            }
        }

        $products = $query->orderByDesc('id')->paginate(15)->withQueryString();
        $categories = Category::whereNull('deleted_at')->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create(): View
    {
        Gate::forUser(Auth::guard('admin')->user())->authorize('create', Product::class);

        $categories = Category::whereNull('deleted_at')->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.products.create', compact('categories'));
    }

    public function store(StoreProductRequest $request, CreateProductAction $action): RedirectResponse
    {
        $product = $action->execute(
            $request->safeProductData(),
            $request->input('description_html'),
            $request->input('images', []),
            $request->input('specifications', [])
        );

        return redirect()->route('admin.products.index')->with('success', "Produk '{$product->name}' berhasil dibuat.");
    }

    public function edit(Product $product): View
    {
        Gate::forUser(Auth::guard('admin')->user())->authorize('update', $product);

        $product->load([
            'images' => fn ($q) => $q->orderBy('sort_order'),
            'specifications' => fn ($q) => $q->orderBy('sort_order'),
            'category',
        ]);
        $categories = Category::whereNull('deleted_at')->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(UpdateProductRequest $request, Product $product, UpdateProductAction $action): RedirectResponse
    {
        $updated = $action->execute(
            $product,
            $request->safeProductData(),
            $request->has('description_html') ? $request->input('description_html') : null,
            $request->input('images'),
            $request->input('specifications')
        );

        return redirect()->route('admin.products.edit', $updated)->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product, DeactivateProductAction $action): RedirectResponse
    {
        Gate::forUser(Auth::guard('admin')->user())->authorize('delete', $product);

        $action->execute($product);

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil dinonaktifkan.');
    }
}
