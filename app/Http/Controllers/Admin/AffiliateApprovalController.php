<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\ReviewAffiliateUrlAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReviewAffiliateUrlRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class AffiliateApprovalController extends Controller
{
    public function store(ReviewAffiliateUrlRequest $request, Product $product, ReviewAffiliateUrlAction $action): RedirectResponse
    {
        $action->execute(
            $product,
            Auth::guard('admin')->user(),
            $request->input('action'),
            $request->input('affiliate_url'),
            $request->input('affiliate_url_provenance'),
            $request->input('affiliate_url_source_field')
        );

        $msg = $request->input('action') === 'approve'
            ? 'URL Afiliasi berhasil disetujui.'
            : 'URL Afiliasi berhasil ditolak.';

        return redirect()->route('admin.products.edit', $product)->with('success', $msg);
    }
}
