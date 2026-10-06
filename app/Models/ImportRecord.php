<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportRecord extends Model
{
    use HasFactory;

    protected $table = 'import_records';

    public $timestamps = false;

    protected $fillable = [
        'import_batch_id',
        'source_row_number',
        'source_reference_no',
        'source_filename_slug',
        'raw_payload',
        'record_checksum_sha256',
        'validation_status',
        'validation_errors',
        'match_status',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'raw_payload' => 'array',
            'validation_errors' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function batch()
    {
        return $this->belongsTo(ImportBatch::class, 'import_batch_id');
    }

    public function importLink()
    {
        return $this->hasOne(ProductImportLink::class, 'import_record_id');
    }
}
