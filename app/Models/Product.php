<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'products';

    protected $fillable = [
        'category_id',
        'marketplace',
        'name',
        'slug',
        'label',
        'store_name',
        'store_url',
        'description_html',
        'description_text',
        'price_amount',
        'price_currency',
        'shopee_rating',
        'shopee_review_count',
        'review_video_url',
        'affiliate_url',
        'affiliate_url_status',
        'affiliate_url_provenance',
        'affiliate_url_source_field',
        'affiliate_url_approved_by_admin_id',
        'affiliate_url_approved_at',
        'is_active',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'price_amount' => 'decimal:2',
            'shopee_rating' => 'decimal:2',
            'shopee_review_count' => 'integer',
            'affiliate_url_approved_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function approvedByAdmin()
    {
        return $this->belongsTo(Admin::class, 'affiliate_url_approved_by_admin_id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id')->orderBy('sort_order');
    }

    public function specifications()
    {
        return $this->hasMany(ProductSpecification::class, 'product_id')->orderBy('sort_order');
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class, 'product_id');
    }

    public function visibleReviews()
    {
        return $this->hasMany(ProductReview::class, 'product_id')
            ->where('is_visible', true)
            ->with('images')
            ->orderByDesc('reviewed_at');
    }

    public function clicks()
    {
        return $this->hasMany(AffiliateClick::class, 'product_id');
    }

    public function importLinks()
    {
        return $this->hasMany(ProductImportLink::class, 'product_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCatalogVisible($query)
    {
        return $query->where('is_active', true)
            ->whereHas('category', function ($q) {
                $q->where('is_active', true);
            });
    }

    public function isCtaEligible(): bool
    {
        return $this->is_active
            && $this->affiliate_url_status === 'approved'
            && !empty($this->affiliate_url)
            && !is_null($this->affiliate_url_approved_by_admin_id)
            && !is_null($this->affiliate_url_approved_at);
    }
}
