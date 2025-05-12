<?php

namespace App\Http\Middleware;

use App\Helpers\SystemDefine;
use App\Models\Feature;
use App\Models\Permission;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DefineGatesMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $permissionList = Permission::active()->select('feature_slug', 'permission_slug')->get();

        foreach ($permissionList as $permission) {
            Gate::define($permission->slug, function (User $user) use ($permission) {
                return has_permission($user, $permission->slug);
            });
        }

        // Tạo mới văn bản cho văn thư hoặc admin hoặc user có quyền tạo mới
        $specialPermission = SystemDefine::DOCUMENT_FOR_CLERICAL_FEATURE . '_' . SystemDefine::ACCESS_PERMISSION;
        Gate::define($specialPermission, function (User $user) use ($specialPermission) {
            return $user->position?->is_clerical || has_permission($user, $specialPermission);
        });

        return $next($request);
    }
}
