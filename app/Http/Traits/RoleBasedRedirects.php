<?php

namespace App\Http\Traits;

use Illuminate\Http\RedirectResponse;

trait RoleBasedRedirects
{
    /**
     * Redirect the user based on their role
     * 
     * @param \App\Models\User $user
     * @param string $errorMessage Optional error message to display
     * @param bool $withError Whether to include the error message in the session
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectBasedOnRole($user, $errorMessage = '', $withError = true): RedirectResponse
    {
        $redirect = null;
        
        if ($user->isAdmin()) {
            $redirect = redirect()->route('tasks.index.admin');
        } elseif ($user->isDirector() || $user->isDeputyDirector() || $user->isDepartmentHead() || $user->isDeputyDepartmentHead()) {
            $redirect = redirect()->route('tasks.managed');
        } else {
            $redirect = redirect()->route('tasks.received');
        }
        
        return $withError && !empty($errorMessage) 
            ? $redirect->with('error', $errorMessage)
            : $redirect;
    }
    
    /**
     * Get the home route based on user role
     * 
     * @param \App\Models\User $user
     * @return string Route name
     */
    protected function getHomeRouteForRole($user): string
    {
        if ($user->isAdmin()) {
            return 'tasks.index.admin';
        } elseif ($user->isDirector() || $user->isDeputyDirector() || $user->isDepartmentHead() || $user->isDeputyDepartmentHead()) {
            return 'tasks.managed';
        } else {
            return 'tasks.received';
        }
    }
}