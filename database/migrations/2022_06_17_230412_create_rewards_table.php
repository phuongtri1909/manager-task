<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRewardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->nullable()->constrained('units')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('dicision_number')->comment('quyết định số')->nullable();
            $table->date('date')->nullable();
            $table->string('year')->nullable();
            $table->string('signer')->nullable();
            $table->string('signer_position')->nullable();
            $table->string('type')->nullable();
            $table->tinyInteger('reward_form')->comment('Hình thức khen thưởng');
            $table->longText('content')->nullable();
            $table->tinyInteger('is_personal')->nullable();
            $table->tinyInteger('active')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rewards');
    }
}
