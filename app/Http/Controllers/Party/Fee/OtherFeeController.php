<?php

namespace App\Http\Controllers\Party\Fee;

use App\Helpers\SystemDefine;
use App\Http\Controllers\Controller;
use App\Http\Requests\DocumentRequest;
use App\Models\OtherFee;
use App\Models\ReserveFee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OtherFeeController extends Controller
{
    public string $title = 'Other Fee';

    public function __construct()
    {
        $this->featureSlug = SystemDefine::PARTY_FEE_OTHER_FEATURE;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $otherFeeList = OtherFee::with('documents')->active();
        $request->year && $otherFeeList->whereYear('time', $request->year);
        $otherFeeList    = $otherFeeList->get();
        $dataTableData = $this->generateDataTableData();
       
        return $this->view('party.other.index', compact('dataTableData', 'otherFeeList'));

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $userList = User::active()->get();
        return $this->view('party.other.form', compact('userList'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)    
    {
        $request->validate([
            'handler_id'    => 'required|exists:users,id',
            'amount'        => 'required|numeric',
            'time'          => 'required|date_format:d/m/Y',
            'type'          => 'required',
            'description'   => 'nullable|string',
        ]);
        
        $otherFee       = new OtherFee();
        $otherFee->fill($request->all());
        $otherFee->time = Carbon::createFromFormat('d/m/Y', $request->time);        
        
        $otherFeecheck = OtherFee::active()->max('time');
       
        //->WhereIn('time', OtherFee::SelectRaw('MAX(time) as MAX')
        if ($otherFeecheck === 0){          
            $otherFeedauky  = ReserveFee::whereYear('time', now())->first();              
            $otherFee->tonquy  = ($otherFeedauky->amount ?? 0) + ($otherFee->type == 1 ? $otherFee->amount : - $otherFee->amount);     
        } else { 
            $otherFeedauky = OtherFee::active()
                                ->select('tonquy as amount')                                 
                                ->WhereIn('time', OtherFee::SelectRaw('MAX(time) as MAX')
                                ->whereYear('time', now()))                                                                                
                                ->first(); 
            //OtherFee::active()->select('tonquy', OtherFee::raw('MAX(time) as maxtime'))->first();            
            $otherFee->tonquy  = ($otherFeedauky->amount ?? 0) + ($otherFee->type == 1 ? $otherFee->amount : - $otherFee->amount);
        }
        $otherFee->save();

        flash_message(SystemDefine::CREATE_SUCCESS_MESSAGE);
        return redirect()->route('party.other-fee.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\OtherFee  $otherFee
     * @return \Illuminate\Http\Response
     */
    public function edit(OtherFee $otherFee)
    {
        $userList = User::active()->get();
        return $this->view('party.other.form', compact('userList', 'otherFee'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\OtherFee  $otherFee
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OtherFee $otherFee)
    {
        $request->validate([
            'handler_id'    => 'required|exists:users,id',
            'amount'        => 'required|numeric',
            'time'          => 'required|date_format:d/m/Y',
            'type'          => 'required',
            'description'   => 'nullable|string',
        ]);
        $otherFee->fill($request->all());
        $otherFee->time = Carbon::createFromFormat('d/m/Y', $request->time);
        $otherFee->save();

        flash_message(SystemDefine::UPDATE_SUCCESS_MESSAGE);
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\OtherFee  $otherFee
     * @return \Illuminate\Http\Response
     */
    public function destroy(OtherFee $otherFee)
    {
      
        $otherFee->delete();

        flash_message(SystemDefine::DELETE_SUCCESS_MESSAGE);
        return redirect()->route('party.other-fee.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  \App\Models\OtherFee  $otherFee
     * @return \Illuminate\Http\Response
     */
    public function documentCreate(OtherFee $otherFee)
    {
        $actionUrl  = route('party.other-fee.document.store', $otherFee->id);
        $backUrl    = route('party.other-fee.index');
        return $this->view('party.document-form', compact('actionUrl', 'backUrl'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\OtherFee  $otherFee
     * @return \Illuminate\Http\Response
     */
    public function documentStore(DocumentRequest $request, OtherFee $otherFee)
    {
        $otherFee->documents()->save(store_file(
            'public/documents/parties/',
            $request->file('file'),
            $request->alias,
            $request->description
        ));

        flash_message(SystemDefine::CREATE_SUCCESS_MESSAGE);
        return redirect()->route('party.other-fee.index');
    }


    private function generateDataTableData(): array
    {
        $heads = [
            'STT',
            'Thu chi',
            'Loại thu chi',
            'Số tiền',
            'Người thực hiện',
            'Ngày thực hiện',            
            'Mô tả/lý do',
            'Tồn quỹ',
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
            ],"scrollX" => true,
        ];

        return ['config' => $config, 'heads' => $heads];
    }
}
