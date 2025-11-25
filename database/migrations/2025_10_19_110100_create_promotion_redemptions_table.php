<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('promotion_redemptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('promotion_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('package_id')->nullable();
            $table->decimal('discount_amount', 12, 2);
            $table->string('discount_currency', 3);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('promotion_id')
                ->references('id')->on('promotions')
                ->onDelete('cascade');
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
            $table->foreign('package_id')
                ->references('id')->on('packages')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_redemptions');
    }
};