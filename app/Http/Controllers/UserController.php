<?php

namespace App\Http\Controllers;

use App\Helpers\SystemDefine;
use App\Http\Requests\ProfileRequest;
use App\Http\Requests\UserRequest;
use App\Models\Department;
use App\Models\Permission;
use App\Models\user;
use App\Models\Position;
use App\Models\Unit;
use App\Models\Ward;

class UserController extends Controller
{
    public string $title = 'Nhân viên';

    public function __construct()
    {
        $this->featureSlug = SystemDefine::USER_FEATURE;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $dataTableData  = $this->generateDataTableData();
        $userList       = User::with('department', 'permissions', 'wards', 'position', 'unit')->get();

        return $this->view('user.index', compact('dataTableData', 'userList'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $departments    = Department::active()->get();
        $positions      = Position::active()->get();
        $wards          = Ward::active()->get();
        $units          = Unit::active()->get();
        $permissionList = Permission::active()->get();
        $ds_ma_don_vi_cong_tac = SystemDefine::DS_DON_VI_CONG_TAC();
        $ds_ma_chuc_vu = SystemDefine::DS_CHUC_VU();

        return $this->view('user.form', compact(
            'permissionList',
            'departments',
            'positions',
            'wards',
            'units',
            'ds_ma_don_vi_cong_tac',
            'ds_ma_chuc_vu',
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserRequest $request)
    {
        $user               = new User();
        $user->fill($request->validated());
        $user->password     = bcrypt($request->password);
        
        $user->save();
        $user->wards()->attach($request->ward_ids);
        $user->permissions()->sync(array_filter($request->permission_ids ?? []));

        flash_message(SystemDefine::CREATE_SUCCESS_MESSAGE);
        return redirect()->route('users.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        $departments    = Department::active()->get();
        $positions      = Position::active()->get();
        $wards          = Ward::active()->get();
        $units          = Unit::active()->get();
        $permissionList = Permission::active()->get();
        $ds_ma_don_vi_cong_tac = SystemDefine::DS_DON_VI_CONG_TAC();
        $ds_ma_chuc_vu = SystemDefine::DS_CHUC_VU();

        return $this->view('user.form', compact(
            'user',
            'permissionList',
            'departments',
            'positions',
            'wards',
            'units',
            'ds_ma_don_vi_cong_tac',
            'ds_ma_chuc_vu',
        ));
    }

    /**
     * `Update` the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UserRequest $request, User $user)
    {
        $inputs = $request->validated();

        if ($request->filled('password')) {
            $inputs['password'] = bcrypt($request->password);
        } else {
            unset($inputs['password']);
        }

        $user->fill($inputs);
        $user->save();
        $user->wards()->sync($request->ward_ids);
        $user->permissions()->sync(array_filter($request->permission_ids ?? []));

        flash_message(SystemDefine::UPDATE_SUCCESS_MESSAGE);
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $user->delete();
        flash_message(SystemDefine::DELETE_SUCCESS_MESSAGE);
        return redirect()->route('users.index');
    }

    private function generateDataTableData(): array
    {
        $heads = [
            'STT',
            'Mã',
            'Mã phân công công việc',
            'Quản lý xã',
            'Họ và Tên',
            'Email',
            'Chức vụ',
            'Phòng ban',
            [
                'label' => __('Hành động'),
                'no-export' => true, 'width' => 10
            ]
        ];

        $config = [
            'order'     => [[0, 'asc']],
            'columns'   => array_merge(
                [['type' => 'num']],
                array_fill(0, 7, null),
                [['orderable' => false]]
            ),
            'language'  => [
                'url'   => asset('vendor/vi.json'),
            ],
            "scrollX"   => true,
        ];

        return ['config' => $config, 'heads' => $heads];
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function profile()
    {
        $user           = auth()->user();
        $departments    = Department::active()->get();
        $positions      = Position::active()->get();
        $wards          = Ward::active()->get();
        $units          = Unit::active()->get();
        $permissionList = Permission::active()->get();

        return $this->view('profile.form', compact(
            'user',
            'permissionList',
            'departments',
            'positions',
            'wards',
            'units'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateProfile(ProfileRequest $request,)
    {
        $user = User::find(auth()->id());
        $inputs = $request->validated();

        if ($request->filled('password')) {
            $inputs['password'] = bcrypt($request->password);
        } else {
            unset($inputs['password']);
        }

        $user->fill($inputs);
        $user->save();

        flash_message(SystemDefine::UPDATE_SUCCESS_MESSAGE);
        return back();
    }
}
