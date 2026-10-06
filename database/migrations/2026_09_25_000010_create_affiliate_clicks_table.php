<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->dateTime('clicked_at', 6);
            $table->string('target_url_snapshot', 2048);
            $table->dateTime('created_at', 6);

            $table->index(['product_id', 'clicked_at']);
            $table->index('clicked_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_clicks');
    }
};
