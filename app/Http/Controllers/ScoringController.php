<?php


namespace App\Http\Controllers;

use App\Helpers\SystemDefine;
use App\Models\Cdgdx01;
use App\Models\District;
use App\Models\Scoring;
use App\Models\ScoringFile;
use App\Models\ScoringUserPermission;
use App\Models\ScoringUserView;
use App\Models\User;
use App\Models\Ward;
use App\Models\Codegdx;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\Facade\Dompdf;
use Barryvdh\DomPDF\Facade\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ScoringController extends Controller
{

    public string $title = 'Kiểm tra điểm GDX';

    public function __construct()
    {
        $this->featureSlug = SystemDefine::CHECK_GDX_FEATURE;
    }


    function findAllOccurrences($array, $valueToFind)
    {
        $positions = [];
        foreach ($array as $key => $value) {
            if ($value === $valueToFind) {
                $positions[] = $key;
            }
        }
        return $positions;
    }

    public function index(Request $request)
    {
        
       $month = request()->month ?? now()->format('m/Y');   
       
    // $time = Carbon::createFromFormat('m/Y', $month ?? now()->format('m/Y'));
    // $mont = request()->month ?? now()->format('m');
    // $year = request()->month ?? now()->format('Y');
      
        $userLogin = Auth::user()->id;
        $isAdmin = is_admin(auth()->user());        
        $featureSlug = "quan_ly_kiem_tra_gdx";

        $checkPermission  = User::where('id', $userLogin)
            ->whereHas('permissionScoring', function ($query) use ($featureSlug) {
                $query->where('feature_slug', $featureSlug)->where('permission_slug', 'truy_cap');
            })
            ->first();
        if(!$checkPermission && !$isAdmin)
        {
            return redirect()->back();
        }

        $canCreate = User::where('id', $userLogin)
        ->whereHas('permissionScoring', function ($query) use ($featureSlug) {
            $query->where('feature_slug', $featureSlug)->where('permission_slug', 'them');
        })
        ->first();

        $canUpdate = User::where('id', $userLogin)
        ->whereHas('permissionScoring', function ($query) use ($featureSlug) {
            $query->where('feature_slug', $featureSlug)->where('permission_slug', 'sua');
        })
        ->first();

        $canDelete = User::where('id', $userLogin)
        ->whereHas('permissionScoring', function ($query) use ($featureSlug) {
            $query->where('feature_slug', $featureSlug)->where('permission_slug', 'xoa');
        })
        ->first();

        $canViewAll = User::where('id', $userLogin)
        ->whereHas('permissionScoring', function ($query) use ($featureSlug) {
            $query->where('feature_slug', $featureSlug)->where('permission_slug', 'xem_tat_ca');
        })
        ->first();

        // print_r($canDelete);die;
        
        $scorings = Scoring::leftjoin('wards', 'wards.id', '=', 'scorings.ward_id')            
            ->leftJoin('users as users_1', 'users_1.id', '=', 'scorings.user_1')
            ->leftJoin('users as users_2', 'users_2.id', '=', 'scorings.user_2')
            ->leftJoin('users as user_boss', 'user_boss.id', '=', 'scorings.user_boss')
            ->leftJoin('scoring_user_views','scoring_user_views.scoring_id','=','scorings.id')            
            ->select(
                'scorings.*',
                'wards.code as ward_code',
                'wards.name as ward_name', // Tên của xã/phường
                'users_1.name as user_1_name', // Tên của user_1
                'users_2.name as user_2_name', // Tên của user_1
                'user_boss.name as users_boss_name' // Tên của user_ld
            )->groupBy('scoring_user_views.scoring_id');
            
       // if (!empty($fromDate) && !empty($toDate)) {
            // $scorings->whereBetween('date_check', [$fromDate, $toDate]);
            //$scorings->whereMonth('date_check', $month);
       // }

        $date = Carbon::createFromFormat('m/Y', $month);

        $scorings->whereMonth('date_check', $date->month)      
            ->whereYear('date_check', $date->year); 

        if(empty($canViewAll) && !$isAdmin)
        {            
            // $scorings->where('scorings.user_1',$userLogin)                    
            //          ->orWhere('scorings.user_2',$userLogin)
            //          ->orWhere('scoring_user_views.user_id',$userLogin)
            //          ->orWhere('scorings.user_boss',$userLogin);  


            //$user_list = DB::table('users')
            $scorings->where(function($query) use ($userLogin){
                    $query->where('scorings.user_1', $userLogin);
                    $query->orWhere('scorings.user_2', $userLogin);
                    $query->orWhere('scorings.user_boss', $userLogin);
                    $query->orWhere('scorings.user_boss',$userLogin);
            });
           
        }

        $scorings = $scorings->orderBy('scorings.date_check', 'ASC')->get();
        // echo '<pre>';
        //     print_r($month);
        // echo '</pre>';die();
        
        foreach ($scorings as $key => $value) {

            $permission_admin = ScoringUserPermission::where('scoring_id', $value->id)->where('user_id', $userLogin)->first();           
           
            // User chỉ được view

            $permission_scoring = ScoringUserView::where('scoring_id', $value->id)->where('user_id', $userLogin)->first();
                    
            // End
            $isUser1orUser2 = Scoring::where('id', $value->id)->where('user_1', $userLogin)
            ->orWhere('user_2', $userLogin)
            ->first();

            $value['permission_scoring'] = $permission_scoring ? 1 : 0 ;
            $value['read'] = $permission_admin ? $permission_admin->read : 0 ;
            $value['update'] = $permission_admin ? $permission_admin->update : 0 ;
            $value['delete'] = $permission_admin ? $permission_admin->delete : 0 ;
            $value['isUser1orUser2'] = $isUser1orUser2  ? 1 : 0 ;
        }
      //  echo $scorings;die();
       return $this->view('scoring.index', compact('scorings', 'userLogin','isAdmin','canCreate','canUpdate','canViewAll','canDelete'));

    }
 
    public function getWards($id_dt)
    {
        if ($id_dt) {
         //   echo "sss".$id_dt;die();
            $wards = Ward::where('district_id', $id_dt)->get(['id', 'code', 'name', 'district_id', 'ngaygdx', 'active']);
          //  echo "sss".$wards;die();
            return response()->json(['data' => $wards]);
        } else {
            return response()->json(['data' => '']);
        }

    }

    public function submitPost(Request $request){

        $data = $request->all();
        $userCreate = substr(Auth::user()->code, 2, 4); 
       // print_r($request->district_id);die();       
        $usercheck = User::where(\DB::raw('substr(code, 3,  4)'), '=', $userCreate)->get();   
       
        $output = '';
                $output.='<option></option>';
                foreach($usercheck as $key => $item){
                $output.='<option value="'.$item->id.'">'.$item->name.'</option>';
                }
            echo $output;
        //return response()->json(['data' => $usercheck]);        

    }

    public function submitWard(Request $request){

        $data = $request->all();
        $userCreate = substr(Auth::user()->code, 2, 4);
        //print_r($request->district_id);die();
        $usercheckward = User::where(\DB::raw('substr(code, 3,  4)'), '=', $userCreate)
                        ->where(\DB::raw('code_for_job_assignment'), '=', 'ld')                        
                        ->get();   
       
        $output = '';
                $output.='<option></option>';
                foreach($usercheckward as $key => $item){
                $output.='<option value="'.$item->id.'">'.$item->name.'</option>';
                }
            echo $output;
        //return response()->json(['data' => $usercheck]);        

    }

    public function create()
    {
        //$users = User::where('active', 1)->whereNotIn('code_for_job_assignment', 'ld')->get();        
        $users = User::where('active', 1)->get();
        $userCreate = substr(Auth::user()->code, 2, 4);  
        //echo $userCreate;die();            
        $userBoss = User::where('active', 1)->where('code_for_job_assignment', 'ld')->get();
        //$districts = District::where('active', 1)->get(['id', 'code', 'name', 'active']);
        $districts = District::where('active', 1);
            if ($userCreate != '4410')
            {
                $districts->where('code', $userCreate);               
            }

            $districts = $districts->get(['id', 'code', 'name', 'active']);


        return $this->view('scoring.create', compact('users', 'districts', 'userBoss'));
    }


    public function store(Request $request)
    {
    
        $validator = Validator::make($request->all(), [
            'ward_id' => 'required',
            'date_check' => 'required',
           // 'user_boss' => 'required',
            'user_check' => 'required',
            'user_view' => 'required',
        ],
            [
                '*.required' => 'Vui lòng nhập đầy đủ thông tin trường bắt buộc.'
            ]
        );
       
        if ($validator->fails()) {
            return back()->withInput()->with('error', [$validator->errors()->first()]);
        }       
        
       // print_r($request->ward_id);die();

        try {
            $userCheck = $request->user_check ? $request->user_check : [];
            $levelCheck = (substr(Auth::user()->code, 2, 4) == '4410' ? 0 : 1);

            DB::beginTransaction();
            $data = [
                'date_check' => $request->date_check,
                'level' => $levelCheck,
                'ward_id' => $request->ward_id,
                'user_1' => (int)$userCheck[0],
                'user_2' => isset($userCheck[1]) ? (int)$userCheck[1] : null ,
                'user_boss' => isset($request->user_boss) ? (int)$request->user_boss : null ,//(int)$request->user_boss
                'status' => 'not_completed',
                'created_by' => time(),
                'updated_by' => time()
            ];
            $scoring = Scoring::create($data);
            $userView = $request->user_view ? $request->user_view : [];
       
            foreach ($userView as $key => $value) {
                ScoringUserView::create([
                    'scoring_id' => (int)$scoring['id'],
                    'user_id' => (int)$value
                ]);
            }
           
            foreach ($userCheck as $key => $value) {
                ScoringUserPermission::create([
                    'scoring_id' => (int)$scoring['id'],
                    'user_id' => (int)$value,
                    'read' => 1,
                    'create' => 1,
                    'update' => 1,
                    'delete' => 1,
                ]);
            }
          //  echo "ddd";die();
            ScoringUserPermission::create([
                'scoring_id' => (int)$scoring['id'],
                'user_id' => (int)$request->user_boss,
                'read' => 1,
                'create' => 1,
                'update' => 1,
                'delete' => 1,
            ]);
            $this->createCdgx($scoring['id']);
            DB::commit();
            return redirect()->route('scoring.index')->with('success', 'Thêm thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', [$e->getMessage()]);
        }
    }

    public function edit($id)
    {
        $users = User::where('active', 1)->get();
        $isAdmin = is_admin(auth()->user());
        $userCreate = substr(Auth::user()->code, 2, 4);
        $scoringUserView = ScoringUserView::where('scoring_id', $id)->with('user')->get();
        $scoringUserPermission = ScoringUserPermission::where('scoring_id', $id)->pluck('user_id')->toArray();
        $userLogin = Auth::user()->id;
        //$userLogin = 3;
        $permission = $this->findAllOccurrences($scoringUserPermission, $userLogin);
        //$districts = District::where('active', 1)->get(['id', 'code', 'name', 'active']);

        $districts = District::where('active', 1);
        if ($userCreate != '4410')
        {
            $districts->where('code', $userCreate);               
        }

        $districts = $districts->get(['id', 'code', 'name', 'active']);
      //  print_r($districts);die();
        $wards = Ward::where('active', 1)->get(['id', 'code', 'name', 'district_id', 'ngaygdx', 'active']);
       // print_r($wards);die();
        $scoring = Scoring::where('id', $id)->first();
        $ward = Ward::where('id', $scoring->ward_id)->first(['id', 'code', 'name', 'district_id']);
        //print_r($wards);die();
        $user_1 = json_decode($scoring->user_1);
        $user_2 = json_decode($scoring->user_2);

        return $this->view('scoring.edit', compact(
            'users', 'districts', 'scoring', 'wards', 'ward', 'user_1', 'user_2', 'scoringUserView',
            'scoringUserPermission', 'userLogin', 'permission','isAdmin'
        ));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'ward_id' => 'required',
            'date_check' => 'required',
            //'user_boss' => 'required',
            'user_check' => 'required',
            'user_view' => 'required',
        ],
            [
                '*.required' => 'Vui lòng nhập đầy đủ thông tin trường bắt buộc.'
            ]
        );
        if ($validator->fails()) {
            return back()->withInput()->with('error', [$validator->errors()->first()]);
        }

        try {
            DB::beginTransaction();
            $userCheck = $request->user_check ? $request->user_check : [];
            $data = [
                'date_check' => $request->date_check,
                'ward_id' => $request->ward_id,
                // 'user_1' => (int)$userCheck[0],
                // 'user_2' => (int)$userCheck[1],
                'user_1' => (int)$userCheck[0],
                'user_2' => isset($userCheck[1]) ? (int)$userCheck[1] : null ,
                'user_boss' => isset($request->user_boss) ? (int)$request->user_boss : null ,//(int)$request->user_boss,
                'created_by' => time(),
                'updated_by' => time()
            ];
            Scoring::where('id', $id)->update($data);
            $userView = $request->user_view ? $request->user_view : [];
            // Cập nhật user view
            ScoringUserView::where('scoring_id', $id)->delete();
            foreach ($userView as $key => $value) {
                ScoringUserView::create([
                    'scoring_id' => $id,
                    'user_id' => (int)$value
                ]);
            }
            DB::commit();
            return redirect()->route('scoring.index')->with('success', 'Sửa thành công!');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::notice("Update scoring failed because " . $e);
            return back()->withInput()->with('error', ['Sửa thất bại!']);
        }

    }

    public function scoringCheck($id)
    {
        $scroingCheck = Cdgdx01::where('scoring_id', $id)->where('parent_id', null)->get();
        $data = [];
        $sumPoint = 0;
        foreach ($scroingCheck as $value) {
            $parent_id = Cdgdx01::where('id', $value->id)->where('parent_id', null)->first();
            $childrentId = Cdgdx01::where('parent_id', $parent_id->id)->pluck('id');
            $sub_children = Cdgdx01::whereIn('parent_id', $childrentId)->sum('point');
            $sumPoint += $sub_children;
            $data[] = [
                'id' => $value->id,
                'parent_id' => $value->parent_id,
                'criteria' => $value->criteria,
                'scoring_id' => $value->scoring_id,
                'point_ladder' => $value->point_ladder,
                'point' => $value->point,
                'note' => $value->note,
                'children' => $this->scoringCheckChildren($value->id),
                'sum_parent_point' => $sub_children,
            ];
        }


        return $this->view('scoring.scoring_check', compact(['data', 'id','sumPoint']));
    }

    private function scoringCheckChildren($id)
    {
        $scroingCheck = Cdgdx01::where('parent_id', $id)->get();
        $data = [];
        foreach ($scroingCheck as $value) {
            $point_count_sub_children = Cdgdx01::where('parent_id', $value->id)->sum('point');
            $data[] = [
                'id' => $value->id,
                'parent_id' => $value->parent_id,
                'criteria' => $value->criteria,
                'scoring_id' => $value->scoring_id,
                'point_ladder' => $value->point_ladder,
                'point' => $value->point,
                'note' => $value->note,
                'sum_children_point' => $point_count_sub_children,
                'sub_children' => $this->scoringCheckChildren($value->id)
            ];
        }

        return $data;
    }

    // update Cdgdx01

    public function updatePoint(Request $request, $id)
    {
        $data = $request->all();
       // dd($data);die();

        try {
            DB::beginTransaction();
            

            foreach ($data['parent'] as $key => $value) {
                // $data = [
                //     'note' => $value['note'],
                // ];
                // Cdgdx01::where('id', $key)->update($data);

                $sumPointParent = 0;

                foreach ($value['children'] as $key_children => $children) {
                    // Cdgdx01::where('id', $children['id'])->update(['note' => $children['note']]);

                    foreach ($children['sub_children'] as $key_sub_children => $sub_children) {
                        $data = [
                            'point' => $sub_children['point'],
                            'note' => $sub_children['note'],
                        ];
                        Cdgdx01::where('id', $sub_children['id'])->update($data);
                    }
                    
                    $sumPointChildren = array_sum(array_map(function ($item) {
                        return (float) $item['point'];
                    }, $children['sub_children']));

                    Cdgdx01::where('id', $children['id'])->update(['point' => $sumPointChildren]);

                    $sumPointParent += $sumPointChildren;
                }

                Cdgdx01::where('id', $value['id'])->update(['point' => $sumPointParent]);
            }
            Scoring::find($id)->update(['status'=> 'completed']);
            DB::commit();
            return redirect()->back()->with('success', 'Cập nhật thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $e->getMessage());
        }
    }


    public function updatePoint_bkup(Request $request, $id)
    {
        $data = $request->all();
        try {
            DB::beginTransaction();
            foreach ($data['parent'] as $key => $value) {
                $data = [
                    'note' => $value['note'],
                ];
                Cdgdx01::where('id', $key)->update($data);
                foreach ($value['children'] as $key_children => $children) {
                    Cdgdx01::where('id', $children['id'])->update(['note' => $children['note']]);
                    foreach ($children['sub_children'] as $key_sub_children => $sub_children) {
                        $data = [
                            'point' => $sub_children['point'],
                            'note' => $sub_children['note'],
                        ];
                        Cdgdx01::where('id', $children['id'])->update(['point' => array_sum([$sub_children['point']])]);
                        Cdgdx01::where('id', $sub_children['id'])->update($data);
                    }
                }
            }
            Scoring::find($id)->update(['status'=> 'completed']);
            DB::commit();
            return redirect()->back()->with('success', 'Cập nhật thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $e->getMessage());
        }
    }



    public function show($id)
    {
       
        $scoring = Scoring::leftJoin('wards', 'wards.id', '=', 'scorings.ward_id')
        ->leftJoin('districts', 'districts.id', '=', 'wards.district_id')
        ->leftJoin('users as user_1', 'user_1.id', '=', 'scorings.user_1')
        ->leftJoin('users as user_2', 'user_2.id', '=', 'scorings.user_2')
        ->leftJoin('users as user_boss', 'user_boss.id', '=', 'scorings.user_boss')
        ->select(
            'scorings.*',
            'wards.code as ward_code',
            'wards.name as ward_name',
            'districts.name as district_name',
            'user_1.name as user_1_name',
            'user_2.name as user_2_name',
            'user_boss.name as user_boss_name')
            ->where('scorings.id', $id)->first();
      //print_r($scoring);die();
        
        $membergdx = Scoring::Join('job_assignments', function ($join) {
            $join->on('job_assignments.ward_id', '=', 'scorings.ward_id');
            $join->on(DB::raw('date(job_assignments.date)') , '=', 'scorings.date_check');
            })->Join('job_assignment_user as job_assig_user', 'job_assig_user.job_assignment_id', '=', 'job_assignments.id')      
            ->Join('users as members', 'members.id', '=', 'job_assig_user.user_id')
            ->Join('wards', 'wards.id', '=', 'scorings.ward_id')
            ->select('scorings.id as room_id','wards.name as ward_name','scorings.date_check as date','members.name as member_name',
                    \DB::raw('(CASE 
                    WHEN job_assig_user.position = "1" THEN "Tổ trưởng"
                    WHEN job_assig_user.position = "2" THEN "Kiểm soát"
                    WHEN job_assig_user.position = "3" THEN "Giao dịch viên chính"
                    WHEN job_assig_user.position = "4" THEN "Giao dịch viên"
                    WHEN job_assig_user.position = "5" THEN "Giao dịch viên"
                    WHEN job_assig_user.position = "6" THEN "Giao dịch viên"
                    WHEN job_assig_user.position = "7" THEN "Lái xe"
                    WHEN job_assig_user.position = "8" THEN "Bảo vệ"
                    WHEN job_assig_user.position = "9" THEN "Lãnh đạo phiên giao dịch"
                    WHEN job_assig_user.position = "10" THEN "Lãnh đạo giám sát trực tiếp"
                    ELSE "Thành phần khác"
                    END) AS position_users'))                  
            ->where('scorings.id', $id)
            ->orderBy('job_assig_user.position', 'ASC')
            ->get();
        
        $scroingCheck = Cdgdx01::where('scoring_id', $id)->where('parent_id', null)->get();
        $data = [];
        $sumPoint = 0;
        foreach ($scroingCheck as $value) {
            $parent_id = Cdgdx01::where('id', $value->id)->where('parent_id', null)->first();
            $childrentId = Cdgdx01::where('parent_id', $parent_id->id)->pluck('id');
            $sub_children = Cdgdx01::whereIn('parent_id', $childrentId)->sum('point');
            $sumPoint += $sub_children;
            $data[] = [
                'id' => $value->id,
                'parent_id' => $value->parent_id,
                'criteria' => $value->criteria,
                'scoring_id' => $value->scoring_id,
                'point_ladder' => $value->point_ladder,
                'point' => $value->point,
                'note' => $value->note,
                'children' => $this->scoringCheckChildren($value->id),
                'sum_parent_point' => $sub_children,
            ];
        }

       return $this->view('scoring.show', compact('scoring', 'membergdx', 'data','sumPoint'));
    }

    public function destroy($id)
    {

        try {
            DB::beginTransaction();
            Cdgdx01::where('scoring_id', $id)->delete();
            ScoringUserView::where('scoring_id', $id)->delete();
            ScoringUserPermission::where('scoring_id', $id)->delete();
            Scoring::where('id', $id)->delete();
            DB::commit();
            return redirect()->back()->with('success', 'Xóa thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::notice("Delete scoring failed because " . $e);
            return back()->withInput()->with('error', ['Xóa thất bại!']);
        }
    }

    public function uploadFile($id)
    {
        $users = User::where('active', 1)->get();
        $districts = District::where('active', 1)->get(['id', 'code', 'name', 'active']);
        $scorings = ScoringFile::where('scoring_id', $id)->get();
        $base_url = url('/');

        return $this->view('scoring.upload_file', compact('users', 'districts', 'id', 'scorings', 'base_url'));
    }

    public function upload(Request $request)
    {


        try {
            $time = date("Y-m-d") ;
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();

            $filePath = $file->storeAs('documents/scoring/'.$time, $fileName, 'public');
            $mime = $file->getClientMimeType();
            $data = [
                'scoring_id' => $request->scoring_id,
                'user_id' => $request->user_id,
                'name' => $fileName,
                'url' => '/storage/' . $filePath,
                'type' => $mime,
                'description' => $request->description,
            ];

            ScoringFile::create($data);
            return redirect()->back()->with('success', 'Upload file thành công!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', ['Upload file thất bại!']);
        }
    }

    public function generatePDF($id)
    {
       
        $scoring = Scoring::leftjoin('wards', 'wards.id', '=', 'scorings.ward_id')
            ->leftJoin('users as users_1', 'users_1.id', '=', 'scorings.user_1')
            ->leftJoin('users as users_2', 'users_2.id', '=', 'scorings.user_2')
            ->leftJoin('users as user_boss', 'user_boss.id', '=', 'scorings.user_boss')
            ->leftJoin('districts', 'districts.id', '=', 'wards.district_id')
            ->select(
                'scorings.*',
                'wards.code as ward_code',
                'wards.name as ward_name', // Tên của xã/phường
                'users_1.name as user_1_name', // Tên của user_1
                'users_2.name as user_2_name', // Tên của user_1
                'user_boss.name as users_boss_name', // Tên của user_1
                'wards.district_id as district_id',
                'districts.name as district_name'
            )->where('scorings.id', $id)->first();
   // print_r($scoring);die();  
    
        $membergdx = Scoring::Join('job_assignments', function ($join) {
            $join->on('job_assignments.ward_id', '=', 'scorings.ward_id');
            $join->on(DB::raw('date(job_assignments.date)') , '=', 'scorings.date_check');
            })->Join('job_assignment_user as job_assig_user', 'job_assig_user.job_assignment_id', '=', 'job_assignments.id')      
            ->Join('users as members', 'members.id', '=', 'job_assig_user.user_id')
            ->Join('wards', 'wards.id', '=', 'scorings.ward_id')
            ->select('scorings.id as room_id','wards.name as ward_name','scorings.date_check as date','members.name as member_name',
                    \DB::raw('(CASE 
                    WHEN job_assig_user.position = "1" THEN "Tổ trưởng"
                    WHEN job_assig_user.position = "2" THEN "Kiểm soát"
                    WHEN job_assig_user.position = "3" THEN "Giao dịch viên chính"
                    WHEN job_assig_user.position = "4" THEN "Giao dịch viên"
                    WHEN job_assig_user.position = "5" THEN "Giao dịch viên"
                    WHEN job_assig_user.position = "6" THEN "Giao dịch viên"
                    WHEN job_assig_user.position = "7" THEN "Lái xe"
                    WHEN job_assig_user.position = "8" THEN "Bảo vệ"
                    WHEN job_assig_user.position = "9" THEN "Lãnh đạo phiên giao dịch"
                    WHEN job_assig_user.position = "10" THEN "Lãnh đạo giám sát trực tiếp"
                    ELSE "Thành phần khác"
                    END) AS position_users'))                  
            ->where('scorings.id', $id)
            ->orderBy('job_assig_user.position', 'ASC')
            ->get();

            $scroingCheck = Cdgdx01::where('scoring_id', $id)->where('parent_id', null)->get();
            $data = [];
            $sumPoint = 0;
            foreach ($scroingCheck as $value) {
                $parent_id = Cdgdx01::where('id', $value->id)->where('parent_id', null)->first();
                $childrentId = Cdgdx01::where('parent_id', $parent_id->id)->pluck('id');
                $sub_children = Cdgdx01::whereIn('parent_id', $childrentId)->sum('point');
                $sumPoint += $sub_children;
                $data[] = [
                    'id' => $value->id,
                    'parent_id' => $value->parent_id,
                    'criteria' => $value->criteria,
                    'scoring_id' => $value->scoring_id,
                    'point_ladder' => $value->point_ladder,
                    'point' => $value->point,
                    'note' => $value->note,
                    'children' => $this->scoringCheckChildren($value->id),
                    'sum_parent_point' => $sub_children,
                ];
            }
        
        $pdf = \PDF::loadView('pages.scoring.template', compact(['scoring', 'membergdx', 'data','sumPoint']));
      //  $pdf->output();
        $domPdf = $pdf->getDomPDF();
        $canvas = $domPdf->get_canvas();
        $canvas->page_text(555, 810, "{PAGE_NUM}/{PAGE_COUNT}", 'timesnewroman', 9, array(.5,.5,.5));
        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->stream('cdgdx01_'. time().'.pdf', array("Attachment" => false));

    }

    public function showPermission()
    {
        return $this->view('scoring.permission');
    }

    public function createPermission(Request $request)
    {
        try {
            $scoringPermission = ScoringUserPermission::created([
                'scoring_id' => $request->scoring_id,
                'user_id' => $request->user_id,
                'read' => $request->read,
                'update' => $request->update,
                'create' => $request->create,
                'delete' => $request->delete,
            ]);

            return redirect()->back()->with('success', 'Thêm thành công!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', ['Thêm thất bại!']);
        }
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
            'Tiêu chí',
            'Thang điểm',
            'Chấm điểm',
            'Ghi Chú',
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

    public function createCdgx($scoring_id): string
    {

        $result = Codegdx::get();
        $parent = Codegdx::where('parent_id', NULL)->get();

        Cdgdx01::where('scoring_id', $scoring_id)->delete();
        foreach ($parent as $value) {
            $scoringCheckArr = [
                'parent_id' => NULL,
                'criteria' => $value->criteria,
                'scoring_id' => $scoring_id,
                'point_ladder' => $value->point_ladder,
                'point' => 0,
                'note' => $value->note,
                'tt_hienthi' => $value->tt_hienthi,
                'ma' => $value->ma,
            ];

            $parentId = Cdgdx01::create($scoringCheckArr);
            $childrens = Codegdx::where('parent_id', $value->id)->get();
            foreach ($childrens as $childrenValue) {
                $children = [
                    'parent_id' => $parentId->id,
                    'criteria' => $childrenValue->criteria,
                    'scoring_id' => $scoring_id,
                    'point_ladder' => $childrenValue->point_ladder,
                    'point' => 0,
                    'note' => $childrenValue->note,
                    'tt_hienthi' => $childrenValue->tt_hienthi,
                    'ma' => $childrenValue->ma,
                ];
                $childrenId = Cdgdx01::create($children);
                // update point parent
                $subChildren = Codegdx::where('parent_id', $childrenValue->id)->get();
                foreach ($subChildren as $sub) {
                    $subChildren = [
                        'parent_id' => $childrenId->id,
                        'criteria' => $sub->criteria,
                        'scoring_id' => $scoring_id,
                        'point_ladder' => $sub->point_ladder,
                        'point' => $sub->point,
                        'note' => $sub->note,
                        'tt_hienthi' => $sub->tt_hienthi,
                        'ma' => $sub->ma,
                    ];

                    Cdgdx01::create($subChildren);
                }
            }
        }
    return 'success';

    }
    public function syncData($id) { 
        $result = [
            'status' => 'success',
            'msg' => 'Đồng bộ dữ liệu thành công.',
        ];
        //dd($id);die();

        try {
            $scoring = Scoring::leftJoin('wards', 'wards.id', '=', 'scorings.ward_id')
            ->leftJoin('districts', 'districts.id', '=', 'wards.district_id')
            ->leftJoin('users as user_1', 'user_1.id', '=', 'scorings.user_1')
            ->select(
                'scorings.date_check',
                'scorings.user_1',
                'wards.name as ward_name',
                'wards.code as ward_code',
                'districts.code as district_code',
            )      
            ->where('scorings.id', $id)
            ->firstOrFail();

            $user = User::findOrFail($scoring->user_1);
           
            // $dataSync = [
            //     "key" => "KTGS_01GDX", //KHOA
            //     "orderValue" => 0, //ThUTU
            //     "orderDescription" => "", //TT_HIENTHI
            //     "code" => "",  //MA  Mã điểm giao dịch:
            //     "name" => "", //TEN
            //     "reportDate" => $scoringIP->date_check, //NGAYBC
            //     "reportYear" => 0, //NAMBC
            //     "posCode" =>  $scoringIP->district_code, //MAPGD = '00' + district_code 
            //     "posFlag" => "", //CO_TONGHOP
            //     "branchCode" => "004410", // 004410 la co dinh
            //     "makerId" => "", //NGUOI_NHAP lay ben user table
            //     "makerDate" => "2024-11-20T08:53:25.226Z", //NGAY_NHAP lay ben scoring 'date_check'
            //     "authoriseId" => "", //NGUOI_DUYET lay ben user  = NGUOI_NHAP
            //     "authoriseDate" => "2024-11-20T08:53:25.226Z",  //date_check lay them HH:MM:SS
            //     // "d1" => //Thang diem
            //     // "d2" => //Ma don vij lay ben export PDF  Mã điểm giao dịch:
            //     // "d3" => //CO dinh nhu file excel
            //     // "d4" => //Point (diem cham)
            //     // "d5" => //Lau ben user
            //     // "d6" => //don_vi_cong_tac lau ben user
            //     // "d7" => //'TXN0'+  Mã điểm giao dịch
            //     // "d8" => //Chuc_vu lay ben user
            //     // "d9" => //Phan mo ta lay ben Descrip
            //     // "d10" => //tu 10 den 49 de trong
            //     "manualFlag" => "",//NHAPTAY
            //     "fontFormat" => "",
            //     "style" => 0
            // ];

            // $orderPoints = [
            //     "thoi-gian-giao-dich",
            //     "thu-nhan-hinh-anh-qua-camera-ip",
            //     "khong-gian-giao-dich",
            //     "can-bo-tham-gia-to-giao-dich-xa",
            //     "trang-thiet-bi-cong-cu-lam-viec-cua-to-giao-dich-xa",
            //     "thuc-hien-quy-trinh-giao-dich",
            //     "hop-giao-ban",
            //     "cong-tac-phoi-hop-cua-to-chuc-chinh-tri-xa-hoi-nhan-uy-thac-cap-xa",
            //     "cong-tac-bao-ve",
            //     "kiem-quy-cuoi-phien-giao-dich",
            // ];

            // $points = Cdgdx04::where('scoring_id', $id)
            //     ->select('point', 'criteria')
            //     ->get()
            //     ->mapWithKeys(function ($row, $index) {
            //         $slug = Str::slug($row->criteria);
            //         return [$slug => $row->point];
            //     })
            //     ->toArray();
            //     $points = collect(array_merge(array_intersect_key(array_flip($orderPoints), $points), $points))
            //     ->values()
            //     ->mapWithKeys(function ($value, $index) {
            //         $key = 'd' . ($index + 1);
            //         return [$key => $value];
            //     })
            //     ->toArray();
            
            // for ($i = 1; $i <= 50; $i++) {
            //     $key = "d${i}";
            //     if (!array_key_exists($key, $points)) {
            //         $points[$key] = "";
            //     }
            // }

            // $dataSync = array_merge($dataSync, $points);

            $dataSync = [];
            $data = Cdgdx01::where('scoring_id', $id)
                ->get()
                ->each(function ($item, $index) use ($scoring, $user, &$dataSync) {
                    $key = "KTGS_01GDX";
                    $reportDate = $scoring->date_check;
                    // $reportDateUnixTimestamp = Carbon::parse($reportDate)->timestamp;
                    // $reportDateUTC = Carbon::parse($reportDate, 'UTC')->format('Y-m-d\TH:i:s.v\Z');
                    $reportYear = date('Y', strtotime($reportDate));
                    $posCode = "00{$scoring->district_code}";
                    $posFlag = $user->co_tong_hop;                   
                    $commonData = [
                        "key" => $key, //KHOA
                        "reportDate" => $reportDate, //"reportDate" => $reportDate,
                        "reportYear" => (int)$reportYear, //NAMBC
                        "posCode" =>  $posCode, //MAPGD = '00' + district_code 
                        "posFlag" => $posFlag, //CO_TONGHOP
                        "branchCode" => "004410", // 004410 la co dinh
                        "manualFlag" => "",//NHAPTAY
                        "makerId" => $user->nguoi_nhap, //NGUOI_NHAP lay ben user table
                        "makerDate" => $reportDate, //NGAY_NHAP lay ben scoring 'date_check'
                        "authoriseId" => $user->nguoi_nhap, //NGUOI_DUYET lay ben user  = NGUOI_NHAP
                        "authoriseDate" => $reportDate,  //date_check lay them HH:MM:SS
                        "fontFormat" => "",
                        "style" => 0,
                        "d1" => (string)$item->point_ladder,
                        "d2" => (string)$scoring->ward_code,
                        "d3" => (string)$item->ma,
                        "d4" => (string)$item->point,
                        "d5" => (string)$user->id_can_bo,
                        "d6" => (string)$user->ma_don_vi_cong_tac,
                        "d7" => (string)"TXN0{$scoring->ward_code}",
                        "d8" => (string)$user->ma_chuc_vu,
                        "d9" => (string)$item->note,
                    ];
                   
                    for($i = 10; $i <= 50; $i++) {
                        $commonData["d{$i}"] = ($i == 50) ? "1" : "";
                    }

                     $i = ++$index;
                    ////$i = $index * 2 + 1;
                    //$code = "{$scoring->ward_code}_GDX01_" .  ($index === 9 ? 'a' : ($index + 1));  
                                     
                    $dataSync[] = array_merge($commonData, [
                        "name" => $item->criteria, //TEN
                        "orderValue" => $i, //ThUTU
                        "orderDescription" => ($item->tt_hienthi == null ? "" : $item->tt_hienthi), //TT_HIENTHI
                        "code" => "{$scoring->ward_code}_" .$item->ma  //MA  Mã điểm giao dịch:
                       
                    ]);
                   
                    // $dataSync[] = array_merge($commonData, [
                    //     "name" => $item->nd_criteria, //TEN
                    //     "orderValue" => $i + 1, //ThUTU
                    //     "orderDescription" => "", //TT_HIENTHI
                    //     "code" => "{$code}1",  //MA  Mã điểm giao dịch:
                    //     "d3" => "GDX01_". ($i + 1) ."1"
                    // ]);
                });
                
            if (empty($dataSync)) {
                throw new \Exception("");
            }

            $key = $dataSync[0]['key'];
            $posCode = $dataSync[0]['posCode'];
            $posFlag = $dataSync[0]['posFlag'];
            $reportDate = str_replace('-', '', $dataSync[0]['reportDate']);
            $reportDate = str_replace('-', '', Carbon::parse($dataSync[0]['reportDate'])->format('Y-m-d'));
            
            //dd($dataSync[2]);
            //dd(array_column($dataSync, 'd9'));

            //$apiUrl = "http://10.63.52.52:8005/api/v1/ktksnb-update-data?key=${key}&posCode=${posCode}&posFlag=${posFlag}&reportDate=${reportDate}";
            $apiUrl = "http://10.63.16.52:8005/api/v1/ktksnb-update-data?key=${key}&posCode=${posCode}&posFlag=${posFlag}&reportDate=${reportDate}";
        
            $response = Http::withHeaders([
                'Accept' => 'text/plain',
                'Content-Type' => 'application/json'
            ])->post($apiUrl, $dataSync);
            
            if ($response->failed()) {
               // dd($response->status(), $response->body());
                throw new \Exception("");
            }
        } catch (\Exception $e) {
            // dd($e->getMessage());
            $result['status'] = 'error';
            $result['msg'] = 'Đông bộ dữ liệu thất bại.<br> Vui lòng thử lại!';
        }

        return redirect()->back()->with('syncData', $result);
    }

}
