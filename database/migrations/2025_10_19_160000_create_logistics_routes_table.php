<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('logistics_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->string('from_country');
            $table->string('from_city')->nullable();
            $table->string('to_country');
            $table->string('to_city')->nullable();
            $table->enum('transport_type', ['road','air','sea','rail','warehousing','customs'])->default('road');
            $table->string('frequency')->nullable();
            $table->unsignedInteger('timeline_days')->nullable();
            $table->decimal('price_per_kg', 12, 2)->nullable();
            $table->decimal('price_per_ton', 12, 2)->nullable();
            $table->decimal('price_per_container', 12, 2)->nullable();
            $table->json('documents')->nullable();
            $table->text('conditions')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logistics_routes');
    }
};