<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffiliateClick extends Model
{
    use HasFactory;

    protected $table = 'affiliate_clicks';

    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'clicked_at',
        'target_url_snapshot',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'clicked_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($click) {
            if (empty($click->created_at)) {
                $click->created_at = Carbon::now('UTC');
            }
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
