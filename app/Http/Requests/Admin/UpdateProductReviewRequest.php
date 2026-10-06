<?php

namespace App\Http\Requests\Admin;

use App\Models\ProductReview;
use App\Support\UrlValidation\UrlValidator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateProductReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        $review = $this->route('review');
        return $this->user('admin') && $this->user('admin')->can('update', $review);
    }

    public function rules(): array
    {
        return [
            'provenance' => ['required', 'string', 'in:shopee_import,shopee_manual_reference'],
            'reviewer_name' => ['nullable', 'string', 'max:255'],
            'avatar_url' => ['nullable', 'string', 'max:2048'],
            'content' => ['nullable', 'string'],
            'rating_value' => ['nullable', 'numeric', 'min:1', 'max:5'],
            'reviewed_at' => ['nullable', 'date'],
            'source_reference' => ['nullable', 'string', 'max:255'],
            'is_visible' => ['required', 'boolean'],
            'images' => ['nullable', 'array'],
            'images.*.image_url' => ['required', 'string', 'max:2048'],
            'images.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $urlValidator = app(UrlValidator::class);

            if ($this->filled('avatar_url')) {
                $error = null;
                if (!$urlValidator->validate($this->input('avatar_url'), 'image', $error)) {
                    $v->errors()->add('avatar_url', $error ?: 'URL avatar tidak valid.');
                }
            }

            $images = $this->input('images', []);
            if (is_array($images)) {
                foreach ($images as $index => $img) {
                    if (!empty($img['image_url'])) {
                        $error = null;
                        if (!$urlValidator->validate($img['image_url'], 'image', $error)) {
                            $v->errors()->add("images.{$index}.image_url", $error ?: 'URL foto ulasan tidak valid.');
                        }
                    }
                }
            }
        });
    }
}
