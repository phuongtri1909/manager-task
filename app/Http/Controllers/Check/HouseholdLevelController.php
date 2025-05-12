<?php

namespace App\Http\Controllers\Check;

use App\Helpers\SystemDefine;
use App\Http\Controllers\Controller;
use App\Http\Requests\Check\HouseholdLevelRequest;
use App\Models\Check;
use App\Models\District;
use App\Models\Household;
use App\Models\Town;
use App\Models\Unit;
use App\Models\Ward;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HouseholdLevelController extends Controller
{
    public string $title = 'Quản lý công tác kiểm tra hộ vay';

    public function __construct()
    {
        $this->featureSlug = SystemDefine::CHECK_TOWN_LEVEL_FEATURE;
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
        )->whereNotNull('household_id')->get();

        return $this->view('check.household.index', compact('dataTableData', 'checkList'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $households = Household::active()->get();
        $units = Unit::active()->get();

        return $this->view('check.household.form', compact('households', 'units'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(HouseholdLevelRequest $request)
    {
        $check          = new Check();
        $check->fill($request->validated());
        $check->time    = Carbon::createFromFormat('d/m/Y', $request->time);
        $check->save();

        flash_message(SystemDefine::CREATE_SUCCESS_MESSAGE);
        return redirect()->route('households.index');
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
        $towns = Town::active()->get();
        $households = Household::active()->get();


        return $this->view('check.household.form', compact('households', 'units', 'check'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Check  $check
     * @return \Illuminate\Http\Response
     */
    public function update(HouseholdLevelRequest $request, $id)
    {
        $check = Check::find($id);
        $check->fill($request->validated());
        $check->time    = Carbon::createFromFormat('d/m/Y', $request->time);
        $check->save();

        flash_message(SystemDefine::CREATE_SUCCESS_MESSAGE);
        return redirect()->route('households.index');
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
        return redirect()->route('households.index');
    }

    private function generateDataTableData(): array
    {
        $heads = [
            'STT',
            'Ngày KT',
            'Tên Huyện',
            'Tên Xã',
            'Hội xã',
            'Tên tổ',
            'Hộ vay',
            'Dư nợ',
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
                array_fill(0, 10, null),
                [['orderable' => false]]
            ),
            'language'  => [
                'url'   => asset('vendor/vi.json'),
            ],"scrollX" => true,
        ];

        return ['config' => $config, 'heads' => $heads];
    }
}
