<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->foreignId('import_record_id')->nullable()->constrained('import_records')->onDelete('restrict');
            $table->enum('provenance', ['shopee_import', 'shopee_manual_reference']);
            $table->string('reviewer_name', 255)->nullable();
            $table->string('avatar_url', 2048)->nullable();
            $table->text('content')->nullable();
            $table->decimal('rating_value', 2, 1)->nullable();
            $table->dateTime('reviewed_at')->nullable();
            $table->string('source_reference', 255)->nullable();
            $table->boolean('is_visible')->default(false);
            $table->timestamps();

            $table->index(['product_id', 'is_visible', 'reviewed_at']);
            $table->index('import_record_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};
