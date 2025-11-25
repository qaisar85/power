<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('moderation_tasks', function (Blueprint $table) {
            $table->string('category_code', 64)->nullable()->after('item_type');
            $table->unsignedBigInteger('subsector_id')->nullable()->after('category_code');
            $table->index(['category_code']);
            $table->index(['subsector_id']);
        });

        if (Schema::hasTable('business_subsectors')) {
            Schema::table('moderation_tasks', function (Blueprint $table) {
                $table->foreign('subsector_id')->references('id')->on('business_subsectors')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('moderation_tasks', function (Blueprint $table) {
            if (Schema::hasColumn('moderation_tasks', 'subsector_id')) {
                $table->dropForeign(['subsector_id']);
                $table->dropIndex(['subsector_id']);
                $table->dropColumn('subsector_id');
            }
            if (Schema::hasColumn('moderation_tasks', 'category_code')) {
                $table->dropIndex(['category_code']);
                $table->dropColumn('category_code');
            }
        });
    }
};