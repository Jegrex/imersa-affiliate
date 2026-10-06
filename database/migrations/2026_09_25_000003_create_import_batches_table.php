<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_batches', function (Blueprint $table) {
            $table->id();
            $table->string('source_name', 255);
            $table->string('source_filename', 512);
            $table->char('source_checksum_sha256', 64)->unique();
            $table->string('source_storage_reference', 1024)->nullable();
            $table->unsignedSmallInteger('column_count');
            $table->unsignedInteger('row_count');
            $table->dateTime('imported_at');
            $table->foreignId('imported_by_admin_id')->nullable()->constrained('admins')->onDelete('restrict');
            $table->text('notes')->nullable();
            $table->dateTime('created_at');

            $table->index('imported_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_batches');
    }
};
