<?php

namespace App\Http\Controllers\Party;

use App\Helpers\SystemDefine;
use App\Http\Controllers\Controller;
use App\Http\Requests\DocumentRequest;
use App\Http\Requests\PartyStudyRequest;
use App\Models\PartyStudy;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StudyController extends Controller
{
    public string $title = 'Học tập và làm theo tấm gương đạo đức Hồ Chí Minh';

    public function __construct()
    {
        $this->featureSlug = SystemDefine::PARTY_STUDY_FEATURE;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        
        $time               = Carbon::createFromFormat('Y', $request->year ?? now()->format('Y'));        

        $studyList      = PartyStudy::with('documents')->whereYear('time', $time)->active();
     //   $request->year && $studyList->whereYear('time', $request->year);
        $studyList      = $studyList->get();
        $dataTableData  = $this->generateDataTableData();
        return $this->view('party.study.index', compact('dataTableData', 'studyList'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return $this->view('party.study.form');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PartyStudyRequest $request)
    {
        $study          = new PartyStudy();
        $study->fill($request->validated());
        $study->time    = Carbon::createFromFormat('d/m/Y H:i', $request->time);
        $study->save();

        flash_message(SystemDefine::CREATE_SUCCESS_MESSAGE);
        return redirect()->route('party.study.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PartyStudy  $study
     * @return \Illuminate\Http\Response
     */
    public function edit(PartyStudy $study)
    {
        return $this->view('party.study.form', compact('study'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PartyStudy  $study
     * @return \Illuminate\Http\Response
     */
    public function update(PartyStudyRequest $request, PartyStudy $study)
    {
        $study->fill($request->validated());
        $study->time = Carbon::createFromFormat('d/m/Y H:i', $request->time);
        $study->save();

        flash_message(SystemDefine::UPDATE_SUCCESS_MESSAGE);
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PartyStudy  $study
     * @return \Illuminate\Http\Response
     */
    public function destroy(PartyStudy $study)
    {
        $study->delete();

        flash_message(SystemDefine::DELETE_SUCCESS_MESSAGE);
        return redirect()->route('party.study.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     * @param  \App\Models\PartyStudy  $study
     */
    public function documentCreate(PartyStudy $study)
    {
        $actionUrl  = route('party.study.document.store', $study->id);
        $backUrl    = route('party.study.index');
        return $this->view('party.document-form', compact('actionUrl', 'backUrl'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PartyStudy  $study
     * @return \Illuminate\Http\Response
     */
    public function documentStore(DocumentRequest $request, PartyStudy $study)
    {
        $study->documents()->save(store_file(
            'public/documents/parties/',
            $request->file('file'),
            $request->alias,
            $request->description
        ));

        flash_message(SystemDefine::CREATE_SUCCESS_MESSAGE);
        return redirect()->route('party.study.index');
    }

    private function generateDataTableData(): array
    {
        $heads = [
            'STT',
            'Chuyên đề',
            'Đơn vị thực hiện',
            'Thời gian thực hiện',
            'Người trình bày',
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
                array_fill(0, 5, null),
                [['orderable' => false]]
            ),
            'language'  => [
                'url'   => asset('vendor/vi.json'),
            ],"scrollX" => true,
        ];
        return ['config' => $config, 'heads' => $heads];
    }
}
