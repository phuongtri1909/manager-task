<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeStringDatatypeInPartyMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('party_members', function (Blueprint $table) {
            $table->dropColumn('joining_date');
            $table->dropColumn('union_joining_date');
            $table->dropColumn('recognition_date');
        });

        Schema::table('party_members', function (Blueprint $table) {
            $table->date('joining_date');
            $table->date('union_joining_date');
            $table->date('recognition_date');
            $table->foreignId('user_id')->after('id')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('party_members', function (Blueprint $table) {
            $table->dropForeign('user_id');
            $table->dropColumn('user_id');
        });
    }
}
