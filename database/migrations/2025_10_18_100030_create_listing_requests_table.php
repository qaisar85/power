<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listing_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained('listings')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['contact', 'inspection', 'shipping', 'subscribe']);
            $table->text('message')->nullable();
            $table->json('meta')->nullable();
            $table->string('status')->default('new');
            $table->timestamps();
            $table->index(['listing_id', 'user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_requests');
    }
};