<?php

namespace App\Http\Controllers\Check;

use App\Helpers\SystemDefine;
use App\Http\Controllers\Controller;
use App\Http\Requests\Check\WardLevelRequest;
use App\Models\Check;
use App\Models\District;
use App\Models\Unit;
use App\Models\Ward;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WardLevelController extends Controller
{
    public string $title = 'Quản lý công tác kiểm tra cấp xã';

    public function __construct()
    {
        $this->featureSlug = SystemDefine::CHECK_DISTRICT_LEVEL_FEATURE;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $dataTableData  = $this->generateDataTableData();
        $checkList      = Check::active()->with(
            'district',
            'ward',
            'ward.district',
            'town',
            'town.ward',
            'town.ward.district',
            'household',
            'household.town',
            'household.town.ward',
            'household.town.ward.district',
            'unit',
        )->whereNotNull('ward_id')->get();

        return $this->view('check.ward.index', compact('dataTableData', 'checkList'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $wards  = Ward::active()->get();
        $units      = Unit::active()->get();
        $districts = District::active()->get();

        return $this->view('check.ward.form', compact('wards', 'units', 'districts'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(WardLevelRequest $request)
    {
        $check          = new Check();
        $check->fill($request->validated());
        $check->time    = Carbon::createFromFormat('d/m/Y', $request->time);
        $check->save();

        flash_message(SystemDefine::CREATE_SUCCESS_MESSAGE);
        return redirect()->route('wards.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Check  $check
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $check = Check::find($id);
        $wards      = Ward::active()->get();
        $units      = Unit::active()->get();
        $districts = District::active()->get();


        return $this->view('check.ward.form', compact('wards', 'units', 'check', 'districts'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Check  $check
     * @return \Illuminate\Http\Response
     */
    public function update(WardLevelRequest $request, $id)
    {
        $check = Check::find($id);
        $check->fill($request->validated());
        $check->time    = Carbon::createFromFormat('d/m/Y', $request->time);
        $check->save();

        flash_message(SystemDefine::CREATE_SUCCESS_MESSAGE);
        return redirect()->route('wards.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Check  $check
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $check = Check::find($id);
        $check->delete();

        flash_message(SystemDefine::DELETE_SUCCESS_MESSAGE);
        return redirect()->route('wards.index');
    }

    private function generateDataTableData(): array
    {
        $heads = [
            'STT',
            'Ngày KT',
            'Tên Huyện',
            'Tên Xã',
            'Dư nợ',
            'Số tổ TK & VV',
            'Số hộ vay',
            'Số dư TK',
            'Đơn vị kiểm tra',
            'Tóm tắt kết quả tra',
            [
                'label' => __('Hành động'),
                'no-export' => true, 'width' => 10
            ]
        ];

        $config = [
            'order'     => [[0, 'asc']],
            'columns'   => array_merge(
                [['type' => 'num']],
                array_fill(0, 9, null),
                [['orderable' => false]]
            ),
            'language'  => [
                'url'   => asset('vendor/vi.json'),
            ],"scrollX" => true,
        ];

        return ['config' => $config, 'heads' => $heads];
    }
}
