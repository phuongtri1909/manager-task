<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DirectorAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Tìm vai trò giám đốc
        $directorRole = Role::where('slug', 'director')->first();
        
        if (!$directorRole) {
            $this->command->error('Vai trò giám đốc không tồn tại. Vui lòng chạy RoleSeeder trước!');
            return;
        }
        
        // Tạo tài khoản giám đốc
        User::create([
            'name' => 'Giám Đốc',
            'email' => 'giamdoc@example.com',
            'password' => Hash::make('password123'), // Đổi mật khẩu theo yêu cầu
            'role_id' => $directorRole->id,
            'can_assign_task' => true,
        ]);
        
        $this->command->info('Tài khoản giám đốc đã được tạo thành công!');
    }
}