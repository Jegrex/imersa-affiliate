<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportBatch extends Model
{
    use HasFactory;

    protected $table = 'import_batches';

    public $timestamps = false;

    protected $fillable = [
        'source_name',
        'source_filename',
        'source_checksum_sha256',
        'source_storage_reference',
        'column_count',
        'row_count',
        'imported_at',
        'imported_by_admin_id',
        'notes',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'column_count' => 'integer',
            'row_count' => 'integer',
            'imported_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function importedBy()
    {
        return $this->belongsTo(Admin::class, 'imported_by_admin_id');
    }

    public function records()
    {
        return $this->hasMany(ImportRecord::class, 'import_batch_id');
    }
}
