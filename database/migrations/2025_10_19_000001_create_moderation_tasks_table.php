<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('moderation_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('item_type', 64);
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('submitted_by');
            $table->enum('status', ['pending', 'approved', 'declined', 'revision_requested'])->default('pending');
            $table->unsignedBigInteger('moderator_id')->nullable();
            $table->text('comment')->nullable();
            $table->unsignedTinyInteger('priority')->default(0);
            $table->string('country', 2)->nullable();
            $table->string('region', 64)->nullable();
            $table->timestamps();

            $table->index(['item_type', 'status', 'priority']);
        });

        Schema::table('moderation_tasks', function (Blueprint $table) {
            // Foreign keys
            $table->foreign('submitted_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('moderator_id')->references('id')->on('admins')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('moderation_tasks', function (Blueprint $table) {
            $table->dropForeign(['submitted_by']);
            $table->dropForeign(['moderator_id']);
        });
        Schema::dropIfExists('moderation_tasks');
    }
};