<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('email', 255)->unique();
            $table->string('password', 255);
            $table->string('role', 50)->default('admin');
            $table->boolean('is_active')->default(true);
            $table->dateTime('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index(['is_active', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
