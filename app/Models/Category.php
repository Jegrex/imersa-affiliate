<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'categories';

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'is_active',
        'sort_order',
        'created_by_admin_id',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public function activeProducts()
    {
        return $this->hasMany(Product::class, 'category_id')->where('is_active', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
