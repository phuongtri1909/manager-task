<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Http\Request;

class UserRoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('check.role:admin');
    }
    
    /**
     * Display a list of users for role assignment with filters
     */
    public function index(Request $request)
    {
        // Get all roles and departments for filter dropdowns
        $allRoles = Role::orderBy('name')->get();
        $allDepartments = Department::orderBy('name')->get();
        
        // Start with a base query
        $usersQuery = User::with(['role', 'department']);
        
        // Apply filters if provided
        if ($request->filled('name')) {
            $usersQuery->where('name', 'like', '%' . $request->input('name') . '%');
        }
        
        if ($request->filled('email')) {
            $usersQuery->where('email', 'like', '%' . $request->input('email') . '%');
        }
        
        if ($request->filled('role_id')) {
            $usersQuery->where('role_id', $request->input('role_id'));
        }
        
        if ($request->filled('department_id')) {
            $usersQuery->where('department_id', $request->input('department_id'));
        }
        
        // Get the filtered users and paginate the results
        $users = $usersQuery->orderBy('name')->paginate(15)->withQueryString();
        
        return view('manager_task.user_roles.index', compact('users', 'allRoles', 'allDepartments'));
    }
    
    /**
     * Show form to assign role to a user
     */
    public function edit(User $user)
    {
        if($user->isAdmin()){
            return redirect()->route('user-roles.index')->with('warning', 'Bạn đã là quản trị viên có toàn quyền trong hệ thống!');
        }
        $roles = Role::all();
        $departments = Department::all();
        
        return view('manager_task.user_roles.edit', compact('user', 'roles', 'departments'));
    }
    
    /**
     * Update the user's role
     */
    public function update(Request $request, User $user)
    {
        if($user->isAdmin()){
            return redirect()->route('user-roles.index')->with('warning', 'Bạn đã là quản trị viên có toàn quyền trong hệ thống!');
        }
        
        // Base validation
        $validationRules = [
            'role_id' => 'required|exists:roles,id',
        ];
        
        // Get the selected role
        $role = Role::findOrFail($request->input('role_id'));
        $roleSlug = $role->slug;
        
        // Add department_id validation based on role type
        if (in_array($roleSlug, ['department-head', 'deputy-department-head', 'staff']) || 
           ($role->isDepartmentSpecific() && !in_array($roleSlug, ['director', 'deputy-director', 'admin']))) {
            $validationRules['department_id'] = 'required|exists:departments,id';
        } else {
            $validationRules['department_id'] = 'nullable|exists:departments,id';
        }
        
        // Validate based on role-specific rules
        $validated = $request->validate($validationRules);
        
        // Handle specific role types
        if ($roleSlug === 'admin') {
            // Admin always has all permissions and no department
            $validated['can_assign_task'] = true;
            $validated['department_id'] = null;
        } 
        else if (in_array($roleSlug, ['director', 'deputy-director'])) {
            // Director/Deputy Director have no department
            $validated['department_id'] = null;
            $validated['can_assign_task'] = $request->has('can_assign_task');
        }
        else if (in_array($roleSlug, ['department-head', 'deputy-department-head'])) {
            // Department Head/Deputy need department and can have task creation rights
            $validated['can_assign_task'] = $request->has('can_assign_task');
        }
        else if ($roleSlug === 'staff') {
            // Staff always have no task creation rights
            $validated['can_assign_task'] = false;
        }
        else {
            // Default - use input values
            $validated['can_assign_task'] = $request->has('can_assign_task');
            
            // Set department_id to null for global roles
            if ($role->isGlobal()) {
                $validated['department_id'] = null;
            }
        }
        
        $user->update($validated);
        
        return redirect()->route('user-roles.index')
            ->with('success', 'Phân quyền cho người dùng ' . $user->name . ' thành công!');
    }
} 