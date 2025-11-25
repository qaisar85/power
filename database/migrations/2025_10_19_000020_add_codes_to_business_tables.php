<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_sectors', function (Blueprint $table) {
            $table->string('standard', 16)->nullable()->after('icon');
            $table->string('code', 32)->nullable()->after('standard');
            $table->unique(['standard', 'code'], 'business_sectors_standard_code_unique');
        });

        Schema::table('business_subsectors', function (Blueprint $table) {
            $table->string('standard', 16)->nullable()->after('slug');
            $table->string('code', 32)->nullable()->after('standard');
            $table->unique(['sector_id', 'standard', 'code'], 'business_subsectors_sector_standard_code_unique');
        });
    }

    public function down(): void
    {
        Schema::table('business_subsectors', function (Blueprint $table) {
            $table->dropUnique('business_subsectors_sector_standard_code_unique');
            $table->dropColumn(['standard', 'code']);
        });

        Schema::table('business_sectors', function (Blueprint $table) {
            $table->dropUnique('business_sectors_standard_code_unique');
            $table->dropColumn(['standard', 'code']);
        });
    }
};