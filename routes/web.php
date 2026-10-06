<?php

use App\Http\Controllers\Admin\AffiliateApprovalController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ProductReviewController;
use App\Http\Controllers\Affiliate\RedirectController;
use App\Http\Controllers\Public\CatalogController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - PT Imersa Solusi Teknologi (Imersa Affiliate)
|--------------------------------------------------------------------------
*/

// Public routes (Electronics MVP)
Route::get('/', HomeController::class)->name('home');
Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');
Route::post('/go/{product}', RedirectController::class)->name('affiliate.redirect');

// Admin Authentication (Guest)
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');

    // Admin Logout (Requires dedicated admin guard, cleans up even if admin became inactive)
    Route::post('/logout', [AuthController::class, 'destroy'])
        ->middleware('auth:admin')
        ->name('logout');

    // Protected Admin Routes (Requires authenticated and active admin)
    Route::middleware(['auth:admin', 'admin.active'])->group(function () {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');

        // Products Management
        Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
        Route::get('/products/create', [AdminProductController::class, 'create'])->name('products.create');
        Route::post('/products', [AdminProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [AdminProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [AdminProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');

        // Dedicated Affiliate URL Approval
        Route::post('/products/{product}/affiliate-approval', [AffiliateApprovalController::class, 'store'])->name('products.affiliate-approval.store');

        // Categories Management
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        // Reviews Management
        Route::get('/products/{product}/reviews', [ProductReviewController::class, 'index'])->name('reviews.index');
        Route::post('/products/{product}/reviews', [ProductReviewController::class, 'store'])->name('reviews.store');
        Route::put('/reviews/{review}', [ProductReviewController::class, 'update'])->name('reviews.update');
        Route::delete('/reviews/{review}', [ProductReviewController::class, 'destroy'])->name('reviews.destroy');

        // Analytics
        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    });
});
