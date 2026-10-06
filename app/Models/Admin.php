<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'admins';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function categories()
    {
        return $this->hasMany(Category::class, 'created_by_admin_id');
    }

    public function approvedProducts()
    {
        return $this->hasMany(Product::class, 'affiliate_url_approved_by_admin_id');
    }

    public function importBatches()
    {
        return $this->hasMany(ImportBatch::class, 'imported_by_admin_id');
    }

    public function importLinks()
    {
        return $this->hasMany(ProductImportLink::class, 'linked_by_admin_id');
    }
}
