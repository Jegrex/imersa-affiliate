<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $category = $this->route('category');
        return $this->user('admin') && $this->user('admin')->can('update', $category);
    }

    public function rules(): array
    {
        $category = $this->route('category');
        $categoryId = $category instanceof Category ? $category->id : $category;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($categoryId)],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id,deleted_at,NULL'],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $category = $this->route('category');
            if (!$category instanceof Category) {
                $category = Category::find($category);
            }

            if (!$category) {
                return;
            }

            $parentId = $this->input('parent_id') ? (int) $this->input('parent_id') : null;
            $newIsActive = $this->boolean('is_active');

            // 1. Cycle & self-reference prevention
            if ($parentId !== null) {
                if ($parentId === (int) $category->id) {
                    $v->errors()->add('parent_id', 'Kategori tidak dapat menjadi induk bagi dirinya sendiri.');
                    return;
                }

                // Check if parentId is a descendant of category
                if ($this->isDescendant($parentId, (int) $category->id)) {
                    $v->errors()->add('parent_id', 'Kategori tidak dapat memilih turunannya sendiri sebagai induk (siklus terdeteksi).');
                    return;
                }
            }

            // 2. Inactivation Guard (BR-32 & Section 1.7)
            // If category was active and now requested to be inactive, ensure no active non-deleted products reference it
            if ($category->is_active && !$newIsActive) {
                $activeProductsCount = Product::where('category_id', $category->id)
                    ->where('is_active', true)
                    ->whereNull('deleted_at')
                    ->count();

                if ($activeProductsCount > 0) {
                    $v->errors()->add('is_active', "Kategori tidak dapat dinonaktifkan karena masih terdapat {$activeProductsCount} produk aktif yang terhubung dengannya. Nonaktifkan atau pindahkan produk terlebih dahulu.");
                }
            }
        });
    }

    /**
     * Check if potentialChildId is a descendant of parentId.
     */
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
