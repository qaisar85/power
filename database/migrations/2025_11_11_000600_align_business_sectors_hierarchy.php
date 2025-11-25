<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ensure hierarchical columns exist on business_sectors
        Schema::table('business_sectors', function (Blueprint $table) {
            if (!Schema::hasColumn('business_sectors', 'parent_id')) {
                $table->foreignId('parent_id')->nullable()->constrained('business_sectors')->onDelete('cascade');
            }
            if (!Schema::hasColumn('business_sectors', 'level')) {
                $table->integer('level')->default(1); // 1=sector, 2=sub-sector, 3=sub-sub-sector
            }
            if (!Schema::hasColumn('business_sectors', 'sort_order')) {
                $table->integer('sort_order')->default(0);
            }
            if (!Schema::hasColumn('business_sectors', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
        });
    }

    public function down(): void
    {
        Schema::table('business_sectors', function (Blueprint $table) {
            if (Schema::hasColumn('business_sectors', 'parent_id')) {
                $table->dropForeign(['parent_id']);
                $table->dropColumn('parent_id');
            }
            if (Schema::hasColumn('business_sectors', 'level')) {
                $table->dropColumn('level');
            }
            if (Schema::hasColumn('business_sectors', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
            if (Schema::hasColumn('business_sectors', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};