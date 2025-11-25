<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payment_methods')) {
            // Add unique index on type if it doesn't already exist
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = array_map(function ($idx) { return $idx->getName(); }, $sm->listTableIndexes('payment_methods'));

            // Use a fixed index name
            $indexName = 'payment_methods_type_unique';
            if (!in_array($indexName, $indexes)) {
                Schema::table('payment_methods', function (Blueprint $table) use ($indexName) {
                    $table->unique('type', $indexName);
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('payment_methods')) {
            Schema::table('payment_methods', function (Blueprint $table) {
                $table->dropUnique('payment_methods_type_unique');
            });
        }
    }
};
