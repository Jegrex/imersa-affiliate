<?php

namespace App\Actions\Admin;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateCategoryAction
{
    public function execute(Category $category, array $data): Category
    {
        return DB::transaction(function () use ($category, $data) {
            /** @var Category $lockedCategory */
            $lockedCategory = Category::where('id', $category->id)
                ->whereNull('deleted_at')
                ->lockForUpdate()
                ->firstOrFail();

            $newParentId = !empty($data['parent_id']) ? (int) $data['parent_id'] : null;
            $newIsActive = (bool) $data['is_active'];

            // 1. Cycle & self-reference check
            if ($newParentId !== null) {
                if ($newParentId === (int) $lockedCategory->id) {
                    throw ValidationException::withMessages([
                        'parent_id' => 'Kategori tidak dapat menjadi induk bagi dirinya sendiri.',
                    ]);
                }

                if ($this->isDescendant($newParentId, (int) $lockedCategory->id)) {
                    throw ValidationException::withMessages([
                        'parent_id' => 'Kategori tidak dapat memilih turunannya sendiri sebagai induk.',
                    ]);
                }
            }

            // 2. Inactivation Guard (BR-32):
            // Cannot inactivate if any referencing product is active & not soft-deleted
            if ($lockedCategory->is_active && !$newIsActive) {
                $activeProductsCount = Product::where('category_id', $lockedCategory->id)
                    ->where('is_active', true)
                    ->whereNull('deleted_at')
                    ->count();

                if ($activeProductsCount > 0) {
                    throw ValidationException::withMessages([
                        'is_active' => "Kategori tidak dapat dinonaktifkan karena masih terdapat {$activeProductsCount} produk aktif yang terhubung dengannya.",
                    ]);
                }
            }

            $lockedCategory->update($data);

            return $lockedCategory->fresh();
        });
    }

    protected function isDescendant(int $potentialChildId, int $parentId): bool
    {
        $current = Category::find($potentialChildId);
        while ($current && $current->parent_id !== null) {
            if ((int) $current->parent_id === $parentId) {
                return true;
            }
            $current = Category::find($current->parent_id);
        }

        return false;
    }
}
