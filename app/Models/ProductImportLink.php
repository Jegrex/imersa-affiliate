<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImportLink extends Model
{
    use HasFactory;

    protected $table = 'product_import_links';

    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'import_record_id',
        'link_method',
        'linked_by_admin_id',
        'linked_at',
        'notes',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'linked_at' => 'datetime',
            'created_at' => 'datetime',
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

    public function linkedBy()
    {
        return $this->belongsTo(Admin::class, 'linked_by_admin_id');
    }
}
