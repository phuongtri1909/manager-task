<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Quản trị viên hệ thống',
                'level' => 100,
            ],
            [
                'name' => 'Giám đốc',
                'slug' => 'director',
                'description' => 'Giám đốc',
                'level' => 90,
            ],
            [
                'name' => 'Phó Giám đốc',
                'slug' => 'deputy-director',
                'description' => 'Phó Giám đốc',
                'level' => 80,
            ],
            [
                'name' => 'Trưởng phòng',
                'slug' => 'department-head',
                'description' => 'Trưởng phòng ban',
                'level' => 70,
            ],
            [
                'name' => 'Phó Trưởng phòng',
                'slug' => 'deputy-department-head',
                'description' => 'Phó Trưởng phòng ban',
                'level' => 60,
            ],
            [
                'name' => 'Nhân viên',
                'slug' => 'staff',
                'description' => 'Nhân viên',
                'level' => 10,
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
} 