<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->string('subcategory')->nullable();
            $table->enum('service_type', ['repair','inspection','calibration','diagnostics','ndt','other'])->default('repair');
            $table->enum('price_type', ['fixed','range','hourly','formula'])->default('fixed');
            $table->decimal('price_value', 12, 2)->nullable();
            $table->string('currency', 8)->default('USD');
            $table->string('price_details')->nullable();
            $table->json('geo')->nullable();
            $table->json('files')->nullable(); // photos, videos, pdfs
            $table->enum('placement_type', ['free','paid'])->default('free');
            $table->enum('visibility', ['pending','published','hidden'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};