<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleAccountsSeeder extends Seeder
{
    public function run()
    {
        // Lấy tất cả vai trò
        $roles = Role::all();
        
        // Mảng thông tin tài khoản cho các vai trò
        $accounts = [
            'admin' => [
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => '11111111',
                'can_assign_task' => true,
            ],
            'director' => [
                'name' => 'Giám Đốc',
                'email' => 'giamdoc@example.com',
                'password' => '11111111',
                'can_assign_task' => true,
            ],
            'deputy-director' => [
                'name' => 'Phó Giám Đốc',
                'email' => 'phogiamdoc@example.com',
                'password' => '11111111',
                'can_assign_task' => true,
            ],
            'department-head' => [
                'name' => 'Trưởng Phòng',
                'email' => 'truongphong@example.com',
                'password' => '11111111',
                'can_assign_task' => true,
            ],
            'deputy-department-head' => [
                'name' => 'Phó Trưởng Phòng',
                'email' => 'photruongphong@example.com',
                'password' => '11111111',
                'can_assign_task' => true,
            ],
            'staff' => [
                'name' => 'Nhân Viên',
                'email' => 'nhanvien@example.com',
                'password' => '11111111',
                'can_assign_task' => false,
            ],
        ];

        // First create admin user
        $adminRole = $roles->where('slug', 'admin')->first();
        $adminInfo = $accounts['admin'];
        
        $admin = User::firstOrCreate(
            ['email' => $adminInfo['email']],
            [
                'name' => $adminInfo['name'],
                'password' => Hash::make($adminInfo['password']),
                'role_id' => $adminRole->id,
                'can_assign_task' => $adminInfo['can_assign_task'],
            ]
        );

        // Now create departments with admin as creator
        $department = Department::firstOrCreate(
            ['slug' => 'phong-test'],
            [
                'name' => 'Phòng Test', 
                'level' => 1,
                'created_by' => $admin->id
            ]
        );

        $department2 = Department::firstOrCreate(
            ['slug' => 'phong-test-2'],
            [
                'name' => 'Phòng Test 2', 
                'level' => 1,
                'created_by' => $admin->id
            ]
        );
        
        // Now create rest of the users
        foreach ($roles as $role) {
            if ($role->slug === 'admin') continue; // Skip admin as already created
            
            if (isset($accounts[$role->slug])) {
                $accountInfo = $accounts[$role->slug];
                
                $user = User::firstOrCreate(
                    ['email' => $accountInfo['email']],
                    [
                        'name' => $accountInfo['name'],
                        'password' => Hash::make($accountInfo['password']),
                        'role_id' => $role->id,
                        'department_id' => $department->id,
                        'can_assign_task' => $accountInfo['can_assign_task'],
                    ]
                );
                
                $this->command->info("Tài khoản {$role->name} đã được " . 
                    ($user->wasRecentlyCreated ? 'tạo' : 'cập nhật') . " thành công!");
            }
        }
    }
}