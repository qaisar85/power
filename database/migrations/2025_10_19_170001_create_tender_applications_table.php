<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tender_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tender_id');
            $table->unsignedBigInteger('supplier_company_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->decimal('price', 18, 2)->nullable();
            $table->string('currency', 8)->default('USD');
            $table->integer('deadline_days')->nullable();
            $table->text('comment')->nullable();
            $table->json('files')->nullable(); // [{name, path, size, mime}]
            $table->enum('status', ['submitted', 'accepted', 'rejected', 'revised'])->default('submitted');
            $table->timestamps();

            $table->index(['tender_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tender_applications');
    }
};