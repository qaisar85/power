<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('drilling_cases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('rig_id')->nullable();
            $table->string('title');
            $table->string('client')->nullable();
            $table->string('region')->nullable();
            $table->string('method')->nullable();
            $table->unsignedInteger('depth')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->default('completed');
            $table->text('summary')->nullable();
            $table->json('photos')->nullable();
            $table->json('documents')->nullable();
            $table->json('metrics')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('drilling_companies')->onDelete('cascade');
            $table->foreign('rig_id')->references('id')->on('drilling_rigs')->onDelete('set null');

            $table->index(['company_id', 'region', 'method', 'status']);
            $table->index(['start_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drilling_cases');
    }
};