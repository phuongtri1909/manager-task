<?php

namespace Database\Seeders;

use App\Helpers\SystemDefine;
use App\Models\Department;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class DepartmentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roleList = [
            [
                'created_by' => 1,
                'name' => 'Admin',
                'slug' => SystemDefine::ADMIN_DEPARTMENT,
                'level' => null,
            ],
            [
                'created_by' => 1,
                'name' => 'Giám đốc',
                'slug' => generate_slug('Giám đốc'),
                'level' => 3,
            ],
            [
                'created_by' => 1,
                'name' => 'Phòng cấp tỉnh 1',
                'slug' => generate_slug('Phòng cấp tỉnh 1'),
                'level' => 2,
            ],
            [
                'created_by' => 1,
                'name' => 'Phòng cấp huyện 1',
                'slug' => generate_slug('Phòng cấp huyện 1'),
                'level' => 1,
            ],
        ];

        foreach ($roleList as $role) {
            Department::create($role);
        }

        // assign full permission for admin department
        $permissionIds      = Permission::pluck('id')->toArray();
        $adminDepartment    = Department::find(1);
        $adminDepartment->permissions()->attach($permissionIds);
    }
}
