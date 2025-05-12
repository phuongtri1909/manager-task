<?php

namespace App\Http\Controllers\Category;

use App\Helpers\SystemDefine;
use App\Http\Controllers\Controller;
use App\Http\Requests\HouseholdRequest;
use App\Models\Household;
use App\Models\Town;
use Illuminate\Http\Request;

class HouseholdController extends Controller
{
    public string $title = 'Quản lý hộ';

    public function __construct()
    {
        $this->featureSlug = SystemDefine::CATEGORY_HOUSEHOLD_FEATURE;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $dataTableData  = $this->generateDataTableData();
        $households     = Household::active()->with('town')->get();

        return $this->view('household.index', compact('dataTableData', 'households'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $towns = Town::active()->get();

        return $this->view('household.form', compact('towns'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(HouseholdRequest $request)
    {
        $household = new Household();
        $household->fill($request->validated());
        $household->save();

        flash_message(SystemDefine::CREATE_SUCCESS_MESSAGE);
        return redirect()->route('household.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Household  $household
     * @return \Illuminate\Http\Response
     */
    public function edit(Household $household)
    {
        $towns = Town::active()->get();

        return $this->view('household.form', compact('towns', 'household'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Household  $household
     * @return \Illuminate\Http\Response
     */
    public function update(HouseholdRequest $request, Household $household)
    {
        $household->fill($request->validated());
        $household->save();

        flash_message(SystemDefine::UPDATE_SUCCESS_MESSAGE);
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Household  $household
     * @return \Illuminate\Http\Response
     */
    public function destroy(Household $household)
    {
        $household->delete();

        flash_message(SystemDefine::DELETE_SUCCESS_MESSAGE);
        return redirect()->route('household.index');
    }

    private function generateDataTableData(): array
    {
        $heads = [
            'STT',
            'Mã',
            'Tên',
            'Tổ',
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
