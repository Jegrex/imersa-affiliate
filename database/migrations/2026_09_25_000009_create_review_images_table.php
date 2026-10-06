<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_review_id')->constrained('product_reviews')->onDelete('restrict');
            $table->string('image_url', 2048);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['product_review_id', 'sort_order']);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE review_images ADD UNIQUE KEY review_images_review_id_image_url_unique (product_review_id, image_url(255))');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('review_images');
    }
};
