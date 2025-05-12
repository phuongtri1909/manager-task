<?php

namespace App\Http\Controllers\Reward;

use App\Helpers\SystemDefine;
use App\Http\Controllers\Controller;
use App\Http\Requests\CollectiveRewardRequest;
use App\Models\CollectiveSearch;
use App\Models\DecideReward;
use App\Models\Unitcd;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CollectiveSearchController extends Controller
{
    public string $title = 'Tra cứu - Tập thể';

    public function __construct()
    {
        $this->featureSlug = SystemDefine::DECIDE_REWARD_COLLECTIVE_SEARCH_FEATURE;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $dataTable  = $this->generateDataTableData();
        $groups = CollectiveSearch::active()->orderBy('unit_id', 'ASC')->orderBy('reward_id1', 'ASC')->get();                 
        //echo "groups:".$groups;die();
        //print_r ($dataTable);die();  
             
        return $this->view('reward.group.index', compact('dataTable', 'groups'));
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $unitscd = Unitcd::active()->get();
        $rewards = DecideReward::active()->get();          
        return $this->view('reward.group.form', compact('unitscd', 'rewards'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CollectiveRewardRequest $request)
    {
        // $colective = new CollectiveSearch();
        // $colective->fill($request->validated());
        // echo "store: ".$colective;die();
        // $colective->save();
        $colective_old = new CollectiveSearch();   
        $colective_old->fill($request->validated());  
            
        $reward_id = $colective_old->reward_id;        

        // Query để lấy data trong rewards theo id rồi ấy unit_id

        $unit_id_rewards = DecideReward::active()->select('unit_id')->where('id', $reward_id)->get();

        foreach ($unit_id_rewards as $item) {
             $unit_id_reward  = $item->unit_id;
        }       
       // echo "editaaaaaaaa: ".$unit_id_reward;die();
        foreach($colective_old->unit_id as $unitId) {
            $colective = new CollectiveSearch();
            $colective->unit_id = $unitId;
            $colective->reward_id = $reward_id;
            $colective->reward_id1 = $unit_id_reward;
            $colective->save();
         }
        flash_message(SystemDefine::CREATE_SUCCESS_MESSAGE);
        return redirect()->route('collective-search.index');
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
        $group = CollectiveSearch::find($id);
        $unitscd = Unitcd::active()->get();
        $rewards = DecideReward::active()->get();
      //  echo "edit: ".$unitscd;die();
        return $this->view('reward.group.form', compact('unitscd', 'rewards', 'group'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CollectiveRewardRequest $request, $id)
    {
        $group = CollectiveSearch::find($id);
        $group->fill($request->validated());
        $group->save();
        flash_message(SystemDefine::CREATE_SUCCESS_MESSAGE);
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
        $group = CollectiveSearch::find($id);
        $group->delete();
        flash_message(SystemDefine::DELETE_SUCCESS_MESSAGE);
        return redirect()->route('collective-search.index');
    }


    /**
     * generate data like heading, title of data table
     *
     * @return array
     */
    private function generateDataTableData(): array
    {
        $heads = [
            'STT',
            'Mã đơn vị',
            'Tên đơn vị',
            'Số QĐ',
            'Ngày QĐ',
            'ĐV khen',
            'Năm KT',
            'Người ký',
            'Chức vụ người ký',
            'Loại KT',
            'Danh hiệu/Hình thức',
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
                array_fill(0, 11, null),
                [['orderable' => false]]
            ),
            'language'  => [
                'url'   => asset('vendor/vi.json'),
            ],"scrollX" => true,
        ];

        return ['config' => $config, 'heads' => $heads];
    }
    public function pdf(Request $request)
    {
        $time = Carbon::createFromFormat('m/Y', $request->month ?? now()->format('m/Y'));
        $mm = $time->month;
        $yy = $time->year;
        $mm .=$yy;
        $groups = CollectiveSearch::active()->orderBy('unit_id', 'ASC')->orderBy('reward_id1', 'ASC')->get();
        $pdf = Pdf::loadView('pdf.collective-search', [
            'groups' => $groups,
            'time' => $time
        ]);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->download('KTCD-tapthe-'.$mm.'.pdf');
    }
}
