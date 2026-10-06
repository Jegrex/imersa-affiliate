<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_batch_id')->constrained('import_batches')->onDelete('restrict');
            $table->unsignedInteger('source_row_number');
            $table->string('source_reference_no', 100)->nullable();
            $table->string('source_filename_slug', 512)->nullable();
            $table->json('raw_payload');
            $table->char('record_checksum_sha256', 64);
            $table->enum('validation_status', ['pending', 'valid', 'warning', 'invalid', 'skipped'])->default('pending');
            $table->json('validation_errors')->nullable();
            $table->enum('match_status', ['unprocessed', 'new', 'exact_source_match', 'candidate_match', 'conflict', 'rejected'])->default('unprocessed');
            $table->dateTime('created_at');

            $table->unique(['import_batch_id', 'source_row_number']);
            $table->index(['import_batch_id', 'validation_status']);
            $table->index(['import_batch_id', 'match_status']);
            $table->index('source_reference_no');
            $table->index('source_filename_slug');
            $table->index('record_checksum_sha256');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_records');
    }
};
