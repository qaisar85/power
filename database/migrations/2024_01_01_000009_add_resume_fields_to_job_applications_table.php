<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->string('currency', 3)->default('USD');
            $table->date('availability_date')->nullable();
            $table->string('notice_period')->nullable();
            
            // Personal Information
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone');
            $table->text('address');
            $table->string('city');
            $table->string('country');
            $table->string('postal_code')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('nationality')->nullable();
            
            // Resume Data (JSON fields)
            $table->json('education');
            $table->json('experience')->nullable();
            $table->json('skills')->nullable();
            $table->json('languages')->nullable();
            $table->json('certifications')->nullable();
        });
    }

    public function down()
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn([
                'currency', 'availability_date', 'notice_period',
                'first_name', 'last_name', 'email', 'phone', 'address',
                'city', 'country', 'postal_code', 'date_of_birth', 'nationality',
                'education', 'experience', 'skills', 'languages', 'certifications'
            ]);
        });
    }
};