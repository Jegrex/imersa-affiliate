<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('restrict');
            $table->string('marketplace', 50)->default('shopee');
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->string('label', 100)->nullable();
            $table->string('store_name', 255)->nullable();
            $table->string('store_url', 2048)->nullable();
            $table->longText('description_html')->nullable();
            $table->longText('description_text')->nullable();
            $table->decimal('price_amount', 15, 2)->nullable();
            $table->char('price_currency', 3)->nullable();
            $table->decimal('shopee_rating', 3, 2)->nullable();
            $table->unsignedInteger('shopee_review_count')->nullable();
            $table->string('review_video_url', 2048)->nullable();
            $table->string('affiliate_url', 2048)->nullable();
            $table->enum('affiliate_url_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->enum('affiliate_url_provenance', ['import_candidate', 'internal_manual', 'approved_other'])->nullable();
            $table->string('affiliate_url_source_field', 64)->nullable();
            $table->foreignId('affiliate_url_approved_by_admin_id')->nullable()->constrained('admins')->onDelete('restrict');
            $table->dateTime('affiliate_url_approved_at')->nullable();
            $table->boolean('is_active')->default(false);
            $table->dateTime('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'marketplace', 'category_id', 'published_at'], 'products_catalog_index');
            $table->index('name');
            $table->index(['affiliate_url_status', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
