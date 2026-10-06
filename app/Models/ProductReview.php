<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductReview extends Model
{
    use HasFactory;

    protected $table = 'product_reviews';

    protected $fillable = [
        'product_id',
        'import_record_id',
        'provenance',
        'reviewer_name',
        'avatar_url',
        'content',
        'rating_value',
        'reviewed_at',
        'source_reference',
        'is_visible',
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'rating_value' => 'decimal:1',
            'reviewed_at' => 'datetime',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function importRecord()
    {
        return $this->belongsTo(ImportRecord::class, 'import_record_id');
    }

    public function images()
    {
        return $this->hasMany(ReviewImage::class, 'product_review_id')->orderBy('sort_order');
    }
}
