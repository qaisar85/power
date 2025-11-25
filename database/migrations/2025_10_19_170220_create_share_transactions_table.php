<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('share_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('type'); // buy or sell
            $table->unsignedBigInteger('shares');
            $table->decimal('price_per_share', 18, 6);
            $table->decimal('amount', 18, 2);
            $table->string('status')->default('pending'); // pending, confirmed, cancelled
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->string('certificate_path')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('share_transactions');
    }
};