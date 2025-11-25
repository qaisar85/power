<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hse_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('title');
            $table->string('type');
            $table->string('file');
            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->boolean('verified')->default(false);
            $table->string('region')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('drilling_companies')->onDelete('cascade');
            $table->index(['company_id', 'type', 'verified']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hse_documents');
    }
};