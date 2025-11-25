<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('drilling_companies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->string('logo')->nullable();
            $table->text('history')->nullable();
            $table->string('region')->nullable();
            $table->boolean('verified')->default(false);
            $table->unsignedBigInteger('tariff_id')->nullable();
            $table->json('contacts')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('region');
            $table->index('tariff_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drilling_companies');
    }
};