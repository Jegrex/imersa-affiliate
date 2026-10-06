<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\CreateCategoryAction;
use App\Actions\Admin\DeactivateCategoryAction;
use App\Actions\Admin\UpdateCategoryAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class CategoryController extends Controller
{
    public function index(): View
    {
        Gate::forUser(Auth::guard('admin')->user())->authorize('viewAny', Category::class);

        $categories = Category::with('parent')
            ->withCount(['products' => function ($q) {
                $q->where('is_active', true)->whereNull('deleted_at');
            }])
            ->whereNull('deleted_at')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20);

        $parentCategories = Category::whereNull('deleted_at')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.categories.index', compact('categories', 'parentCategories'));
    }

    public function store(StoreCategoryRequest $request, CreateCategoryAction $action): RedirectResponse
    {
        $category = $action->execute($request->validated(), Auth::guard('admin')->user());

        return redirect()->route('admin.categories.index')->with('success', "Kategori '{$category->name}' berhasil dibuat.");
    }

    public function update(UpdateCategoryRequest $request, Category $category, UpdateCategoryAction $action): RedirectResponse
    {
        $updated = $action->execute($category, $request->validated());

        return redirect()->route('admin.categories.index')->with('success', "Kategori '{$updated->name}' berhasil diperbarui.");
    }

    public function destroy(Category $category, DeactivateCategoryAction $action): RedirectResponse
    {
        Gate::forUser(Auth::guard('admin')->user())->authorize('delete', $category);

        $action->execute($category);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil dinonaktifkan.');
    }
}
