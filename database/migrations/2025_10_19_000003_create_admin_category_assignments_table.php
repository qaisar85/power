<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('admin_category_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->unsignedBigInteger('subsector_id')->nullable();
            $table->string('category_code', 64)->nullable();
            $table->timestamps();

            $table->index(['admin_id']);
            $table->index(['subsector_id']);
            $table->index(['category_code']);
        });

        Schema::table('admin_category_assignments', function (Blueprint $table) {
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            if (Schema::hasTable('business_subsectors')) {
                $table->foreign('subsector_id')->references('id')->on('business_subsectors')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('admin_category_assignments', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            if (Schema::hasColumn('admin_category_assignments', 'subsector_id')) {
                $table->dropForeign(['subsector_id']);
            }
        });
        Schema::dropIfExists('admin_category_assignments');
    }
};