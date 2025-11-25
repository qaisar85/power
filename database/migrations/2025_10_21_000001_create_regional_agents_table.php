<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regional_agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Region coverage
            $table->enum('region_type', ['global', 'country', 'state', 'city'])->default('city');
            $table->foreignId('country_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('state_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('city_id')->nullable()->constrained()->onDelete('cascade');
            
            // Commission and rates
            $table->decimal('commission_rate', 5, 2)->default(10.00); // Percentage
            $table->decimal('service_fee', 10, 2)->nullable(); // Fixed fee per service
            
            // Service offerings
            $table->json('service_types')->nullable(); // ['equipment_listing', 'consultation', 'verification', 'logistics']
            $table->json('supported_categories')->nullable(); // Which business sectors/categories they cover
            
            // Status and verification
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->references('id')->on('admins');
            
            // Performance metrics
            $table->decimal('performance_rating', 3, 2)->default(0.00); // 0-5 rating
            $table->integer('total_services_completed')->default(0);
            $table->integer('total_clients_served')->default(0);
            $table->decimal('total_revenue_generated', 12, 2)->default(0.00);
            
            // Contact and business info
            $table->string('business_name')->nullable();
            $table->text('business_description')->nullable();
            $table->string('business_license')->nullable();
            $table->json('certifications')->nullable();
            $table->json('languages')->nullable(); // Languages spoken
            
            // Availability
            $table->json('working_hours')->nullable();
            $table->string('timezone')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['country_id', 'is_active']);
            $table->index(['state_id', 'is_active']);
            $table->index(['city_id', 'is_active']);
            $table->index(['is_verified', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regional_agents');
    }
};
