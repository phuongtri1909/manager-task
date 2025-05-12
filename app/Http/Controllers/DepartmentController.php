<?php

namespace App\Http\Controllers;

use App\Helpers\SystemDefine;
use App\Http\Requests\DepartmentRequest;
use App\Http\Requests\PermissionRequest;
use App\Models\Department;
use App\Models\Permission;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public string $title = 'Phân quyền hệ thống';

    public function __construct()
    {
        $this->featureSlug = SystemDefine::DEPARTMENT_FEATURE;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $departmentList = Department::with('children')->whereNull('parent_id')->get();
        $permissionList = Permission::active()->get();
        return $this->view('management.index', compact('departmentList', 'permissionList',));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $departmentList = Department::with('children')->whereNull('parent_id')->get();
        $permissionList = Permission::active()->get();
        return $this->view('management.index', compact('departmentList', 'permissionList',));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(DepartmentRequest $request)
    {
        $department = new Department();
        $department->fill($request->validated());
        $department->slug = generate_slug($request->name);
        $department->save();
        flash_message(SystemDefine::CREATE_SUCCESS_MESSAGE);
        return redirect()->route('departments.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Department $department)
    {
        $departmentList = Department::with('children')->whereNull('parent_id')->get();
        $permissionList = Permission::active()->get();
        return $this->view('management.index', compact(
            'departmentList',
            'permissionList',
            'department',
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(DepartmentRequest $request, Department $department)
    {
        $department->fill($request->validated());
        $department->slug = generate_slug($request->name);
        $department->save();
        flash_message(SystemDefine::UPDATE_SUCCESS_MESSAGE);
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Department $department)
    {
        $department->delete();
        flash_message(SystemDefine::DELETE_SUCCESS_MESSAGE);
        return redirect()->route('departments.index');
    }

    public function updatePermission(PermissionRequest $request, Department $department)
    {
        $department->permissions()->sync(array_filter($request->permission_ids ?? []));
        flash_message(SystemDefine::UPDATE_SUCCESS_MESSAGE);
        return redirect()->route('departments.edit', $department->id);
    }
}
