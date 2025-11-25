<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('wallet_topup_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('provider'); // 'paypal' or 'stripe'
            $table->string('provider_order_id')->index();
            $table->string('provider_session_id')->nullable();
            $table->string('status')->default('created'); // created, approved, completed, canceled, failed
            $table->decimal('amount_native', 12, 2)->default(0);
            $table->string('currency_native', 3)->default('USD');
            $table->decimal('amount_usd', 12, 2)->default(0);
            $table->string('capture_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['provider', 'provider_order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_topup_orders');
    }
};