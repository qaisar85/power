<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('type', ['percent', 'amount']);
            $table->decimal('value', 10, 2); // percent or fixed amount
            $table->string('currency', 3)->nullable(); // only used if type=amount
            $table->unsignedBigInteger('applies_to_package_id')->nullable();
            $table->integer('max_uses')->nullable();
            $table->integer('times_used')->default(0);
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_to')->nullable();
            $table->boolean('active')->default(true);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('applies_to_package_id')
                ->references('id')->on('packages')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};