<?php

namespace App\Http\Controllers\Party;

use App\Helpers\SystemDefine;
use App\Http\Controllers\Controller;
use App\Http\Requests\PartyMemberRequest;
use App\Models\Nation;
use App\Models\Party;
use App\Models\PartyMember;
use App\Models\Religion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public string $title = 'Đảng viên';

    public function __construct()
    {
        $this->featureSlug = SystemDefine::PARTY_MEMBER_FEATURE;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $memberList     = PartyMember::with('religion', 'nation', 'party')->active()->orderBy('sx')->get();
        $dataTableData  = $this->generateDataTableData();
      //  print_r($memberList);die();
        return $this->view('party.members.index', compact('dataTableData', 'memberList'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $nationList     = Nation::active()->get();
        $religionList   = Religion::active()->get();
        $partyList      = Party::active()->get();
        $userList       = User::active()->with('department')->get()->groupBy('department.id');

        return $this->view('party.members.form', compact(
            'nationList',
            'religionList',
            'partyList',
            'userList'
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PartyMemberRequest $request)
    {
        $data                       = $request->validated();
        $data['avatar']             = store_file('public/documents/member/', $request->file('avatar'), $request->alias ?? '')->path;
        $data['date_of_birth']      = $request->date_of_birth
            ? Carbon::createFromFormat('d/m/Y', $request->date_of_birth)
            : null;
        $data['joining_date']       = $request->joining_date
            ? Carbon::createFromFormat('d/m/Y', $request->joining_date)
            : null;
        $data['recognition_date']   = $request->recognition_date
            ? Carbon::createFromFormat('d/m/Y', $request->recognition_date)
            : null;
        PartyMember::create($data);

        flash_message(SystemDefine::CREATE_SUCCESS_MESSAGE);
        return redirect()->route('party.members.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PartyMember  $member
     * @return \Illuminate\Http\Response
     */
    public function edit(PartyMember $member)
    {
        $nationList     = Nation::active()->get();
        $religionList   = Religion::active()->get();
        $partyList      = Party::active()->get();
        $userList       = User::active()->with('department')->get()->groupBy('department.id');

        return $this->view('party.members.form', compact(
            'member',
            'nationList',
            'religionList',
            'partyList',
            'userList'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PartyMember  $member
     * @return \Illuminate\Http\Response
     */
    public function update(PartyMemberRequest $request, PartyMember $member)
    {
        $data                       = $request->validated();
        
        $data['avatar']             = $request->file('avatar')
            ? store_file('public/documents/member/', $request->file('avatar'), $request->alias ?? '')->path
            : $member->avatar;
        $data['date_of_birth']      = $request->date_of_birth
            ? Carbon::createFromFormat('d/m/Y', $request->date_of_birth)
            : null;
        $data['joining_date']       = $request->joining_date
            ? Carbon::createFromFormat('d/m/Y', $request->joining_date)
            : null;
        $data['recognition_date']   = $request->recognition_date
            ? Carbon::createFromFormat('d/m/Y', $request->recognition_date)
            : null;
        $data['sx']   = $request->sx;            
   
        $member->update($data);

        flash_message(SystemDefine::UPDATE_SUCCESS_MESSAGE);
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PartyMember  $member
     * @return \Illuminate\Http\Response
     */
    public function destroy(PartyMember $member)
    {
        $member->delete();

        flash_message(SystemDefine::DELETE_SUCCESS_MESSAGE);
        return redirect()->route('party.members.index');
    }

    private function generateDataTableData(): array
    {
        $heads = [
            'STT',
            'Sắp xếp',
            'Họ và tên',
            'Năm sinh',
            'Chức vụ đảng',
            'Ngày vào đảng',
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
