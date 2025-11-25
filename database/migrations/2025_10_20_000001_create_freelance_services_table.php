<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('freelance_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->json('subcategories')->nullable();
            $table->string('price_type')->default('fixed'); // fixed|hourly|package
            $table->decimal('price_value', 10, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->unsignedInteger('delivery_days')->nullable();
            $table->string('status')->default('pending'); // draft|pending|published|suspended
            $table->json('tags')->nullable();
            $table->json('photos')->nullable();
            $table->json('packages')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freelance_services');
    }
};