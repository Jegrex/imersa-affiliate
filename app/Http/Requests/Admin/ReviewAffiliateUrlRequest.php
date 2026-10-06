<?php

namespace App\Http\Requests\Admin;

use App\Support\UrlValidation\UrlValidator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ReviewAffiliateUrlRequest extends FormRequest
{
    public function authorize(): bool
    {
        $product = $this->route('product');
        return $this->user('admin') && $this->user('admin')->can('approveAffiliate', $product);
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'string', 'in:approve,reject'],
            'affiliate_url' => ['required_if:action,approve', 'nullable', 'string', 'max:2048'],
            'affiliate_url_provenance' => ['nullable', 'string', 'in:import_candidate,internal_manual,approved_other'],
            'affiliate_url_source_field' => ['nullable', 'string', 'max:64'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if ($this->input('action') === 'approve' && $this->filled('affiliate_url')) {
                $urlValidator = app(UrlValidator::class);
                $error = null;
                if (!$urlValidator->validate($this->input('affiliate_url'), 'marketplace', $error)) {
                    $v->errors()->add('affiliate_url', $error ?: 'URL afiliasi untuk approval tidak valid.');
                }
            }
        });
    }
}
