<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSpecification extends Model
{
    use HasFactory;

    protected $table = 'product_specifications';

    protected $fillable = [
        'product_id',
        'import_record_id',
        'name',
        'value',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
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
}
