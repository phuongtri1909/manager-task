<?php

namespace App\Http\Controllers\Party;

use App\Helpers\SystemDefine;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobAssignmentRequest;
use App\Http\Requests\UpdateJobAssignmentRequest;
use App\Models\JobAssignment;
use App\Models\User;
use App\Models\Ward;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class JobAssignmentController extends Controller
{
    public string $title = 'Phân công giao dịch xã';

    public function __construct()
    {
        $this->featureSlug = SystemDefine::PARTY_JOB_ASSIGNMENT_FEATURE;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        list($wardList, $assignmentList, $statisticList, $time) = $this->getDataForView($request);

        return $this->view('party.job.index', compact(
            'wardList',
            'assignmentList',
            'statisticList',
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        list($wardList, $assignmentList, $statisticList, $time) = $this->getDataForView($request);

        return $this->view('party.job.index', compact(
            'wardList',
            'assignmentList',
            'statisticList',
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreJobAssignmentRequest $request)
    {
        $personsCount           = intval($request->persons_count);
        $wardId                 = intval($request->ward_id);
        $assignmentUsers        = $this->generateUserAssignment($personsCount, $wardId);
        $countAssignmentUsers   = count($assignmentUsers);

        if ($personsCount > $countAssignmentUsers) {
            $wardName           = Ward::active()->where('id', $wardId)->select('name')->first()->name;
            $message            = "Phân công giao dịch xã {$wardName} không đủ số lượng, mong muốn {$personsCount} nhưng hiện đang có {$countAssignmentUsers} cán bộ.";

            throw ValidationException::withMessages(['persons_count' => $message]);
        }

        $jobAssignment          = new JobAssignment();
        $jobAssignment->ward_id = $wardId;
        $jobAssignment->date    = Carbon::createFromFormat('d/m/Y', $request->date);
        $jobAssignment->save();

        $jobAssignment->users()->attach($assignmentUsers);

        flash_message(SystemDefine::CREATE_SUCCESS_MESSAGE);
        return redirect()->route('party.job-assignment.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PartyCheck  $check
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, JobAssignment $jobAssignment)
    {
        list($wardList, $assignmentList, $statisticList, $time) = $this->getDataForView($request);

        $managerDepartmentId    = User::active()->with('department')
            ->where('can_assign_job', true)
            ->whereHas('wards', fn (Builder $q) => $q->where('wards.id', $jobAssignment->ward_id))
            ->first()?->department?->id;

        $userGDVC               = User::active()
            ->with('department')
            ->where('can_assign_job', true)
            ->where('code_for_job_assignment', '<>', 'tkt')
            ->where('code_for_job_assignment', '<>', 'tq')
            ->where('code_for_job_assignment', '<>', 'ld')
            ->where('code_for_job_assignment', '<>', 'lx')
            ->where('code_for_job_assignment', '<>', 'bv')
            ->whereHas('department', fn (Builder $query) => $query->whereDepartmentId($managerDepartmentId))
            ->get()
            ->groupBy('department.id'); 

        $userLd               = User::active()
            ->with('department')
            ->where('can_assign_job', true) 
            ->where(function ($query) {
                 $query->where('code_for_job_assignment', '=', 'ld')
                       ->Orwhere('code_for_job_assignment', '=', 'pp');
            })
            ->whereHas('department', fn (Builder $query) => $query->whereDepartmentId($managerDepartmentId))
            ->get()
            ->groupBy('department.id'); 
            
        $userList               = User::active()
            ->with('department')
            ->where('can_assign_job', true)
            ->whereHas('department', fn (Builder $query) => $query->whereDepartmentId($managerDepartmentId))
            ->get()
            ->groupBy('department.id');
//dd($userList);
        return $this->view('party.job.index', compact(
            'wardList',
            'userList',
            'userGDVC',
            'userLd',
            'assignmentList',
            'jobAssignment',
            'statisticList'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateJobAssignmentRequest $request, JobAssignment $jobAssignment)
    {
        $personsCount           = intval($request->persons_count);
        $userIds                = $request->user_ids;
        $wardId                 = $jobAssignment->ward_id;
        $assignmentUsers        = $this->generateUserAssignment($personsCount, $wardId, $userIds);
        $countAssignmentUsers   = count($assignmentUsers);

        if ($personsCount > $countAssignmentUsers) {
            $wardName           = Ward::active()->select('name')->find($wardId)->first()->name;
            $message            = "Phân công giao dịch xã {$wardName} không đủ số lượng, mong muốn {$personsCount} nhưng hiện đang có {$countAssignmentUsers} cán bộ.";

            throw ValidationException::withMessages(['persons_count' => $message]);
        }

        $jobAssignment->users()->detach();
        $jobAssignment->users()->attach($assignmentUsers);

        flash_message(SystemDefine::UPDATE_SUCCESS_MESSAGE);
        return redirect()->route('party.job-assignment.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(JobAssignment $jobAssignment)
    {
        $jobAssignment->delete();

        flash_message(SystemDefine::DELETE_SUCCESS_MESSAGE);
        return redirect()->route('party.job-assignment.index');
    }

    public function pdf(Request $request)
    {
        list($wardList, $assignmentList, $statisticList, $time) = $this->getDataForView($request);
      //$myDate = '01/09/2023';      
        $time               = Carbon::createFromFormat('!m/Y', $request->month ?? now()->format('m/Y'));
      //$date               = Carbon::createFromFormat('d/m/Y', $myDate);
      //$time = $date->format('m/Y');
      
        $mm = $time->month;
        $yy = $time->year;
        //$mm = '09';
        //$yy = '2023';
        $mm .=$yy;
        $pdf = Pdf::loadView('pdf.job-assignment', [
            'assignmentList' => $assignmentList,
            'month' => $time,
            'department' => auth()->user()->department,

        ]);
        $pdf->setPaper('A4', 'landscape');
        return $pdf->download('Kehoachgdx-'.$mm.'.pdf');
    }

    private function getDataForView(Request $request): array
    {
        //$time               = Carbon::createFromFormat('!m/Y', $request->month ?? now()->format('m/Y'));
        $time               = Carbon::createFromFormat('!m/Y', $request->month ?? now()->format('m/Y'));
        
     //   print_r($time);die();
		//$myDate = '01/09/2023';      
        //$date               = Carbon::createFromFormat('d/m/Y', $myDate);
        //$time = $date->format('m/Y');
   
        // check can view all
        $assignmentList     = is_admin(auth()->user()) || can_view($this->featureSlug)
            ? JobAssignment::active()->with('users', 'ward')->whereMonth('date', $time)->whereYear('date', $time)->get()
            : JobAssignment::active()->with('users', 'ward')->whereMonth('date', $time)->whereYear('date', $time)
            ->whereHas('users', function (Builder $q) {
                $q->whereDepartmentId(auth()->user()->department_id);
            })->orderBy('job_assignments.date')->get();
//dd($assignmentList);
        $wardList           = is_admin(auth()->user())
            ? Ward::active()->with('district')->orderBy('wards.tt')->get()
            : Ward::active()->with('district')->whereHas('managers', function (Builder $q) {
                $q->whereDepartmentId(auth()->user()->department_id);
            })->orderBy('wards.tt')->get();
//dd($wardList);
        $statisticList      = $assignmentList
            ->pluck('users')
            ->flatten(1)
            ->unique('id')
            ->map(function (User $u) use ($assignmentList) {
                $u->date_count = $assignmentList->reduce(function (int $dateCount, JobAssignment $j) use ($u) {
                    return $j->users->where('id', $u->id)->first() ? $dateCount + 1 : $dateCount;
                }, 0);
                return $u;
            });
        //    print_r($time);die();
        return [$wardList, $assignmentList, $statisticList, $time];
    }

    private function generateUserAssignment(int $total, int $wardId, array $userIds = null): array
    {
        // tm trưng nhóm: người có ward (quản lý ward)
        $teamLeader         = User::active()
            ->with('department')
            ->where('can_assign_job', true)
            ->whereHas('wards', fn (Builder $q) => $q->where('wards.id', $wardId))
            ->first();

        // nếu ko c leader => lỗi
        throw_if(
            empty($teamLeader) || empty($teamLeader->department),
            ValidationException::withMessages(['ward_id' => 'Xã hiện chưa chỉ định tổ trưởng.'])
        );

        $positions = $teamLeader->department->level === 1
            ? SystemDefine::WORK_SCHEDULE_POSITIONS_FOR_LEVEL_1()
            : SystemDefine::WORK_SCHEDULE_POSITIONS_FOR_LEVEL_2_3();

        $assignmentList     = [];
        $ignoreIds          = [];
        // tìm các user thuộc department ca leader
        $userList           = User::active()
            ->where('can_assign_job', true)
            ->whereDepartmentId($teamLeader->department->id)
            ->get();

        foreach (collect($positions)->sortBy('priority') as $jobPosition) {
            // count($assignmentList) >= $total => break
            $assignedUsersCount = collect($assignmentList)->unique('user_id')->count();
            if ($assignedUsersCount >= $total) {
                break;
            }

            $id                         = $jobPosition['id'];
            // check xem userId hiện ti có nằm trong list user ids mà khách hàng ch định (chỉ trong update)
            if (!is_null($userIds) && array_key_exists($id, $userIds)) {
                $specifiedByCustomer    = @$userIds[$id];
                // nếu ko empty => push user id vào ignoreIds và assignmentList đ ln sau ko check lại
                if (empty($specifiedByCustomer)) continue;
                $ignoreIds[]            = @$specifiedByCustomer;
                $assignmentList[$id]    = ['user_id' => $specifiedByCustomer, 'position' => $id];
                continue;
            }

            // check xem có cần ly trng vi user nào ko vd t trưởng kim bảo vệ
            if (!empty($jobPosition['coincides_with_id'])) {
                $coincide               = @$assignmentList[$jobPosition['coincides_with_id']];
                if (empty($coincide)) continue;
                // nu ko empty => push user id vào assignmentList đ lần sau ko check lại
                // không cần push vào ignoreIds vì là lấy trùng => chc chắn đã có sn trong ignoreIds
                $assignmentList[$id]    = ['user_id' => $coincide['user_id'], 'position' => $id];
                continue;
            }

            // tìm ra user thoả với conditions của WORK_SCHEDULE_POSITIONS
            $user                       = $userList
                ->whereNotIn('id', $ignoreIds) // user ko đợc nm trong $ignoreIds (tránh trùng lặp)
                ->filter(function (User $u) use ($jobPosition, $wardId) {
                    // nếu ko c conditions => chọn, nếu c thì phi thoả tất cả conditions
                    if (empty($jobPosition['condition'])) return true;

                    return collect($jobPosition['condition']) // lặp và so snh tt c cc điều kiện
                        ->every(function (string $v, string $c) use ($u, $wardId) {
                            return $this->isPassedCondition($c, $v, $u, $wardId);
                        });
                })
                ->shuffle() // sau này cải tin shuffle bằng thuật ton nào đó đ tránh lặp
                ->first();

            if (empty($user)) continue;

            $ignoreIds[]                = $user->id;
            $assignmentList[$id]        = ['user_id' => $user->id, 'position' => $id];
        }

        return $assignmentList;
    }

    private function isPassedCondition(string $condition, mixed $value, User $user, int $wardId): bool
    {
        switch ($condition) {
            case 'exists_ward_id':
                return $user->wards?->pluck('id')->contains($wardId);

            case 'code_for_job_assignment':
                return str_contains(strtolower($user->code_for_job_assignment), strtolower($value));

            case 'is_leader':
                return @$user->position->is_leader;

            default:
                return true;
        }
    }
}
