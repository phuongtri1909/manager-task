<?php

namespace Database\Seeders;

use App\Models\District;
use Illuminate\Database\Seeder;

class DistrictsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $districts = [
            ['4401', 'Đơn Dương',],
            ['4402', 'Đức Trọng',],
            ['4403', 'Di Linh',],
            ['4404', 'Đam Rông',],
            ['4405', 'Đạ Huoai',],
            ['4406', 'Đạ Tẻh',],
            ['4407', 'Cát Tiên',],
            ['4408', 'Lạc Dương',],
            ['4409', 'Lâm Hà',],
            ['4410', 'Hội sở',],
            ['4411', 'Bảo Lộc',],
            ['4412', 'Bảo Lâm',],
            ['CN44', 'Toàn tỉnh',],
        ];

        foreach ($districts as $d) {
            District::create([
                'name' => $d[1],
                'code' => $d[0],
            ]);
        }
    }
}
