<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $positionList = [
            [
                'created_by' => 1,
                'name' => 'Văn thư cấp huyện',
                'slug' => generate_slug('Văn thư cấp huyện'),
                'is_clerical' => true,
            ],
            [
                'created_by' => 1,
                'name' => 'Nhân viên cấp huyện',
                'slug' => generate_slug('Nhân viên cấp huyện'),
            ],
            [
                'created_by' => 1,
                'name' => 'Tổ trưởng cấp huyện',
                'slug' => generate_slug('Tổ trưởng cấp huyện'),
            ],
            [
                'created_by' => 1,
                'name' => 'Phó giám đốc cấp huyện',
                'slug' => generate_slug('Phó giám đốc cấp huyện'),
                'is_leader' => true,
            ],
            [
                'created_by' => 1,
                'name' => 'Giám đốc cấp huyện',
                'slug' => generate_slug('Giám đốc cấp huyện'),
                'is_leader' => true,
            ],
            [
                'created_by' => 1,
                'name' => 'Nhân viên cấp tỉnh',
                'slug' => generate_slug('Nhân viên cấp tỉnh'),
                'is_provincial_level' => true,
            ],
            [
                'created_by' => 1,
                'name' => 'Văn thư cấp tỉnh',
                'slug' => generate_slug('Văn thư cấp tỉnh'),
                'is_provincial_level' => true,
                'is_clerical' => true,
            ],
            [
                'created_by' => 1,
                'name' => 'Phó phòng cấp tỉnh',
                'slug' => generate_slug('Phó phòng cấp tỉnh'),
                'is_provincial_level' => true,
                'is_leader' => true,
            ],
            [
                'created_by' => 1,
                'name' => 'Trưởng phòng cấp tỉnh',
                'slug' => generate_slug('Trưởng phòng cấp tỉnh'),
                'is_provincial_level' => true,
                'is_leader' => true,
            ],
            [
                'created_by' => 1,
                'name' => 'Phó giám đốc cấp tỉnh',
                'slug' => generate_slug('Phó giám đốc cấp tỉnh'),
                'is_provincial_level' => true,
                'is_leader' => true,
            ],
            [
                'created_by' => 1,
                'name' => 'Giám đốc cấp tỉnh',
                'slug' => generate_slug('Giám đốc cấp tỉnh'),
                'is_provincial_level' => true,
                'is_leader' => true,
            ],
        ];

        foreach ($positionList as $position) {
            Position::create($position);
        }
    }
}
