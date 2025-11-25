<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('drilling_cases', function (Blueprint $table) {
            $table->boolean('verified')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('drilling_cases', function (Blueprint $table) {
            $table->dropColumn('verified');
        });
    }
};