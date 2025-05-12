<?php

namespace App\Http\Controllers\Category;

use App\Helpers\SystemDefine;
use App\Http\Controllers\Controller;
use App\Http\Requests\TownRequest;
use App\Models\Town;
use App\Models\Ward;
use Illuminate\Http\Request;

class TownController extends Controller
{
    public string $title = 'Quản lý tổ';

    public function __construct()
    {
        $this->featureSlug = SystemDefine::CATEGORY_TOWN_FEATURE;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $dataTableData  = $this->generateDataTableData();
        $towns          = Town::active()->with('ward')->get();

        return $this->view('town.index', compact('dataTableData', 'towns'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $wards = Ward::active()->get();

        return $this->view('town.form', compact('wards'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(TownRequest $request)
    {
        $town = new Town();
        $town->fill($request->validated());
        $town->save();

        flash_message(SystemDefine::CREATE_SUCCESS_MESSAGE);
        return redirect()->route('town.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Town  $town
     * @return \Illuminate\Http\Response
     */
    public function edit(Town $town)
    {
        $wards = Ward::active()->get();

        return $this->view('town.form', compact('wards', 'town'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Town  $town
     * @return \Illuminate\Http\Response
     */
    public function update(TownRequest $request, Town $town)
    {
        $town->fill($request->validated());
        $town->save();

        flash_message(SystemDefine::UPDATE_SUCCESS_MESSAGE);
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Town  $town
     * @return \Illuminate\Http\Response
     */
    public function destroy(Town $town)
    {
        $town->delete();

        flash_message(SystemDefine::DELETE_SUCCESS_MESSAGE);
        return redirect()->route('town.index');
    }

    private function generateDataTableData(): array
    {
        $heads = [
            'STT',
            'Mã',
            'Tên',
            'Xã',
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
