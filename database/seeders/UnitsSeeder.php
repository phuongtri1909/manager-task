<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $units = [
            ['11', 'Hội nông dân',],
            ['12', 'Hội phụ nữ',],
            ['13', 'Hội cựu chiến binh',],
            ['14', 'Đoàn thanh niên',],
            ['99', 'Vay trực tiếp',],
        ];

        foreach ($units as $u) {
            Unit::create([
                'name' => $u[1],
                'code' => $u[0],
            ]);
        }
    }
}
