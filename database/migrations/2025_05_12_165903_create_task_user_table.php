<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('task_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('status')->default('sending'); // sending, viewed, in_progress, completed, approval_rejected, approved, rejected
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('completion_date')->nullable();
            $table->integer('approved_rejected')->default(0); // số lần duyệt hoặc từ chối
            $table->text('approved_rejected_reason')->nullable(); // lý do duyệt hoặc từ chối
            $table->foreignId('assigned_by')->nullable()->constrained('users');
            $table->timestamp('assigned_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            $table->unique(['task_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_user');
    }
};
