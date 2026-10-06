<?php

namespace App\Actions\Admin;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeactivateCategoryAction
{
    /**
     * Deactivate and soft-delete category.
     * Rejects if active non-deleted products reference it.
     *
     * @param Category $category
     * @return bool
     * @throws ValidationException
     */
    public function execute(Category $category): bool
    {
        return DB::transaction(function () use ($category) {
            /** @var Category $lockedCategory */
            $lockedCategory = Category::where('id', $category->id)
                ->whereNull('deleted_at')
                ->lockForUpdate()
                ->firstOrFail();

            // Check referencing active non-deleted products
            $activeProductsCount = Product::where('category_id', $lockedCategory->id)
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->count();

            if ($activeProductsCount > 0) {
                throw ValidationException::withMessages([
                    'category' => "Kategori tidak dapat dihapus/dinonaktifkan karena masih terdapat {$activeProductsCount} produk aktif yang terhubung dengannya. Nonaktifkan atau pindahkan produk tersebut terlebih dahulu.",
                ]);
            }

            // Check if active subcategories exist
            $activeChildrenCount = Category::where('parent_id', $lockedCategory->id)
                ->whereNull('deleted_at')
                ->count();

            if ($activeChildrenCount > 0) {
                throw ValidationException::withMessages([
                    'category' => "Kategori tidak dapat dihapus karena masih memiliki {$activeChildrenCount} sub-kategori.",
                ]);
            }

            $lockedCategory->is_active = false;
            $lockedCategory->save();

            return (bool) $lockedCategory->delete();
        });
    }
}
