<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('drilling_rigs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('name');
            $table->string('type')->nullable();
            $table->string('capacity')->nullable();
            $table->integer('year')->nullable();
            $table->string('serial')->nullable();
            $table->string('region')->nullable();
            $table->json('photos')->nullable();
            $table->json('passports')->nullable();
            $table->string('status')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('drilling_companies')->onDelete('cascade');
            $table->index(['company_id', 'type', 'region']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drilling_rigs');
    }
};