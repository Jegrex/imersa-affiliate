<?php

namespace App\Actions\Affiliate;

use App\Models\AffiliateClick;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RecordAffiliateClickAndRedirectAction
{
    /**
     * Record the affiliate click atomically and return the redirect response.
     * Invariant: Click must be committed to DB before redirect occurs.
     * Invariant: No IP or User Agent recorded.
     *
     * @param int|Product $product
     * @return RedirectResponse
     * @throws HttpException
     */
    public function execute($product): RedirectResponse
    {
        $productId = $product instanceof Product ? $product->id : (int) $product;

        // Preliminary read (outside lock)
        $initialProduct = Product::where('id', $productId)
            ->whereNull('deleted_at')
            ->first();

        if (!$initialProduct) {
            abort(404, 'Produk tidak ditemukan.');
        }

        if (!$initialProduct->is_active) {
            abort(400, 'Produk ini sedang tidak aktif.');
        }

        if ($initialProduct->affiliate_url_status !== 'approved' || empty($initialProduct->affiliate_url)) {
            abort(400, 'Link afiliasi untuk produk ini belum disetujui atau belum tersedia.');
        }

        $targetUrlSnapshot = null;

        // Begin transaction with lock
        DB::beginTransaction();

        try {
            /** @var Product|null $lockedProduct */
            $lockedProduct = Product::where('id', $productId)
                ->whereNull('deleted_at')
                ->lockForUpdate()
                ->first();

            if (!$lockedProduct || !$lockedProduct->is_active) {
                DB::rollBack();
                abort(400, 'Produk tidak aktif saat memproses pengalihan.');
            }

            // Verify category invariant
            if ($lockedProduct->category_id) {
                $category = DB::table('categories')
                    ->where('id', $lockedProduct->category_id)
                    ->whereNull('deleted_at')
                    ->where('is_active', true)
                    ->first();

                if (!$category) {
                    DB::rollBack();
                    abort(400, 'Kategori produk tidak aktif atau tidak valid.');
                }
            } else {
                DB::rollBack();
                abort(400, 'Produk aktif wajib memiliki kategori.');
            }

            // Final eligibility validation
            if (
                $lockedProduct->affiliate_url_status !== 'approved' ||
                empty($lockedProduct->affiliate_url) ||
                is_null($lockedProduct->affiliate_url_approved_by_admin_id) ||
                is_null($lockedProduct->affiliate_url_approved_at)
            ) {
                DB::rollBack();
                abort(400, 'Status afiliasi produk belum disetujui secara lengkap.');
            }

            $targetUrlSnapshot = $lockedProduct->affiliate_url;
            $now = Carbon::now('UTC')->format('Y-m-d H:i:s.u');

            // Strictly append-only record with NO IP and NO User Agent
            AffiliateClick::create([
                'product_id' => $lockedProduct->id,
                'clicked_at' => $now,
                'target_url_snapshot' => $targetUrlSnapshot,
                'created_at' => $now,
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            if ($e instanceof HttpException) {
                throw $e;
            }
            abort(500, 'Gagal mencatat tautan afiliasi. Silakan coba kembali.');
        }

        // Redirect happens strictly AFTER successful commit
        return redirect()->away($targetUrlSnapshot);
    }
}
