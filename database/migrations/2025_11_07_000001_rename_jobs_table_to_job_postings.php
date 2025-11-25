<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('jobs', 'job_postings');
    }

    public function down(): void
    {
        Schema::rename('job_postings', 'jobs');
    }
};
