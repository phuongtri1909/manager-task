<?php

namespace App\Http\Controllers\Reward;

use App\Helpers\SystemDefine;
use App\Http\Controllers\Controller;
use App\Http\Requests\PersonalRewardRequest;
use App\Models\DecideReward;
use App\Models\PersonalLookup;
use App\Models\Unit;
use App\Models\Unitcd;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PersonalLookupController extends Controller
{
    public string $title = 'Tra cứu - Cá nhân';

    public function __construct()
    {
        $this->featureSlug = SystemDefine::DECIDE_REWARD_PERSON_LOOKUP_FEATURE;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $dataTable  = $this->generateDataTableData();
        $persons = PersonalLookup::active()->orderBy('user_id', 'ASC')->orderBy('reward_id1', 'ASC')->get();        
        //echo "rrrrrrr".$persons;die();
        return $this->view('reward.personal.index', compact('dataTable', 'persons'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $users = User::active()->get();        
        $rewards = DecideReward::active()->get();     
       
        return $this->view('reward.personal.form', compact('users', 'rewards'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PersonalRewardRequest $request)
    {      
        
        $personal_old = new PersonalLookup();  
        $personal_old->fill($request->validated());    
          
        $rewardId = $personal_old->reward_id;

        // Query để lấy data trong rewards theo id rồi ấy unit_id

        $unit_id_rewards = DecideReward::active()->select('unit_id')->where('id', $rewardId)->get();

        foreach ($unit_id_rewards as $item) {
             $unit_id_reward  = $item->unit_id;
        }       
        
        foreach($personal_old->user_id as $userId) {
            $personal = new PersonalLookup();        
            $personal->user_id = $userId;
            $personal->reward_id  = $rewardId;
            $personal->reward_id1 = $unit_id_reward;
            $personal->save();
         }
         
        flash_message(SystemDefine::CREATE_SUCCESS_MESSAGE);
        return redirect()->route('personal-lookup.index');
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
        $personal = PersonalLookup::find($id);
        $users = User::active()->get();
        $rewards = DecideReward::active()->get();
        //echo "edit: ".$personal;die();
        return $this->view('reward.personal.form', compact('users', 'rewards', 'personal'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(PersonalRewardRequest $request, $id)
    {
        $personal = PersonalLookup::find($id);
        $personal->fill($request->validated());
        $personal->save();
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
        $personal = PersonalLookup::find($id);
        $personal->delete();
        flash_message(SystemDefine::DELETE_SUCCESS_MESSAGE);
        return redirect()->route('personal-lookup.index');
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
            'Mã nhân viên',
            'Tên nhân viên',
            'ĐV khen',
            'Số QĐ',
            'Ngày QĐ',
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
        $persons = PersonalLookup::active()->orderBy('user_id', 'ASC')->orderBy('reward_id1', 'ASC')->get();       
        $pdf = Pdf::loadView('pdf.personal-lookup', [
            'persons' => $persons,
            'time' => $time
        ]);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->download('KTCD-canhan-'.$mm.'.pdf');
    }
}
