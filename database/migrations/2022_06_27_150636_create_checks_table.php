<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChecksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->nullable()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('ward_id')->nullable()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('town_id')->nullable()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('unit_id')->nullable()->comment('Tên ĐVUT/ Hội xã')->constrained()->cascadeOnUpdate()->cascadeOnDelete();

            $table->timestamp('time')->comment('ngày kiểm tra');
            $table->unsignedDouble('debt')->comment('dư nợ');
            $table->unsignedDouble('balance')->comment('số dư');
            $table->unsignedInteger('number_of_groups')->comment('số tổ');
            $table->unsignedInteger('number_of_borrowers')->comment('số hộ vay');
            $table->unsignedBigInteger('unit_check_id')->comment('đơn vị kiểm tra'); // SystemDefine::UNIT_CHECK()
            $table->text('description')->nullable()->comment('tóm tắt kết quả kiểm tra');

            $table->boolean('active')->default(1);
            $table->foreignId('created_by')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
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
        Schema::dropIfExists('checks');
    }
}
