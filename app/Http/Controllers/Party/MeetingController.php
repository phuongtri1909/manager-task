<?php

namespace App\Http\Controllers\Party;

use App\Helpers\SystemDefine;
use App\Http\Controllers\Controller;
use App\Http\Requests\DocumentRequest;
use App\Http\Requests\PartyMeetingRequest;
use App\Models\PartyMeeting;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MeetingController extends Controller
{
    public string $title = 'Meeting';

    public function __construct()
    {
        $this->featureSlug = SystemDefine::PARTY_MEETING_FEATURE;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $time               = Carbon::createFromFormat('Y', $request->year ?? now()->format('Y'));    

        $meetingList    = PartyMeeting::with('documents')->whereYear('time', $time)->active();
        //$request->year && $meetingList->whereYear('time', $request->year);
        $meetingList    = $meetingList->get();
        $dataTableData  = $this->generateDataTableData();
        return $this->view('party.meeting.index', compact('dataTableData', 'meetingList'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return $this->view('party.meeting.form');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PartyMeetingRequest $request)
    {
        $meeting        = new PartyMeeting();
        $meeting->fill($request->only('name', 'description', 'status'));
        $meeting->time  = Carbon::createFromFormat('d/m/Y H:i', $request->time);
        $meeting->save();

        flash_message(SystemDefine::CREATE_SUCCESS_MESSAGE);
        return redirect()->route('party.meeting.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PartyMeeting  $meeting
     * @return \Illuminate\Http\Response
     */
    public function edit(PartyMeeting $meeting)
    {
        return $this->view('party.meeting.form', compact('meeting'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PartyMeeting  $meeting
     * @return \Illuminate\Http\Response
     */
    public function update(PartyMeetingRequest $request, PartyMeeting $meeting)
    {
        $meeting->fill($request->validated());
        $meeting->time = Carbon::createFromFormat('d/m/Y H:i', $request->time);
        $meeting->save();

        flash_message(SystemDefine::UPDATE_SUCCESS_MESSAGE);
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PartyMeeting  $meeting
     * @return \Illuminate\Http\Response
     */
    public function destroy(PartyMeeting $meeting)
    {
        $meeting->delete();

        flash_message(SystemDefine::DELETE_SUCCESS_MESSAGE);
        return redirect()->route('party.meeting.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  \App\Models\PartyMeeting  $meeting
     * @return \Illuminate\Http\Response
     */
    public function documentCreate(PartyMeeting $meeting)
    {
        $actionUrl  = route('party.meeting.document.store', $meeting->id);
        $backUrl    = route('party.meeting.index');
        return $this->view('party.document-form', compact('actionUrl', 'backUrl'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PartyMeeting  $meeting
     * @return \Illuminate\Http\Response
     */
    public function documentStore(DocumentRequest $request, PartyMeeting $meeting)
    {
        $meeting->documents()->save(store_file(
            'public/documents/parties/',
            $request->file('file'),
            $request->alias,
            $request->description
        ));

        flash_message(SystemDefine::CREATE_SUCCESS_MESSAGE);
        return redirect()->route('party.meeting.index');
    }

    private function generateDataTableData(): array
    {
        $heads = [
            'STT',
            'Chuyên đề',
            'Ngày họp',
            'Giờ họp',
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
