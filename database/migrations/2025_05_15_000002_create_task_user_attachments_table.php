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
        Schema::create('task_user_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_user_id')->constrained('task_user')->onDelete('cascade');
            $table->string('filename');
            $table->string('original_filename');
            $table->string('file_path');
            $table->string('file_type');
            $table->bigInteger('file_size');
            $table->foreignId('uploaded_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_user_attachments');
    }
}; 