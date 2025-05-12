<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCdgdx04sTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cdgdx04s', function (Blueprint $table) {
            $table->tinyText('file_second')->nullable();
            $table->tinyText('file_third')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cdgdx04s', function (Blueprint $table) {
            $table->dropColumn('file_second');
            $table->dropColumn('file_third');
        });
    }
}
