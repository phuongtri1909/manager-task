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
        Schema::table('task_user', function (Blueprint $table) {
            $table->integer('completion_attempt')->default(0)->after('completion_date');
        });

        Schema::table('task_user_attachments', function (Blueprint $table) {
            $table->integer('completion_attempt')->default(1)->after('uploaded_by');
            $table->string('description')->nullable()->after('completion_attempt');
            $table->boolean('is_active')->default(true)->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_user', function (Blueprint $table) {
            $table->dropColumn('completion_attempt');
        });

        Schema::table('task_user_attachments', function (Blueprint $table) {
            $table->dropColumn(['completion_attempt', 'description', 'is_active']);
        });
    }
};
