<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckCanAssignTask
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        // Admin cannot create tasks
        if ($user->isAdmin()) {
            return redirect()->route('tasks.index')
                ->with('error', 'Quản trị viên không có quyền tạo công việc!');
        }
        
        // Only specific roles with permission can create tasks
        if (!($user->isDirector() || $user->isDeputyDirector() || 
              $user->isDepartmentHead() || $user->isDeputyDepartmentHead())) {
            return redirect()->route('tasks.index')
                ->with('error', 'Chỉ Giám đốc, Phó giám đốc, Trưởng phòng và Phó trưởng phòng có quyền tạo công việc!');
        }
        
        // Check if user has task assignment permission set by admin
        if (!$user->can_assign_task) {
            return redirect()->route('tasks.index')
                ->with('error', 'Bạn không có quyền tạo công việc! Vui lòng liên hệ admin để được cấp quyền.');
        }
        
        return $next($request);
    }
} 