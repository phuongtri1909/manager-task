<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|array  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // Nếu không có vai trò được chỉ định, cho phép truy cập
        if (empty($roles)) {
            return $next($request);
        }

        // Kiểm tra role_id của người dùng
        if (!$user->role_id) {
            return redirect()->route('tasks.index')
                ->with('error', 'Tài khoản của bạn chưa được phân quyền.');
        }

        // Admin always has access to everything
        if ($user->isAdmin()) {
            return $next($request);
        }
        
        $hasPermission = false;
        
        // Check if the route involves a specific department
        $departmentId = null;
        
        // Check if we're accessing a department-specific resource
        // For example, if we have a route parameter 'department'
        if ($request->route('department')) {
            $departmentId = $request->route('department')->id;
        } 
        // Or if we're passing department_id as a parameter
        elseif ($request->has('department_id')) {
            $departmentId = $request->input('department_id');
        }
        // Or if we have a task that belongs to a department
        elseif ($request->route('task') && $request->route('task')->department_id) {
            $departmentId = $request->route('task')->department_id;
        }
        
        foreach ($roles as $role) {
            $methodName = 'is' . ucfirst($role);
            
            // Check if method exists and returns true
            if (method_exists($user, $methodName) && call_user_func([$user, $methodName])) {
                // For global roles (admin, director, deputy director)
                if (in_array($role, ['admin', 'director', 'deputyDirector'])) {
                    $hasPermission = true;
                    break;
                }
                
                // For department-specific roles, check if user has access to the department
                if ($departmentId === null || $user->hasAccessToDepartment($departmentId)) {
                    $hasPermission = true;
                    break;
                }
            }
        }

        if ($hasPermission) {
            return $next($request);
        }

        return redirect()->route('tasks.index')
            ->with('error', 'Bạn không có quyền truy cập trang này.');
    }
} 