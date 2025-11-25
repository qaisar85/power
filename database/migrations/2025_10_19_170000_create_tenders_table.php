<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('tenders')) {
            // Table already exists; skip creation.
            return;
        }

        Schema::create('tenders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable(); // Customer company
            $table->unsignedBigInteger('user_id')->nullable();    // Fallback to user if no company
            $table->string('title');
            $table->string('category')->nullable();
            $table->string('subcategory')->nullable();
            $table->text('description');
            $table->string('country')->nullable();
            $table->string('region')->nullable();
            $table->string('city')->nullable();
            $table->decimal('budget_min', 18, 2)->nullable();
            $table->decimal('budget_max', 18, 2)->nullable();
            $table->string('currency', 8)->default('USD');
            $table->timestamp('deadline_at')->nullable();
            $table->enum('visibility', ['public', 'link', 'private'])->default('public');
            $table->enum('status', ['draft', 'pending', 'published', 'closed', 'archived'])->default('pending');
            $table->json('attachments')->nullable(); // [{name, path, size, mime}]
            $table->json('options')->nullable();     // extra flags (nda_required, lots_enabled, etc.)
            $table->string('link_token')->nullable(); // token for link visibility
            $table->timestamps();

            $table->index(['status', 'deadline_at']);
            $table->index(['country', 'city']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenders');
    }
};