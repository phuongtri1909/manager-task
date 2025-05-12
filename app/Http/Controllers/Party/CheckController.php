<?php

namespace App\Http\Controllers\Party;

use App\Helpers\SystemDefine;
use App\Http\Controllers\Controller;
use App\Http\Requests\DocumentRequest;
use App\Http\Requests\PartyCheckRequest;
use App\Models\PartyCheck;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CheckController extends Controller
{
    public string $title = 'Kiểm tra Đảng viên';

    public function __construct()
    {
        $this->featureSlug = SystemDefine::PARTY_CHECK_FEATURE;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $time               = Carbon::createFromFormat('Y', $request->year ?? now()->format('Y'));
        

        $checkList      = PartyCheck::with('documents')->whereYear('time', $time)->active();
     //   $request->year && $checkList->whereYear('time', $request->year);
        $checkList      = $checkList->get();
        $dataTableData  = $this->generateDataTableData();
        return $this->view('party.checks.index', compact('dataTableData', 'checkList'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return $this->view('party.checks.form');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PartyCheckRequest $request)
    {
        $check          = new PartyCheck();
        $check->fill($request->validated());
        $check->time    = Carbon::createFromFormat('d/m/Y H:i', $request->time);
        $check->save();

        flash_message(SystemDefine::CREATE_SUCCESS_MESSAGE);
        return redirect()->route('party.checks.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PartyCheck  $check
     * @return \Illuminate\Http\Response
     */
    public function edit(PartyCheck $check)
    {
        return $this->view('party.checks.form', compact('check'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PartyCheck  $check
     * @return \Illuminate\Http\Response
     */
    public function update(PartyCheckRequest $request, PartyCheck $check)
    {
        $check->fill($request->validated());
        $check->time = Carbon::createFromFormat('d/m/Y H:i', $request->time);
        $check->save();

        flash_message(SystemDefine::UPDATE_SUCCESS_MESSAGE);
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PartyCheck  $check
     * @return \Illuminate\Http\Response
     */
    public function destroy(PartyCheck $check)
    {
        $check->delete();

        flash_message(SystemDefine::DELETE_SUCCESS_MESSAGE);
        return redirect()->route('party.checks.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     * @param  \App\Models\PartyCheck  $check
     */
    public function documentCreate(PartyCheck $check)
    {
        $actionUrl  = route('party.checks.document.store', $check->id);
        $backUrl    = route('party.checks.index');
        return $this->view('party.document-form', compact('actionUrl', 'backUrl'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PartyCheck  $check
     * @return \Illuminate\Http\Response
     */
    public function documentStore(DocumentRequest $request, PartyCheck $check)
    {
        $check->documents()->save(store_file(
            'public/documents/parties/',
            $request->file('file'),
            $request->alias,
            $request->description
        ));

        flash_message(SystemDefine::CREATE_SUCCESS_MESSAGE);
        return redirect()->route('party.checks.index');
    }

    private function generateDataTableData(): array
    {
        $heads = [
            'STT',
            'Tên đảng viên',
            'Đơn vị công tác',
            'Thời gian kiểm tra',
            'Trạng thái',
            [
                'label' => __('Hành động'),
                'no-export' => true, 'width' => 10
            ]
        ];

        $config = [
            'order'     => [[0, 'asc']],
            'columns'   => array_merge(
                [['type' => 'num']],
                array_fill(0, 4, null),
                [['orderable' => false]]
            ),
            'language'  => [
                'url'   => asset('vendor/vi.json'),
            ],"scrollX" => true,
        ];
        return ['config' => $config, 'heads' => $heads];
    }
}
