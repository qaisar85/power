<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('tenders')) return;

        Schema::table('tenders', function (Blueprint $table) {
            if (!Schema::hasColumn('tenders', 'company_id')) $table->unsignedBigInteger('company_id')->nullable()->after('id');
            if (!Schema::hasColumn('tenders', 'category')) $table->string('category')->nullable()->after('title');
            if (!Schema::hasColumn('tenders', 'subcategory')) $table->string('subcategory')->nullable()->after('category');
            if (!Schema::hasColumn('tenders', 'country')) $table->string('country')->nullable()->after('description');
            if (!Schema::hasColumn('tenders', 'region')) $table->string('region')->nullable()->after('country');
            if (!Schema::hasColumn('tenders', 'city')) $table->string('city')->nullable()->after('region');
            if (!Schema::hasColumn('tenders', 'budget_min')) $table->decimal('budget_min', 18, 2)->nullable()->after('city');
            if (!Schema::hasColumn('tenders', 'budget_max')) $table->decimal('budget_max', 18, 2)->nullable()->after('budget_min');
            if (!Schema::hasColumn('tenders', 'currency')) $table->string('currency', 8)->default('USD')->after('budget_max');
            if (!Schema::hasColumn('tenders', 'deadline_at')) $table->timestamp('deadline_at')->nullable()->after('currency');
            if (!Schema::hasColumn('tenders', 'visibility')) $table->enum('visibility', ['public','link','private'])->default('public')->after('deadline_at');
            if (!Schema::hasColumn('tenders', 'attachments')) $table->json('attachments')->nullable()->after('visibility');
            if (!Schema::hasColumn('tenders', 'options')) $table->json('options')->nullable()->after('attachments');
            if (!Schema::hasColumn('tenders', 'link_token')) $table->string('link_token')->nullable()->after('options');
            if (!Schema::hasColumn('tenders', 'status')) $table->enum('status', ['draft','pending','published','closed','archived'])->default('pending')->after('location');
        });
    }

    public function down(): void
    {
        // No-op: we won't drop added columns to avoid data loss in production.
    }
};