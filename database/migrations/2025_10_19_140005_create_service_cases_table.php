<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('service_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('equipment_type')->nullable();
            $table->string('location')->nullable();
            $table->json('files')->nullable(); // before/after photos, pdf report
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_cases');
    }
};