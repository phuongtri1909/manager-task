<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartyTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('documentable_id');
            $table->string('documentable_type');
            $table->string('name');
            $table->string('alias');
            $table->string('path');
            $table->string('size');
            $table->string('type');
            $table->text('description')->nullable();

            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('party_meetings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamp('time');
            $table->text('description')->nullable();
            $table->string('status');

            $table->boolean('active')->default(1);
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('party_documents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamp('time');
            $table->text('description')->nullable();
            $table->string('type');
            $table->string('direction');

            $table->boolean('active')->default(1);
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('party_checks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamp('time');
            $table->text('description')->nullable();
            $table->string('place');

            $table->boolean('active')->default(1);
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('party_studies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamp('time');
            $table->text('description')->nullable();
            $table->string('place');

            $table->boolean('active')->default(1);
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('party_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nation_id')->comment('Dân tộc')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('religion_id')->comment('Tôn giáo')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('party_id')->comment('Chi bộ')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('name');
            $table->string('avatar')->nullable();
            $table->boolean('gender')->comment('1: male, 2: female');
            $table->timestamp('date_of_birth')->nullable();
            $table->string('alias')->comment('Bí danh')->nullable();
            $table->string('residence')->comment('Nơi thường trú')->nullable();
            $table->string('shelter')->comment('Nơi tạm trú')->nullable();
            $table->string('job')->comment('Nghề nghiệp')->nullable();
            $table->string('education_level')->comment('Trình độ PT')->nullable();
            $table->string('vocational_education')->comment('Giáo dục nghề nghiệp')->nullable();
            $table->string('postgraduate')->comment('Sau đại học')->nullable();
            $table->string('foreign_language')->comment('Ngoại ngữ')->nullable();
            $table->string('information_technology')->comment('Tin học')->nullable();
            $table->string('union_joining_date')->comment('Ngày vào đoàn')->nullable();
            $table->string('union_joining_place')->comment('Nơi vào đoàn')->nullable();
            $table->string('joining_date')->comment('Kết nạp đảng ngày')->nullable();
            $table->string('joining_place')->comment('Nơi kết nạp 1')->nullable();
            $table->string('recognition_date')->comment('Chính thức ngày')->nullable();
            $table->string('recognition_place_1')->comment('Nơi công nhận đảng viên dự bị')->nullable();
            $table->string('recognition_place_2')->comment('Nơi công nhận đảng viên chính thức')->nullable();
            $table->string('introduced_1')->comment('Người giới thiệu 1')->nullable();
            $table->string('introduced_2')->comment('Người giới thiệu 2')->nullable();
            $table->string('profile')->comment('Lý lịch cá nhân')->nullable();
            $table->string('position')->comment('Chức vụ công tác')->nullable();
            $table->string('childhood')->comment('Đặc điểm lịch sử')->nullable();
            $table->string('training')->comment('Đào tạo, bồi dưỡng')->nullable();
            $table->string('go_abroad')->comment('Đi nước ngoài')->nullable();
            $table->string('bonus')->comment('Khen thưởng')->nullable();
            $table->string('discipline')->comment('Kỷ luật')->nullable();
            $table->string('family_circumstances')->comment('Hoàn cảnh gia đình')->nullable();
            $table->string('self_review')->comment('Tự nhận xét')->nullable();
            $table->string('party_review')->comment('Nhận xét của chi bộ')->nullable();
            $table->string('committee_review')->comment('Nhận xét cấp ủy')->nullable();
            $table->unsignedTinyInteger('academic_rank')->comment('Học hàm')->nullable();
            $table->unsignedTinyInteger('political_theory')->comment('Lý luận chính trị')->nullable();
            $table->unsignedTinyInteger('party_badge')->comment('Huy hiệu đảng')->nullable();
            $table->unsignedTinyInteger('party_position')->comment('Chức vụ đảng')->nullable();
            $table->unsignedTinyInteger('union_position')->comment('Chức vụ đoàn thể')->nullable();
            $table->unsignedTinyInteger('status');
            $table->string('official_code')->comment('Mã số đảng viên chính thức')->nullable();
            $table->string('reserve_code')->comment('Mã số dự bị')->nullable();
            $table->unsignedFloat('position_salary_coefficient')->default(0)->comment('Hệ số lương chức vụ');
            $table->unsignedFloat('responsibility_salary_coefficient')->default(0)->comment('Hệ số lương trách nhiệm');
            $table->unsignedFloat('toxic_salary_coefficient')->default(0)->comment('Hệ số lương độc hại');
            $table->unsignedFloat('regional_allowance')->default(0)->comment('Phụ cấp khu vực');
            $table->unsignedFloat('regional_minimum_wage')->default(0)->comment('Mức lương tối thiểu vùng');
            $table->boolean('free_party_fee')->default(0)->comment('Thuộc diện miễn đóng đảng phí');

            $table->boolean('active')->default(1);
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('reserve_fees', function (Blueprint $table) {
            $table->id();
            $table->timestamp('time');
            $table->double('amount');
            $table->text('description')->nullable();

            $table->boolean('active')->default(1);
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('party_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('party_member_id')->constrained('party_members')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamp('time');
            $table->double('income')->comment('cột 2');
            $table->double('fee')->comment('cột 3');
            $table->double('fee_clone')->comment('cột 4');
            $table->double('previous_fee')->nullable()->comment('cột 5');
            $table->double('total')->comment('cột 6');

            $table->boolean('active')->default(1);
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('additional_party_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('party_member_id')->constrained('party_members')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamp('time');

            $table->double('old_salary');
            $table->double('new_salary');
            $table->double('deviation');
            $table->double('count_months');
            $table->double('amount');

            $table->boolean('active')->default(1);
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('other_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('handler_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamp('time');
            $table->double('amount');
            $table->tinyInteger('type')->comment('-1: chi, 1: thu');
            $table->text('description')->nullable();

            $table->boolean('active')->default(1);
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('documents');
        Schema::dropIfExists('party_meetings');
        Schema::dropIfExists('party_texts');
        Schema::dropIfExists('party_checks');
        Schema::dropIfExists('party_studies');
        Schema::dropIfExists('party_members');
        Schema::dropIfExists('reserve_fees');
        Schema::dropIfExists('party_fees');
        Schema::dropIfExists('additional_party_fees');
        Schema::dropIfExists('other_fees');
    }
}
