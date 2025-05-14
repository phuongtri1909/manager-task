<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Lấy tất cả vai trò
        $roles = Role::all();
        
        // Lấy phòng mặc định
        $department = Department::first() ?? Department::create([
            'name' => 'Phòng Test', 
            'slug' => 'phong-test', 
            'level' => 1,
            'created_by' => 1
        ]);
        
        // Mảng thông tin tài khoản cho các vai trò
        $accounts = [
            'admin' => [
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => 'password123',
                'can_assign_job' => true,
            ],
            'director' => [
                'name' => 'Giám Đốc',
                'email' => 'giamdoc@example.com',
                'password' => 'password123',
                'can_assign_job' => true,
            ],
            'deputy-director' => [
                'name' => 'Phó Giám Đốc',
                'email' => 'phogiamdoc@example.com',
                'password' => 'password123',
                'can_assign_job' => true,
            ],
            'department-head' => [
                'name' => 'Trưởng Phòng',
                'email' => 'truongphong@example.com',
                'password' => 'password123',
                'can_assign_job' => true,
            ],
            'deputy-department-head' => [
                'name' => 'Phó Trưởng Phòng',
                'email' => 'photruongphong@example.com',
                'password' => 'password123',
                'can_assign_job' => true,
            ],
            'staff' => [
                'name' => 'Nhân Viên',
                'email' => 'nhanvien@example.com',
                'password' => 'password123',
                'can_assign_job' => false,
            ],
        ];
        
        foreach ($roles as $role) {
            // Kiểm tra xem có thông tin tài khoản cho vai trò này không
            if (isset($accounts[$role->slug])) {
                $accountInfo = $accounts[$role->slug];
                
                // Kiểm tra xem email đã tồn tại chưa
                $existingUser = User::where('email', $accountInfo['email'])->first();
                
                if (!$existingUser) {
                    // Tạo tài khoản mới
                    User::create([
                        'name' => $accountInfo['name'],
                        'email' => $accountInfo['email'],
                        'password' => Hash::make($accountInfo['password']),
                        'role_id' => $role->id,
                        'department_id' => $department->id,
                        'can_assign_job' => $accountInfo['can_assign_job'],
                    ]);
                    
                    $this->command->info("Tài khoản {$role->name} đã được tạo thành công!");
                } else {
                    // Cập nhật tài khoản đã tồn tại
                    $existingUser->update([
                        'role_id' => $role->id,
                        'department_id' => $department->id,
                        'can_assign_job' => $accountInfo['can_assign_job'],
                    ]);
                    
                    $this->command->info("Tài khoản {$role->name} đã được cập nhật thành công!");
                }
            }
        }
    }
} 