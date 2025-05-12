<?php

namespace App\Http\Controllers\Reward;

use App\Helpers\SystemDefine;
use App\Http\Controllers\Controller;
use App\Http\Requests\DecideRewardRequest;
use App\Models\DecideReward;
use App\Models\Unitcd;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DecideRewardController extends Controller
{

    public string $title = 'Quyết định khen thưởng';

    public function __construct()
    {
        $this->featureSlug = SystemDefine::DECIDE_REWARD_FEATURE;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $dataTable  = $this->generateDataTableData();
        $rewards = DecideReward::active()->get();
        $unitscd = Unitcd::active()->get();
        return $this->view('reward.decide.index', compact('dataTable', 'unitscd', 'rewards'));        
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $unitscd = Unitcd::active()->get();
        return $this->view('reward.decide.form', compact('unitscd'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(DecideRewardRequest $request)
    {
        $decides = new DecideReward();
        $decides->fill($request->validated());
        $decides->date = Carbon::createFromFormat('d/m/Y', $request->date);
        //echo "store:";die();
        $decides->save();

        flash_message(SystemDefine::CREATE_SUCCESS_MESSAGE);
        return redirect()->route('decide-reward.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $reward       = DecideReward::find($id);
        $unitscd      = Unitcd::active()->get();
        return $this->view('reward.decide.form', compact('unitscd', 'reward'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(DecideRewardRequest $request, $id)
    {
        $reward = DecideReward::find($id);
        $reward->fill($request->validated());
        $reward->date = Carbon::createFromFormat('d/m/Y', $request->date);
        $reward->save();
        flash_message(SystemDefine::UPDATE_SUCCESS_MESSAGE);
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $reward = DecideReward::find($id);
        $reward->delete();
        flash_message(SystemDefine::DELETE_SUCCESS_MESSAGE);
        return redirect()->route('decide-reward.index');
    }

    private function generateDataTableData(): array
    {
        $heads = [
            'STT',
            'Số QĐ',
            'Ngày Q',
            'ĐV khen',
            'Năm KT',
            'Người ký',
            'Chc vụ người ký',
            'Loại KT',
            'Danh hiệu/Hình thức khen',
            'Nội dung khen',
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
