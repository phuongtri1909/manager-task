<?php


namespace App\Http\Controllers;

use App\Helpers\SystemDefine;
use App\Models\Cdgdx04;
use App\Models\District;
use App\Models\ScoringIP;
use App\Models\ScoringFileIP;
use App\Models\ScoringIPUserPermission;
use App\Models\ScoringIPUserView;
use App\Models\User;
use App\Models\Ward;
use App\Models\Codegdxip;
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

class ScoringIPController extends Controller
{

    public string $title = 'Giám sát điểm GDX qua CamIP';

    public function __construct()
    {
        $this->featureSlug = SystemDefine::CHECK_GDX_IP_FEATURE;
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
       
        //$fromDate = $request->input('fromDate');
        //$toDate = $request->input('toDate');
        // $fromDate = request()->fromDate ?? \Carbon\Carbon::now()->format('Y-m-01');          
        // $toDate = request()->toDate ?? \Carbon\Carbon::now()->format('Y-m-t');  
        $month = request()->month ?? now()->format('m/Y');
    //   print_r($month);die();
    //   $fromDate = \Carbon\Carbon::now()->format('Y-m-01');     
    //   $toDate = \Carbon\Carbon::now()->format('Y-m-t'); 
        $userLogin = Auth::user()->id;
        $isAdmin = is_admin(auth()->user());        
        $featureSlug = "quan_ly_giam_sat_gdx_ip";

        $checkPermission  = User::where('id', $userLogin)
            ->whereHas('permissionScoringIP', function ($query) use ($featureSlug) {
                $query->where('feature_slug', $featureSlug)->where('permission_slug', 'truy_cap');
            })
            ->first();


        if(!$checkPermission && !$isAdmin)
        {
            return redirect()->back();
        }

        $canCreate = User::where('id', $userLogin)
        ->whereHas('permissionScoringIP', function ($query) use ($featureSlug) {
            $query->where('feature_slug', $featureSlug)->where('permission_slug', 'them');
        })
        ->first();
// echo '<pre>';
// print_r($canCreate);
// echo '</pre>';die();

        $canUpdate = User::where('id', $userLogin)
        ->whereHas('permissionScoringIP', function ($query) use ($featureSlug) {
            $query->where('feature_slug', $featureSlug)->where('permission_slug', 'sua');
        })
        ->first();

        $canDelete = User::where('id', $userLogin)
        ->whereHas('permissionScoringIP', function ($query) use ($featureSlug) {
            $query->where('feature_slug', $featureSlug)->where('permission_slug', 'xoa');
        })
        ->first();

        $canViewAll = User::where('id', $userLogin)
        ->whereHas('permissionScoringIP', function ($query) use ($featureSlug) {
            $query->where('feature_slug', $featureSlug)->where('permission_slug', 'xem_tat_ca');
        })
        ->first();

        
        $scorings = ScoringIP::leftjoin('wards', 'wards.id', '=', 'scoringsip.ward_id')  
            ->leftJoin('districts', 'districts.id', '=', 'wards.district_id')          
            ->leftJoin('users as user_1', 'user_1.id', '=', 'scoringsip.user_1')
            ->leftJoin('scoringip_user_views','scoringip_user_views.scoring_id','=','scoringsip.id')
            ->select(
                'scoringsip.*',
                'wards.code as ward_code',
                'wards.name as ward_name', // Tên của xã/phường 
                'districts.name as district_name',               
                'user_1.name as users_1_name' // Tên của user ktra
            )->groupBy('scoringip_user_views.scoring_id');
            
       // if (!empty($fromDate) && !empty($toDate)) {
       //  $scorings->whereBetween('date_check', [$fromDate, $toDate]);
       //  $scorings->whereMonth('date_check', $month);

           $date = Carbon::createFromFormat('m/Y', $month);

           $scorings->whereMonth('date_check', $date->month)      
               ->whereYear('date_check', $date->year); 


       // }
        if(empty($canViewAll) && !$isAdmin)
        {
            // $scorings->whereMonth('date_check', $month)
            // ->where(function($query) use ($userLogin){
            //         $query->where('scoringsip.user_1', $userLogin);
            //         $query->orWhere('scoringip_user_views.user_id',$userLogin);
            // });  
            
            $scorings->where(function($query) use ($userLogin){
                $query->where('scoringsip.user_1', $userLogin);
                $query->orWhere('scoringip_user_views.user_id',$userLogin);               
            });
                             
        }

        $scorings = $scorings->orderBy('scoringsip.date_check', 'ASC')->get();
        
    //  print_r(json_encode($scorings));die;
        foreach ($scorings as $key => $value) {

            $permission_admin = ScoringIPUserPermission::where('scoring_id', $value->id)->where('user_id', $userLogin)->first();           
           
            // User chỉ được view

            $permission_scoring = ScoringIPUserView::where('scoring_id', $value->id)->where('user_id', $userLogin)->first();
                    
            // End
            $isUser1 = ScoringIP::where('id', $value->id)->where('user_1', $userLogin)->first();
            // ->orWhere('user_2', $userLogin)
            //->first();

            $value['permission_scoring'] = $permission_scoring ? 1 : 0 ;
            $value['read'] = $permission_admin ? $permission_admin->read : 0 ;
            $value['update'] = $permission_admin ? $permission_admin->update : 0 ;
            $value['delete'] = $permission_admin ? $permission_admin->delete : 0 ;
            $value['isUser1'] = $isUser1  ? 1 : 0 ;
        }
    
       $dataTableData      = $this->generateDataTableData();
     
       return $this->view('scoringip.index', compact('scorings','dataTableData','userLogin','isAdmin','canCreate','canUpdate','canViewAll','canDelete'));

    }
 
    public function getWards($id_dt)
    {
        if ($id_dt) {
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


        return $this->view('scoringip.create', compact('users', 'districts', 'userBoss'));
    }


    public function store(Request $request)
    {
    
        $validator = Validator::make($request->all(), [
            'ward_id' => 'required',
            'date_check' => 'required',           
            'user_check' => 'required',           
        ],
            [
                '*.required' => 'Vui lòng nhập đầy đủ thông tin trường bắt buộc.'
            ]
        );
       
        if ($validator->fails()) {
            return back()->withInput()->with('error', [$validator->errors()->first()]);
        }       
       
        try {
            $userCheck = $request->user_check ? $request->user_check : '';
            //$levelCheck = ((substr(Auth::user()->code, 2, 4) == '4410') && (Auth::user()->name != 'Lê Thành Công') ? 0 : 1);
            $levelCheck = (Auth::user()->name == 'Lê Thành Công' ? 1 : (substr(Auth::user()->code, 2, 4) == '4410' ? 0 : 1));
            DB::beginTransaction();
            $data = [
                'date_check' => $request->date_check,
                'level' => $levelCheck,
                'ward_id' => $request->ward_id,
                'user_1' => (int)$request->user_check,
                'status' => 'not_completed',
                'created_by' => time(),
                'updated_by' => time()
            ];
            $scoring = ScoringIP::create($data);
            $userView = $request->user_view ? $request->user_view : [];
       
            foreach ($userView as $key => $value) {
                ScoringIPUserView::create([
                    'scoring_id' => (int)$scoring['id'],
                    'user_id' => (int)$value
                ]);
            }
           
            ScoringIPUserPermission::create([
                'scoring_id' => (int)$scoring['id'],
                'user_id' => (int)$value,
                'read' => 1,
                'create' => 1,
                'update' => 1,
                'delete' => 1,
            ]);
            
          //  echo "ddd";die();
            // ScoringIPUserPermission::create([
            //     'scoring_id' => (int)$scoring['id'],
            //     'user_id' => (int)$request->user_boss,
            //     'read' => 1,
            //     'create' => 1,
            //     'update' => 1,
            //     'delete' => 1,
            // ]);
            $this->createCdgx($scoring['id']);
            DB::commit();
            return redirect()->route('scoringip.index')->with('success', 'Thêm thành công!');
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
        $scoringUserView = ScoringIPUserView::where('scoring_id', $id)->with('user')->get();
        $scoringUserPermission = ScoringIPUserPermission::where('scoring_id', $id)->pluck('user_id')->toArray();
        $userLogin = Auth::user()->id;
        //$userLogin = 3;
        $permission = $this->findAllOccurrences($scoringUserPermission, $userLogin);
        $permission = 1;
        //$districts = District::where('active', 1)->get(['id', 'code', 'name', 'active']);
//print_r($userLogin);die();
        $districts = District::where('active', 1);
        if ($userCreate != '4410')
        {
            $districts->where('code', $userCreate);               
        }

        $districts = $districts->get(['id', 'code', 'name', 'active']);
      //  print_r($districts);die();
        $wards = Ward::where('active', 1)->get(['id', 'code', 'name', 'district_id', 'ngaygdx', 'active']);
       // print_r($wards);die();
        $scoring = ScoringIP::where('id', $id)->first();
        $ward = Ward::where('id', $scoring->ward_id)->first(['id', 'code', 'name', 'district_id']);
        //print_r($wards);die();
        $user_1 = json_decode($scoring->user_1);
        //$user_2 = json_decode($scoring->user_2);

        return $this->view('scoringip.edit', compact(
            'users', 'districts', 'scoring', 'wards', 'ward', 'user_1', 'scoringUserView',
            'scoringUserPermission', 'userLogin', 'permission','isAdmin'
        ));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'ward_id' => 'required',
            'date_check' => 'required',
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
            //$userCheck = $request->user_check;
            $data = [
                'date_check' => $request->date_check,
                'ward_id' => $request->ward_id,
                'user_1' => (int)$userCheck[0],
                'created_by' => time(),
                'updated_by' => time()
            ];
            ScoringIP::where('id', $id)->update($data);
            $userView = $request->user_view ? $request->user_view : [];
            // Cập nhật user view
            ScoringIPUserView::where('scoring_id', $id)->delete();
            foreach ($userView as $key => $value) {
                ScoringIPUserView::create([
                    'scoring_id' => $id,
                    'user_id' => (int)$value
                ]);
            }
            DB::commit();
            return redirect()->route('scoringip.index')->with('success', 'Sửa thành công!');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::notice("Update scoring failed because " . $e);
            return back()->withInput()->with('error', ['Sửa thất bại!']);
        }

    }

    public function scoringIPCheck(Request $request, $id)
    {
        $scroingCheck = Cdgdx04::where('scoring_id', $id)->get();
        $data = [];
        $sumPoint = 0;
        foreach ($scroingCheck as $value) {           
            $parent_id = Cdgdx04::where('id', $value->id)->pluck('id');                     
            $sub_children = Cdgdx04::whereIn('id', $parent_id)->sum('point');          
            $sumPoint += $sub_children;
            $data[] = [
                'id' => $value->id,
                'scoring_id' => $value->scoring_id,
                'criteria' => $value->criteria,
                'nd_criteria' => $value->nd_criteria,                
                'point_ladder' => $value->point_ladder,
                'point' => $value->point,
                'note' => $value->note,
                'file' => $value->file,
                'file_second' => $value->file_second,
                'file_third' => $value->file_third,
               // 'children' => $this->scoringCheckChildren($value->id),
                'sum_parent_point' => $sub_children,
            ];
        }


        return $this->view('scoringip.scoring_check', compact(['data', 'id','sumPoint']));
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
                'sum_children_point' => $point_count_sub_children,
                'sub_children' => $this->scoringCheckChildren($value->id)
            ];
        }

        return $data;
    }

    // update Cdgdx01

    public function updatePoint(Request $request, $id)
    {
        $dataRq = $request->all();
        try {
            DB::beginTransaction();            
            foreach ($dataRq['parent'] as $key => $value) {
                $sql = Cdgdx04::where('id', $key)->first();
                    $data = [
                        'point' => $value['point'],
                        'note' => $value['note'],
                    ];

                    if (isset($value['del']) && !empty($value['del'])) {
                        foreach ($value['del'] as $keyDel => $valueDel) {
                            if($valueDel) {
                                if (!empty($sql->$keyDel)) {
                                    $pathOld = public_path() . '/storage/image/ScoringIp/' . $key . '/' . $sql->$keyDel;
                                    if (file_exists($pathOld)) {
                                        unlink($pathOld);
                                    }
                                }

                                $data[$keyDel] = null;
                            }
                        }
                    }

                    if (isset($value['file']) && !empty($value['file'])) {
                        foreach ($value['file'] as $keyFile => $valueFile) {
                            $fileName = $valueFile->getClientOriginalName();
                            $path = '/image/ScoringIp/' . $key . '/' . $fileName;
                            
                            $upload = Storage::disk('public')->put($path, file_get_contents($valueFile));

                            $data[SystemDefine::NAME_FILE_COL($keyFile)] = $fileName;
                        }
                    }
                 //   Cdgdx04::where('id', $key)->update($data);
                 $sql->update($data);                                
            }
            
            ScoringIP::find($id)->update(['status'=> 'completed']);
            DB::commit();
            return redirect()->back()->with('success', 'Cập nhật thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    // public fucntion generatePath()
    // {

    // }

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
        $isPdf = false;
        $scoring = ScoringIP::leftJoin('wards', 'wards.id', '=', 'scoringsip.ward_id')
        ->leftJoin('districts', 'districts.id', '=', 'wards.district_id')
        ->leftJoin('users as user_1', 'user_1.id', '=', 'scoringsip.user_1')       
        ->select(
            'scoringsip.*',
            'wards.code as ward_code',
            'wards.name as ward_name',
            'districts.name as district_name',
            'user_1.name as user_1_name')            
            ->where('scoringsip.id', $id)->first();
      //print_r($scoring);die();

        $membergdx = ScoringIP::Join('job_assignments', function ($join) {
            $join->on('job_assignments.ward_id', '=', 'scoringsip.ward_id');
            $join->on(DB::raw('date(job_assignments.date)') , '=', 'scoringsip.date_check');
            })->Join('job_assignment_user as job_assig_user', 'job_assig_user.job_assignment_id', '=', 'job_assignments.id')      
            ->Join('users as members', 'members.id', '=', 'job_assig_user.user_id')
            ->Join('wards', 'wards.id', '=', 'scoringsip.ward_id')
            ->select('scoringsip.id as room_id','wards.name as ward_name','scoringsip.date_check as date','members.name as member_name',
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
            ->where('scoringsip.id', $id)
            ->orderBy('job_assig_user.position', 'ASC')
            ->get();
        
        $scroingCheck = Cdgdx04::where('scoring_id', $id)->get();
        //print_r($scroingCheck);die();
        $data = [];
        $sumPoint = 0;
        foreach ($scroingCheck as $value) {
            $fileBase64 = '';
            $parent_id = Cdgdx04::where('id', $value->id)->pluck('id');           
            $sub_children = Cdgdx04::whereIn('id', $parent_id)->sum('point');
            $sumPoint += $sub_children;

            $data[] = [
                'id' => $value->id,
                'scoring_id' => $value->scoring_id,
                'criteria' => $value->criteria,
                'nd_criteria' => $value->nd_criteria,
                'point_ladder' => $value->point_ladder,
                'point' => $value->point,
                'note' => $value->note,
                'file' => $this->fileToBase64($value->id, $value->file),
                'file_second' => $this->fileToBase64($value->id, $value->file_second),
                'file_third' => $this->fileToBase64($value->id, $value->file_third),
                // 'children' => $this->scoringCheckChildren($value->id),
                'sum_parent_point' => $sub_children,
            ];
        }

        return $this->view('scoringip.show', compact('scoring', 'membergdx', 'data','sumPoint', 'isPdf'));
    }

    public function destroy($id)
    {

        try {
            DB::beginTransaction();
            Cdgdx04::where('scoring_id', $id)->delete();
            ScoringIPUserView::where('scoring_id', $id)->delete();
            ScoringIPUserPermission::where('scoring_id', $id)->delete();
            ScoringIP::where('id', $id)->delete();
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

        return $this->view('scoringip.upload_file', compact('users', 'districts', 'id', 'scorings', 'base_url'));
    }

    public function upload(Request $request)
    {


        try {
            $time = date("Y-m-d") ;
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();

            $filePath = $file->storeAs('documents/scoringip/'.$time, $fileName, 'public');
            $mime = $file->getClientMimeType();
            $data = [
                'scoring_id' => $request->scoring_id,
                'user_id' => $request->user_id,
                'name' => $fileName,
                'url' => '/storage/' . $filePath,
                'type' => $mime,
                'description' => $request->description,
            ];

            ScoringIPFile::create($data);
            return redirect()->back()->with('success', 'Upload file thành công!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', ['Upload file thất bại!']);
        }
    }

    public function generatePDF($id)
    {
       $isPdf = true;

        $scoring = ScoringIP::leftjoin('wards', 'wards.id', '=', 'scoringsip.ward_id')
            ->leftJoin('users as users_1', 'users_1.id', '=', 'scoringsip.user_1')            
            ->leftJoin('districts', 'districts.id', '=', 'wards.district_id')
            ->select(
                'scoringsip.*',
                'wards.code as ward_code',
                'wards.name as ward_name', // Tên của xã/phường
                'users_1.name as user_1_name', // Tên của user_1                
                'wards.district_id as district_id',
                'districts.name as district_name'
            )->where('scoringsip.id', $id)->first();
   // print_r($scoring);die();  
    
        $membergdx = ScoringIP::Join('job_assignments', function ($join) {
            $join->on('job_assignments.ward_id', '=', 'scoringsip.ward_id');
            $join->on(DB::raw('date(job_assignments.date)') , '=', 'scoringsip.date_check');
            })->Join('job_assignment_user as job_assig_user', 'job_assig_user.job_assignment_id', '=', 'job_assignments.id')      
            ->Join('users as members', 'members.id', '=', 'job_assig_user.user_id')
            ->Join('wards', 'wards.id', '=', 'scoringsip.ward_id')
            ->select('scoringsip.id as room_id','wards.name as ward_name','scoringsip.date_check as date','members.name as member_name',
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
            ->where('scoringsip.id', $id)
            ->orderBy('job_assig_user.position', 'ASC')
            ->get();

            $scroingCheck = Cdgdx04::where('scoring_id', $id)->get();
            $data = [];
            $sumPoint = 0;
            foreach ($scroingCheck as $value) {
                $parent_id = Cdgdx04::where('id', $value->id)->pluck('id');           
                $sub_children = Cdgdx04::whereIn('id', $parent_id)->sum('point');
                $sumPoint += $sub_children;
                
                $data[] = [
                    'id' => $value->id,   
                    'scoring_id' => $value->scoring_id,                 
                    'criteria' => $value->criteria,
                    'nd_criteria' => $value->nd_criteria,                    
                    'point_ladder' => $value->point_ladder,
                    'point' => $value->point,
                    'note' => $value->note,
                    'file' => $value->file,
                    'file_second' => $value->file_second,
                    'file_third' => $value->file_third,
                   // 'children' => $this->scoringCheckChildren($value->id),
                    'sum_parent_point' => $sub_children,
                ];
            }
       // print_r($sumPoint);die();
        $pdf = \PDF::loadView('pages.scoringip.template', compact(['scoring', 'membergdx', 'data','sumPoint', 'isPdf']));
      //  $pdf->output();
        $domPdf = $pdf->getDomPDF();
        $canvas = $domPdf->get_canvas();
        $canvas->page_text(555, 810, "{PAGE_NUM}/{PAGE_COUNT}", 'timesnewroman', 9, array(.5,.5,.5));
        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->stream('cdgdxip4_'. time().'.pdf', array("Attachment" => false));

    }

    public function showPermission()
    {
        return $this->view('scoring.permission');
    }

    public function createPermission(Request $request)
    {
        try {
            $scoringPermission = ScoringIPUserPermission::created([
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
            'Ngày kiểm tra',
            'Cấp kiểm tra',
            'Tên xã/phường',
            'Tên huyện/TP',
            'Cán bộ giám sát',
            'Kết quả giám sát',
            'Tình trạng',
        ];

        $config = [
            'order'     => [[0, 'asc']],
            'columns'   => array_merge(
                [['type' => 'num']],
                array_fill(0, 6, null),
                [['orderable' => false]]
            ),
            'language'  => [
                'url'   => asset('vendor/vi.json'),
            ],
        ];

        return ['config' => $config, 'heads' => $heads];
    }

    public function createCdgx($scoring_id): string
    {

        // $result = Codegdxip::get();
        // Cdgdx04::where('scoring_id', $scoring_id)->delete();
        // foreach ($result as $sub) {
        //     $sub = [
        //         'parent_id' => $sub->id,
        //         'criteria' => $sub->criteria,
        //         'nd_criteria' => $sub->nd_criteria,
        //         'scoring_id' => $scoring_id,
        //         'point_ladder' => $sub->point_ladder,
        //         'point' => $sub->point,                
        //         'note' => $sub->note,
        //         'file' => $sub->file,
        //     ];

        //     Cdgdx04::create($sub);


        // }
        $result = Codegdxip::get();
        $parent = Codegdxip::get();

        Cdgdx04::where('scoring_id', $scoring_id)->delete();
        foreach ($parent as $value) {
            $scoringCheckArr = [                                           
                'criteria' => $value->criteria,
                'nd_criteria' => $value->nd_criteria,
                'scoring_id' => $scoring_id,
                'point_ladder' => $value->point_ladder,
                'point' => $value->point,
                'note' => $value->note,
                'file' => $value->file,
            ];
            $parentId = Cdgdx04::create($scoringCheckArr);         
            
        }      
          
    return 'success';
    }

    private function fileToBase64($id, $fileName)
    {
        $fileBase64 = null;

        if (!empty($fileName)) {
            $path = public_path() . '/storage/image/ScoringIp/'. $id . '/' . $fileName;
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $dataImage = file_get_contents($path);
            $fileBase64 = base64_encode($dataImage);
        }

        return $fileBase64;
    }

    public function deletefilethird($id)
    {
        print_r($id);die();
        {
            $del = File::find($id);           
            Storage::delete($del->path);
            $del->delete();
            return redirect('/home');
        }

        // try {
        //     DB::beginTransaction();
        //     Cdgdx04::where('scoring_id', $id)->delete();
        //     ScoringIPUserView::where('scoring_id', $id)->delete();
        //     ScoringIPUserPermission::where('scoring_id', $id)->delete();
        //     ScoringIP::where('id', $id)->delete();
        //     DB::commit();
        //     return redirect()->back()->with('success', 'Xóa thành công!');
        // } catch (\Exception $e) {
        //     DB::rollBack();
        //     Log::notice("Delete scoring failed because " . $e);
        //     return back()->withInput()->with('error', ['Xóa thất bại!']);
        // }
    }

    public function syncData($id) { 
        $result = [
            'status' => 'success',
            'msg' => 'Đồng bộ dữ liệu thành công.',
        ];

        try {
            $scoringIP = ScoringIP::leftJoin('wards', 'wards.id', '=', 'scoringsip.ward_id')
            ->leftJoin('districts', 'districts.id', '=', 'wards.district_id')
            ->leftJoin('users as user_1', 'user_1.id', '=', 'scoringsip.user_1')
            ->select(
                'scoringsip.date_check',
                'scoringsip.user_1',
                'wards.name as ward_name',
                'wards.code as ward_code',
                'districts.code as district_code',
            )      
            ->where('scoringsip.id', $id)
            ->firstOrFail();

            $user = User::findOrFail($scoringIP->user_1);

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
            $data = Cdgdx04::where('scoring_id', $id)
                ->get()
                ->each(function ($item, $index) use ($scoringIP, $user, &$dataSync) {
                    $key = "KTGS_04GDX";
                    $reportDate = $scoringIP->date_check;
                    // $reportDateUnixTimestamp = Carbon::parse($reportDate)->timestamp;
                    // $reportDateUTC = Carbon::parse($reportDate, 'UTC')->format('Y-m-d\TH:i:s.v\Z');
                    $reportYear = date('Y', strtotime($reportDate));
                    $posCode = "00{$scoringIP->district_code}";
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
                        "kieuin" => 3,
                        "d1" => (string)$item->point_ladder,
                        "d2" => (string)$scoringIP->ward_code,
                        "d4" => (string)((int)$item->point),
                        "d5" => (string)$user->id_can_bo,
                        "d6" => (string)$user->ma_don_vi_cong_tac,
                        "d7" => (string)"TXN0{$scoringIP->ward_code}",
                        "d8" => (string)$user->ma_chuc_vu,
                    ];

                    for($i = 10; $i <= 50; $i++) {
                        $commonData["d{$i}"] = ($i == 50) ? "1" : "";
                    }

                    // $i = ++$index;
                    $i = $index * 2 + 1;
                    $code = "{$scoringIP->ward_code}_GDX04_" .  ($index === 9 ? 'a' : ($index + 1));
                    $d3Index = $index == 9 ? 'a' : ($index + 1);
                    $dataSync[] = array_merge($commonData, [
                        "name" => $item->criteria, //TEN
                        "orderValue" => $i, //ThUTU
                        "orderDescription" => (string)(($i +1 ) / 2), //TT_HIENTHI
                        "code" => $code,  //MA  Mã điểm giao dịch:
                        "d3" => "GDX04_" . $d3Index,
                        "d9" => "",
                    ]);

                    $dataSync[] = array_merge($commonData, [
                        "name" => $item->nd_criteria, //TEN
                        "orderValue" => $i + 1, //ThUTU
                        "orderDescription" => "", //TT_HIENTHI
                        "code" => "{$code}1",  //MA  Mã điểm giao dịch:
                        "d3" => "GDX04_". $d3Index ."1",
                        "d9" => (string)$item->note,
                    ]);
                });

            if (empty($dataSync)) {
                throw new \Exception("");
            }

            $key = $dataSync[0]['key'];
            $posCode = $dataSync[0]['posCode'];
            $posFlag = $dataSync[0]['posFlag'];
            $reportDate = str_replace('-', '', $dataSync[0]['reportDate']);
            $reportDate = str_replace('-', '', Carbon::parse($dataSync[0]['reportDate'])->format('Y-m-d'));
            // dd(array_column($dataSync, 'd9'));
            //dd($dataSync);die();
            //$apiUrl = "http://10.63.52.52:8005/api/v1/ktksnb-update-data?key=${key}&posCode=${posCode}&posFlag=${posFlag}&reportDate=${reportDate}";
            $apiUrl = "http://10.63.16.52:8005/api/v1/ktksnb-update-data?key=${key}&posCode=${posCode}&posFlag=${posFlag}&reportDate=${reportDate}";
            
            $response = Http::withHeaders([
                'Accept' => 'text/plain',
                'Content-Type' => 'application/json'
            ])->post($apiUrl, $dataSync);
            
            if ($response->failed()) {
            //    dd($response->status(), $response->body());
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
