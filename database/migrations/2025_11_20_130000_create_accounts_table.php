<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedTinyInteger('level');
            $table->string('id_type')->nullable();
            $table->string('root_category')->nullable();
            $table->timestamps();
            $table->index(['parent_id','level']);
            $table->index('root_category');
        });

        DB::table('accounts')->insert([
            ['code' => 'A', 'name' => 'Asset', 'parent_id' => null, 'level' => 1, 'id_type' => 'alphabetical', 'root_category' => 'Asset', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'L', 'name' => 'Liability', 'parent_id' => null, 'level' => 1, 'id_type' => 'alphabetical', 'root_category' => 'Liability', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'C', 'name' => 'Capital', 'parent_id' => null, 'level' => 1, 'id_type' => 'alphabetical', 'root_category' => 'Capital', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'R', 'name' => 'Revenue', 'parent_id' => null, 'level' => 1, 'id_type' => 'alphabetical', 'root_category' => 'Revenue', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'E', 'name' => 'Expense', 'parent_id' => null, 'level' => 1, 'id_type' => 'alphabetical', 'root_category' => 'Expense', 'created_at' => now(), 'updated_at' => now()],
            ['code' => '11', 'name' => 'Asset', 'parent_id' => null, 'level' => 1, 'id_type' => 'numeric', 'root_category' => 'Asset', 'created_at' => now(), 'updated_at' => now()],
            ['code' => '21', 'name' => 'Liability', 'parent_id' => null, 'level' => 1, 'id_type' => 'numeric', 'root_category' => 'Liability', 'created_at' => now(), 'updated_at' => now()],
            ['code' => '31', 'name' => 'Capital', 'parent_id' => null, 'level' => 1, 'id_type' => 'numeric', 'root_category' => 'Capital', 'created_at' => now(), 'updated_at' => now()],
            ['code' => '41', 'name' => 'Revenue', 'parent_id' => null, 'level' => 1, 'id_type' => 'numeric', 'root_category' => 'Revenue', 'created_at' => now(), 'updated_at' => now()],
            ['code' => '51', 'name' => 'Expense', 'parent_id' => null, 'level' => 1, 'id_type' => 'numeric', 'root_category' => 'Expense', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};

