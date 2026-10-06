<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use App\Support\HtmlSanitization\HtmlSanitizer;
use App\Support\UrlValidation\UrlValidator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin') && $this->user('admin')->can('create', \App\Models\Product::class);
    }

    public function rules(): array
    {
        $isActive = filter_var($this->input('is_active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:products,slug'],
            'is_active' => ['required', 'boolean'],
            'label' => ['nullable', 'string', 'max:100'],
            'store_name' => ['nullable', 'string', 'max:255'],
            'store_url' => ['nullable', 'string', 'max:2048'],
            'description_html' => ['nullable', 'string'],
            'price_amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99'],
            'price_currency' => ['nullable', 'string', 'size:3', 'required_with:price_amount'],
            'shopee_rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'shopee_review_count' => ['nullable', 'integer', 'min:0'],
            'review_video_url' => ['nullable', 'string', 'max:2048'],
            'affiliate_url' => ['nullable', 'string', 'max:2048'],

            // Child relations
            'images' => ['nullable', 'array'],
            'images.*.image_url' => ['required', 'string', 'max:2048'],
            'images.*.alt_text' => ['nullable', 'string', 'max:255'],
            'images.*.sort_order' => ['nullable', 'integer', 'min:0'],

            'specifications' => ['nullable', 'array'],
            'specifications.*.name' => ['required', 'string', 'max:255'],
            'specifications.*.value' => ['required', 'string'],
            'specifications.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];

        if ($isActive === true) {
            // Resulting active: Category must exist, non-deleted, and active
            $rules['category_id'] = [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $category = Category::where('id', $value)->whereNull('deleted_at')->first();
                    if (!$category) {
                        $fail('Kategori yang dipilih tidak valid atau sudah dihapus.');
                    } elseif (!$category->is_active) {
                        $fail('Produk aktif wajib menggunakan kategori yang aktif.');
                    }
                },
            ];
            $rules['electronics_scope_confirmed'] = ['required', 'accepted'];
        } else {
            // Inactive/draft: Category is nullable, but if supplied must exist and non-deleted
            $rules['category_id'] = [
                'nullable',
                'integer',
                function ($attribute, $value, $fail) {
                    if (!empty($value)) {
                        $category = Category::where('id', $value)->whereNull('deleted_at')->first();
                        if (!$category) {
                            $fail('Kategori yang dipilih tidak valid atau sudah dihapus.');
                        }
                    }
                },
            ];
            $rules['electronics_scope_confirmed'] = ['nullable'];
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            // 1. Check description byte length (max 100,000 bytes)
            $rawDesc = $this->input('description_html');
            if ($rawDesc !== null && strlen($rawDesc) > HtmlSanitizer::MAX_RAW_BYTES) {
                $v->errors()->add('description_html', 'Deskripsi HTML melebihi batas maksimal ' . HtmlSanitizer::MAX_RAW_BYTES . ' byte.');
            }

            // 2. Validate external URLs with UrlValidator
            $urlValidator = app(UrlValidator::class);

            if ($this->filled('store_url')) {
                $error = null;
                if (!$urlValidator->validate($this->input('store_url'), 'marketplace', $error)) {
                    $v->errors()->add('store_url', $error ?: 'URL toko tidak valid.');
                }
            }

            if ($this->filled('affiliate_url')) {
                $error = null;
                if (!$urlValidator->validate($this->input('affiliate_url'), 'marketplace', $error)) {
                    $v->errors()->add('affiliate_url', $error ?: 'URL afiliasi kandidat tidak valid.');
                }
            }

            if ($this->filled('review_video_url')) {
                $error = null;
                if (!$urlValidator->validate($this->input('review_video_url'), 'video', $error)) {
                    $v->errors()->add('review_video_url', $error ?: 'URL video ulasan tidak valid.');
                }
            }

            // Validate image URLs
            $images = $this->input('images', []);
            if (is_array($images)) {
                foreach ($images as $index => $img) {
                    if (!empty($img['image_url'])) {
                        $error = null;
                        if (!$urlValidator->validate($img['image_url'], 'image', $error)) {
                            $v->errors()->add("images.{$index}.image_url", $error ?: 'URL gambar tidak valid.');
                        }
                    }
                }
            }

            // Check duplicate specification names
            $specs = $this->input('specifications', []);
            if (is_array($specs)) {
                $names = [];
                foreach ($specs as $index => $spec) {
                    if (!empty($spec['name'])) {
                        $lowerName = strtolower(trim($spec['name']));
                        if (in_array($lowerName, $names, true)) {
                            $v->errors()->add("specifications.{$index}.name", "Nama spesifikasi '{$spec['name']}' duplikat dalam produk ini.");
                        } else {
                            $names[] = $lowerName;
                        }
                    }
                }
            }
        });
    }

    /**
     * Whitelisted safe payload for product creation.
     * Blocks all server-owned fields completely.
     */
    public function safeProductData(): array
    {
        return [
            'name' => trim($this->input('name')),
            'slug' => trim($this->input('slug')),
            'category_id' => $this->input('category_id') ? (int) $this->input('category_id') : null,
            'is_active' => $this->boolean('is_active'),
            'label' => $this->filled('label') ? trim($this->input('label')) : null,
            'store_name' => $this->filled('store_name') ? trim($this->input('store_name')) : null,
            'store_url' => $this->filled('store_url') ? trim($this->input('store_url')) : null,
            'price_amount' => $this->filled('price_amount') ? $this->input('price_amount') : null,
            'price_currency' => $this->filled('price_currency') ? strtoupper(trim($this->input('price_currency'))) : null,
            'shopee_rating' => $this->filled('shopee_rating') ? $this->input('shopee_rating') : null,
            'shopee_review_count' => $this->filled('shopee_review_count') ? (int) $this->input('shopee_review_count') : null,
            'review_video_url' => $this->filled('review_video_url') ? trim($this->input('review_video_url')) : null,
            'affiliate_url' => $this->filled('affiliate_url') ? trim($this->input('affiliate_url')) : null,
            // marketplace is server-controlled 'shopee', not client-supplied
            'marketplace' => 'shopee',
            // affiliate status starts as pending for candidates
            'affiliate_url_status' => 'pending',
            'affiliate_url_provenance' => $this->filled('affiliate_url') ? 'internal_manual' : null,
            'affiliate_url_approved_by_admin_id' => null,
            'affiliate_url_approved_at' => null,
            'published_at' => null,
        ];
    }
}
