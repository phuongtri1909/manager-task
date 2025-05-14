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

        $hasPermission = false;
        
        foreach ($roles as $role) {
            $methodName = 'is' . ucfirst($role);
            
            // Kiểm tra nếu phương thức tồn tại và trả về true
            if (method_exists($user, $methodName) && call_user_func([$user, $methodName])) {
                $hasPermission = true;
                break;
            }
        }

        if ($hasPermission) {
            return $next($request);
        }

        return redirect()->route('tasks.index')
            ->with('error', 'Bạn không có quyền truy cập trang này.');
    }
} 