<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_import_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->foreignId('import_record_id')->unique()->constrained('import_records')->onDelete('restrict');
            $table->enum('link_method', ['created_from_record', 'manual_approved_match', 'approved_source_key_match']);
            $table->foreignId('linked_by_admin_id')->nullable()->constrained('admins')->onDelete('restrict');
            $table->dateTime('linked_at');
            $table->text('notes')->nullable();
            $table->dateTime('created_at');

            $table->unique(['product_id', 'import_record_id']);
            $table->index(['product_id', 'linked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_import_links');
    }
};
