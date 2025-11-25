<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('regional_agents', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable()->after('city_id');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->string('logo')->nullable()->after('business_description');
            $table->string('video_resume_url')->nullable()->after('logo');
            $table->text('office_address')->nullable()->after('video_resume_url');
            $table->string('office_phone')->nullable()->after('office_address');
            $table->string('office_email')->nullable()->after('office_phone');
            $table->json('office_hours')->nullable()->after('office_email');
            
            // Index for geolocation queries
            $table->index(['latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('regional_agents', function (Blueprint $table) {
            $table->dropIndex(['latitude', 'longitude']);
            $table->dropColumn([
                'latitude',
                'longitude',
                'logo',
                'video_resume_url',
                'office_address',
                'office_phone',
                'office_email',
                'office_hours',
            ]);
        });
    }
};
