<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Agent Services - Track services provided by regional agents
        Schema::create('agent_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('regional_agents')->onDelete('cascade');
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('listing_id')->nullable()->constrained()->onDelete('set null');
            
            $table->string('service_type'); // 'listing_support', 'verification', 'consultation', 'logistics'
            $table->text('description')->nullable();
            
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('USD');
            
            $table->enum('status', ['requested', 'accepted', 'in_progress', 'completed', 'cancelled'])->default('requested');
            
            $table->decimal('commission_amount', 10, 2)->default(0.00);
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            $table->integer('rating')->nullable(); // 1-5
            $table->text('review')->nullable();
            
            $table->timestamps();
            
            $table->index(['agent_id', 'status']);
            $table->index(['company_id', 'status']);
        });

        // Revenue Shares - Track revenue sharing for demo/commission-based listings
        Schema::create('revenue_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Seller
            $table->foreignId('agent_id')->nullable()->constrained('regional_agents')->onDelete('set null');
            
            $table->enum('share_type', ['package', 'commission', 'demo_percentage'])->default('package');
            $table->decimal('percentage', 5, 2)->nullable(); // For percentage-based
            $table->decimal('amount_earned', 12, 2)->default(0.00);
            $table->decimal('platform_fee', 12, 2)->default(0.00);
            $table->decimal('agent_commission', 12, 2)->default(0.00);
            
            $table->enum('status', ['pending', 'processing', 'paid', 'failed'])->default('pending');
            $table->timestamp('payment_date')->nullable();
            
            $table->string('payment_reference')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['listing_id', 'status']);
        });

        // Sales Transactions - Complete transaction tracking with all parties
        Schema::create('sales_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();
            
            // Parties involved
            $table->foreignId('listing_id')->constrained()->onDelete('cascade');
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('agent_id')->nullable()->constrained('regional_agents')->onDelete('set null');
            
            // Transaction amounts
            $table->decimal('total_amount', 12, 2);
            $table->string('currency', 3)->default('USD');
            $table->decimal('platform_fee', 12, 2)->default(0.00);
            $table->decimal('agent_commission', 12, 2)->default(0.00);
            $table->decimal('seller_amount', 12, 2);
            
            // Payment details
            $table->string('payment_method'); // 'card', 'wallet', 'bank_transfer', 'paypal'
            $table->string('payment_provider')->nullable(); // 'stripe', 'paypal', etc.
            $table->string('payment_reference')->nullable();
            $table->enum('payment_status', ['pending', 'processing', 'completed', 'failed', 'refunded'])->default('pending');
            
            // Distribution status
            $table->boolean('platform_fee_collected')->default(false);
            $table->boolean('agent_paid')->default(false);
            $table->boolean('seller_paid')->default(false);
            
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            $table->index(['buyer_id', 'payment_status']);
            $table->index(['seller_id', 'payment_status']);
            $table->index(['agent_id', 'payment_status']);
            $table->index('transaction_id');
        });

        // Agent Reviews - Separate reviews for agent performance
        Schema::create('agent_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('regional_agents')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Reviewer
            $table->foreignId('agent_service_id')->nullable()->constrained()->onDelete('set null');
            
            $table->integer('rating'); // 1-5
            $table->text('comment')->nullable();
            
            // Rating breakdown
            $table->integer('communication_rating')->nullable();
            $table->integer('professionalism_rating')->nullable();
            $table->integer('response_time_rating')->nullable();
            $table->integer('quality_rating')->nullable();
            
            $table->boolean('is_verified')->default(false); // Verified purchase/service
            $table->boolean('is_visible')->default(true);
            
            $table->timestamps();
            
            $table->index(['agent_id', 'is_visible']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_reviews');
        Schema::dropIfExists('sales_transactions');
        Schema::dropIfExists('revenue_shares');
        Schema::dropIfExists('agent_services');
    }
};
