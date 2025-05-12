<?php

namespace App\Http\Controllers\Category;

use App\Helpers\SystemDefine;
use App\Http\Controllers\Controller;
use App\Http\Requests\WardRequest;
use App\Models\District;
use App\Models\Ward;
use Illuminate\Http\Request;

class WardController extends Controller
{
    public string $title = 'Quản lý phường/xã';

    public function __construct()
    {
        $this->featureSlug = SystemDefine::CATEGORY_WARD_FEATURE;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $dataTableData  = $this->generateDataTableData();
        $wards          = Ward::active()->with('district')->get();
//echo "aaaaa".$wards;die();
        return $this->view('ward.index', compact('dataTableData', 'wards'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $districts = District::active()->get();

        return $this->view('ward.form', compact('districts'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(WardRequest $request)
    {
        $ward = new Ward();
        $ward->fill($request->validated());
        $ward->save();

        flash_message(SystemDefine::CREATE_SUCCESS_MESSAGE);
        return redirect()->route('ward.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Ward  $ward
     * @return \Illuminate\Http\Response
     */
    public function edit(Ward $ward)
    {
        $districts = District::active()->get();

        return $this->view('ward.form', compact('districts', 'ward'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Ward  $ward
     * @return \Illuminate\Http\Response
     */
    public function update(WardRequest $request, Ward $ward)
    {
        $ward->fill($request->validated());
        $ward->save();

        flash_message(SystemDefine::UPDATE_SUCCESS_MESSAGE);
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Ward  $ward
     * @return \Illuminate\Http\Response
     */
    public function destroy(Ward $ward)
    {
        $ward->delete();

        flash_message(SystemDefine::DELETE_SUCCESS_MESSAGE);
        return redirect()->route('ward.index');
    }

    private function generateDataTableData(): array
    {
        $heads = [
            'STT',
            'Mã',
            'Tên',
            'Huyện',
            [
                'label' => __('Hành động'),
                'no-export' => true, 'width' => 10
            ]
        ];

        $config = [
            'order'     => [[0, 'asc']],
            'columns'   => array_merge(
                [['type' => 'num']],
                array_fill(0, 3, null),
                [['orderable' => false]]
            ),
            'language'  => [
                'url'   => asset('vendor/vi.json'),
            ],"scrollX" => true,
        ];

        return ['config' => $config, 'heads' => $heads];
    }
}
