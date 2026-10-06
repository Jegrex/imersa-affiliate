<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->foreignId('import_record_id')->nullable()->constrained('import_records')->onDelete('restrict');
            $table->string('image_url', 2048);
            $table->string('alt_text', 255)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['product_id', 'sort_order']);
            $table->index('import_record_id');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE product_images ADD UNIQUE KEY product_images_product_id_image_url_unique (product_id, image_url(255))');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};
