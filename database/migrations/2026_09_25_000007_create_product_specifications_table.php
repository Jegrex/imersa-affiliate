<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_specifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->foreignId('import_record_id')->nullable()->constrained('import_records')->onDelete('restrict');
            $table->string('name', 255);
            $table->text('value');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'name']);
            $table->index(['product_id', 'sort_order']);
            $table->index('import_record_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_specifications');
    }
};
