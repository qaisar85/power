<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedInteger('duration_days')->default(30);
            $table->decimal('price', 10, 2)->default(0);
            $table->enum('visibility_type', ['free', 'paid'])->default('free');
            $table->unsignedInteger('service_limit')->default(10);
            $table->enum('contact_visibility', ['hidden', 'request_only', 'visible'])->default('request_only');
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};