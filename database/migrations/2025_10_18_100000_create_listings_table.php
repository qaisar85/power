<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('role')->nullable(); // seller/company, logistics, journalist, employer, agent
            $table->string('type')->default('product'); // product, service, vacancy, news, tender, auction
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('photos')->nullable();
            $table->json('documents')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->string('currency', 8)->nullable();
            $table->string('status')->default('under_review'); // draft, under_review, published, rejected
            $table->string('location')->nullable();
            $table->string('deal_type')->default('sale'); // sale, rent, auction
            $table->json('payment_options')->nullable();
            $table->string('category')->nullable();
            $table->json('subcategories')->nullable();
            // Multi-section toggles + fields
            $table->boolean('publish_in_rent')->default(false);
            $table->boolean('publish_in_auction')->default(false);
            $table->json('rent_fields')->nullable();
            $table->json('auction_fields')->nullable();
            // Additional role-specific fields (e.g., logistics)
            $table->json('logistics_fields')->nullable();
            // Preview/comment and package selection
            $table->text('preview_comment')->nullable();
            $table->string('package')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};