<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('freelance_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->unique();
            $table->unsignedBigInteger('service_id');
            $table->unsignedBigInteger('buyer_id');
            $table->unsignedBigInteger('seller_id');
            $table->string('package')->nullable();
            $table->decimal('amount', 12, 2);
            $table->decimal('platform_fee', 12, 2)->default(0);
            $table->decimal('seller_amount', 12, 2)->default(0);
            $table->string('currency', 8)->default('USD');
            $table->string('status')->default('pending');
            $table->string('payment_reference')->nullable();
            $table->string('payment_provider')->nullable();
            $table->text('refund_reason')->nullable();
            $table->timestamps();

            $table->index(['buyer_id','seller_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freelance_orders');
    }
};
