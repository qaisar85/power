<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('drilling_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('type');
            $table->string('method')->nullable();
            $table->unsignedInteger('depth')->nullable();
            $table->string('region')->nullable();
            $table->json('certificates')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->string('currency', 3)->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('drilling_companies')->onDelete('cascade');
            $table->index(['type', 'region']);
            $table->index('depth');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drilling_services');
    }
};