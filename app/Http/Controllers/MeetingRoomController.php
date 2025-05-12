<?php


namespace App\Http\Controllers;

use App\Helpers\SystemDefine;
use App\Models\Cdgdx01;
use App\Models\District;
use App\Models\Mroom;
use App\Models\MroomFile;
use App\Models\MroomUserPermission;
use App\Models\MroomUserView;
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
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class MeetingRoomController extends Controller
{

    public string $title = 'Phòng họp không giấy';

    public function __construct()
    {
        $this->featureSlug = SystemDefine::CHECK_MROOM_FEATURE;
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
        $fromDate = $request->input('fromDate');
        $toDate = $request->input('toDate');
        $userLogin = Auth::user()->id;
        $isAdmin = is_admin(auth()->user());        
        $featureSlug = "quan_ly_phong_hop_khong_giay";

        $checkPermission  = User::where('id', $userLogin)
            ->whereHas('permissionMroom', function ($query) use ($featureSlug) {
                $query->where('feature_slug', $featureSlug)->where('permission_slug', 'truy_cap');
            })
            ->first();
        if(!$checkPermission && !$isAdmin)
        {
            return redirect()->back();
        }

        $canCreate = User::where('id', $userLogin)
        ->whereHas('permissionMroom', function ($query) use ($featureSlug) {
            $query->where('feature_slug', $featureSlug)->where('permission_slug', 'them');
        })
        ->first();

        $canUpdate = User::where('id', $userLogin)
        ->whereHas('permissionMroom', function ($query) use ($featureSlug) {
            $query->where('feature_slug', $featureSlug)->where('permission_slug', 'sua');
        })
        ->first();

        $canDelete = User::where('id', $userLogin)
        ->whereHas('permissionMroom', function ($query) use ($featureSlug) {
            $query->where('feature_slug', $featureSlug)->where('permission_slug', 'xoa');
        })
        ->first();

        $canViewAll = User::where('id', $userLogin)
        ->whereHas('permissionMroom', function ($query) use ($featureSlug) {
            $query->where('feature_slug', $featureSlug)->where('permission_slug', 'xem_tat_ca');
        })
        ->first();

        // print_r($canDelete);die;
        
        $mrooms = Mroom::leftJoin('users as users_1', 'users_1.id', '=', 'mrooms.user_1')
                    ->leftJoin('users as user_boss', 'user_boss.id', '=', 'mrooms.user_boss')
                    ->leftJoin('mroom_user_views','mroom_user_views.mroom_id','=','mrooms.id')
                    ->select(
                        'mrooms.*',
                        'users_1.name as user_1_name', // Thư ký                
                        'user_boss.name as users_boss_name' // Chủ trì
                    )->groupBy('mroom_user_views.mroom_id');
            
        if (!empty($fromDate) && !empty($toDate)) {
            $mrooms->whereBetween('time', [$fromDate, $toDate]);
        }
        if(empty($canViewAll) && !$isAdmin)
        {
            $mrooms->where('mrooms.user_1',$userLogin)
                     ->orWhere('mroom_user_views.user_id',$userLogin)
                     ->orWhere('mrooms.user_boss',$userLogin);               
        }

        $mrooms = $mrooms->orderBy('mroom_id', 'DESC')->get();
      // print_r(json_encode($mrooms));die;
        foreach ($mrooms as $key => $value) {

            $permission_admin = MroomUserPermission::where('mroom_id', $value->id)->where('user_id', $userLogin)->first();           
           
            // User chỉ được view

            $permission_mroom = MroomUserView::where('mroom_id', $value->id)->where('user_id', $userLogin)->first();
                    
            // End
            $isUser1 = Mroom::where('id', $value->id)->where('user_1', $userLogin)->first();

            $value['permission_mroom'] = $permission_mroom ? 1 : 0 ;
            $value['read'] = $permission_admin ? $permission_admin->read : 0 ;
            $value['update'] = $permission_admin ? $permission_admin->update : 0 ;
            $value['delete'] = $permission_admin ? $permission_admin->delete : 0 ;
            $value['isUser1'] = $isUser1  ? 1 : 0 ;         

        }
       
        $memberuser_1   = Mroom::leftJoin('users as users_1', 'users_1.id', '=', 'mrooms.user_1')
                ->leftJoin('users as user_boss', 'user_boss.id', '=', 'mrooms.user_boss')  
                ->leftJoin('mroom_user_views','mroom_user_views.mroom_id','=','mrooms.id')
                ->leftJoin('users as user_views', 'user_views.id', '=', 'mroom_user_views.user_id')
                ->select('mrooms.id as mroom_id','mrooms.user_1 as userview','users_1.name as users_boss_name',
                \DB::raw('(CASE 
                    WHEN users_1.id = "3" THEN "Giám đốc CN"
                    WHEN users_1.id = "4" THEN "Phó giám đốc CN"
                    WHEN users_1.id = "5" THEN "Phó giám đốc CN"
                    WHEN users_1.id = "99" THEN "CB HCTC, Thư ký"
                    WHEN users_1.id = "98" THEN "TP. KTKSNB"
                    WHEN users_1.id = "106" THEN "PP. Hành chính-Tổ chức"
                    WHEN users_1.id = "135" THEN "TP. Tin học"
                    WHEN users_1.id = "134" THEN "PP. Tin học"
                    WHEN users_1.id = "107" THEN "TP. Kế toán-Ngân quỹ"
                    WHEN users_1.id = "127" THEN "PP. Kế toán-Ngân quỹ"
                    WHEN users_1.id = "130" THEN "PP. KHNV tín dụng"
                    WHEN users_1.id = "103" THEN "PP. KHNV tín dụng"
                    WHEN users_1.id = "102" THEN "Giám đốc PGD Đức Trọng"
                    WHEN users_1.id = "115" THEN "PP. KTKSNB"
                    ELSE "Thành viên"
                    END) AS position_usersview'));
                
        $memberuserviews  = Mroom::leftJoin('users as users_1', 'users_1.id', '=', 'mrooms.user_1')
                ->leftJoin('users as user_boss', 'user_boss.id', '=', 'mrooms.user_boss')  
                ->leftJoin('mroom_user_views','mroom_user_views.mroom_id','=','mrooms.id')
                ->leftJoin('users as user_views', 'user_views.id', '=', 'mroom_user_views.user_id')
                ->select('mrooms.id as mroom_id','mroom_user_views.user_id as userview','user_views.name as users_boss_name',
                \DB::raw('(CASE 
                    WHEN user_views.id = "3" THEN "Giám đốc CN"
                    WHEN user_views.id = "4" THEN "Phó giám đốc CN"
                    WHEN user_views.id = "5" THEN "Phó giám đốc CN"
                    WHEN user_views.id = "99" THEN "CB HCTC, Thư ký"
                    WHEN user_views.id = "98" THEN "TP. KTKSNB"
                    WHEN user_views.id = "106" THEN "PP. Hành chính-Tổ chức"
                    WHEN user_views.id = "135" THEN "TP. Tin học"
                    WHEN user_views.id = "134" THEN "PP. Tin học"
                    WHEN user_views.id = "107" THEN "TP. Kế toán-Ngân quỹ"
                    WHEN user_views.id = "127" THEN "PP. Kế toán-Ngân quỹ"
                    WHEN user_views.id = "130" THEN "PP. KHNV tín dụng"
                    WHEN user_views.id = "103" THEN "PP. KHNV tín dụng"
                    WHEN user_views.id = "102" THEN "Giám đốc PGD Đức Trọng"
                    WHEN user_views.id = "115" THEN "PP. KTKSNB"
                    ELSE "Thành viên"
                    END) AS position_usersview'));                 
                
        $memberuserboss   = Mroom::leftJoin('users as users_1', 'users_1.id', '=', 'mrooms.user_1')
                ->leftJoin('users as user_boss', 'user_boss.id', '=', 'mrooms.user_boss')  
                ->leftJoin('mroom_user_views','mroom_user_views.mroom_id','=','mrooms.id')
                ->leftJoin('users as user_views', 'user_views.id', '=', 'mroom_user_views.user_id')
                ->select('mrooms.id as mroom_id','mrooms.user_boss as userview','user_boss.name as users_boss_name',
                \DB::raw('(CASE 
                    WHEN user_boss.id = "3" THEN "Giám đốc CN"
                    WHEN user_boss.id = "4" THEN "Phó giám đốc CN"
                    WHEN user_boss.id = "5" THEN "Phó giám đốc CN"
                    WHEN user_boss.id = "99" THEN "CB HCTC, Thư ký"
                    WHEN user_boss.id = "98" THEN "TP. KTKSNB"
                    WHEN user_boss.id = "106" THEN "PP. Hành chính-Tổ chức"
                    WHEN user_boss.id = "135" THEN "TP. Tin học"
                    WHEN user_boss.id = "134" THEN "PP. Tin học"
                    WHEN user_boss.id = "107" THEN "TP. Kế toán-Ngân quỹ"
                    WHEN user_boss.id = "127" THEN "PP. Kế toán-Ngân quỹ"
                    WHEN user_boss.id = "130" THEN "PP. KHNV tín dụng"
                    WHEN user_boss.id = "103" THEN "PP. KHNV tín dụng"
                    WHEN user_boss.id = "102" THEN "Giám đốc PGD Đức Trọng"
                    WHEN user_boss.id = "115" THEN "PP. KTKSNB"
                    ELSE "Thành viên"
                    END) AS position_usersview'))
                ->union($memberuser_1)
                ->union($memberuserviews)                
                ->get();        
                //->toArray();
       
// echo "<pre>";
//     echo $memberuserboss;
// echo "</pre>";die();
  
        return $this->view('mroom.index', compact('mrooms', 'userLogin', 'memberuserboss','isAdmin','canCreate','canUpdate','canViewAll','canDelete'));

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


    public function submitSecretary(Request $request){

        $data = $request->all();          
        $usercheck = User::where(\DB::raw('substr(code, 3,  4)'), '=', '4410')->get();   
       
        $output = '';
                $output.='<option></option>';
                foreach($usercheck as $key => $item){
                $output.='<option value="'.$item->id.'">'.$item->name.'</option>';
                }
            echo $output;
            

    }

    public function create()
    {
        $users = User::where('active', 1)->get();
        $userSecret = substr(Auth::user()->code, 2, 4);
        $userleader = substr(Auth::user()->code, 2, 4);
        $users_hs = User::where(\DB::raw('substr(code, 3,  4)'), '=', $userSecret)->get(); 
        $userleader = User::where('department_id', '=', 2)
                        ->where(\DB::raw('substr(code, 3,  4)'), '=', $userSecret)
                        ->get();  
        $userBoss = User::where('active', 1)->where('code_for_job_assignment', 'ld')->get();
       
        return $this->view('mroom.create', compact('users', 'users_hs', 'userleader', 'userBoss'));
    }


    public function store(Request $request)
    {
    
        $validator = Validator::make($request->all(), [
            'time' => 'required',
            'name' => 'required',
            'user_check' => 'required',
            'user_boss' => 'required',
            'user_view' => 'required',
        ],
            [
                '*.required' => 'Vui lòng nhập đầy đủ thông tin trường bắt buộc.'
            ]
        );
       
        if ($validator->fails()) {
            return back()->withInput()->with('error', [$validator->errors()->first()]);
        }       
        
        //print_r($request->input('time'));die();
        
        try {
        //   $userCheck = $request->user_check ? $request->user_check : [];

            DB::beginTransaction();
            $data = [
                'name' => $request->name,
                'time' => Carbon::createFromFormat('d/m/Y H:i', $request->time),
                'place' => $request->place,
                'user_1' => (int)$request->user_check,
                'user_boss' => (int)$request->user_boss,                
                'created_by' => time(),
                'updated_by' => time()
            ];
            $mroom = Mroom::create($data);
            $userView = $request->user_view ? $request->user_view : [];
       
            foreach ($userView as $key => $value) {
                MroomUserView::create([
                    'mroom_id' => (int)$mroom['id'],
                    'user_id' => (int)$value
                ]);
            }
          
            // foreach ($userCheck as $key => $value) {
            //     MroomUserPermission::create([
            //         'mroom_id' => (int)$mroom['id'],
            //         'user_id' => (int)$value,
            //         'read' => 1,
            //         'create' => 1,
            //         'update' => 1,
            //         'delete' => 1,
            //     ]);
            // }
          //  echo "ddd";die();

                MroomUserPermission::create([
                    'mroom_id' => (int)$mroom['id'],
                    'user_id' => (int)$request->user_check,
                    'read' => 1,
                    'create' => 1,
                    'update' => 1,
                    'delete' => 1,
                ]);
                MroomUserPermission::create([
                    'mroom_id' => (int)$mroom['id'],
                    'user_id' => (int)$request->user_boss,
                    'read' => 1,
                    'create' => 1,
                    'update' => 1,
                    'delete' => 1,
                ]);
            //$this->createCdgx($scoring['id']);
            DB::commit();
            return redirect()->route('mroom.index')->with('success', 'Thêm thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', [$e->getMessage()]);
        }
    }

    public function edit($id)
    {
        $users = User::where('active', 1)->get();        
        $isAdmin = is_admin(auth()->user());
        $userSecret = substr(Auth::user()->code, 2, 4);
        $userleader = substr(Auth::user()->code, 2, 4);
        $users_hs = User::where(\DB::raw('substr(code, 3,  4)'), '=', $userSecret)->get(); 
        $userleader = User::where('department_id', '=', 2)
                        ->where(\DB::raw('substr(code, 3,  4)'), '=', $userSecret)
                        ->get();
        $mroomUserView = MroomUserView::where('mroom_id', $id)->with('user')->get();
        $mroomUserPermission = MroomUserPermission::where('mroom_id', $id)->pluck('user_id')->toArray();
        $userLogin = Auth::user()->id;
        //$userLogin = 3;
        $permission = $this->findAllOccurrences($mroomUserPermission, $userLogin);        
       
        $mroom = Mroom::where('id', $id)->first();
     
        $user_1 = json_decode($mroom->user_1);
        $user_boss = json_decode($mroom->user_boss);

        return $this->view('mroom.edit', compact(
            'users', 'userleader', 'users_hs', 'mroom', 'user_1' , 'user_boss', 'mroomUserView', 'mroomUserPermission', 'userLogin', 'permission','isAdmin'
        ));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'time' => 'required',
            'name' => 'required',
            'user_boss' => 'required',
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
           
            $data = [
                'name' => $request->name,
                'time' => Carbon::createFromFormat('d/m/Y H:i', $request->time),
                'place' => $request->place,
                'user_1' => (int)$request->user_check,
                'user_boss' => (int)$request->user_boss,                
                'created_by' => time(),
                'updated_by' => time()
            ];
            Mroom::where('id', $id)->update($data);

            $userView = $request->user_view ? $request->user_view : [];
            // Cập nhật user view
            MroomUserView::where('mroom_id', $id)->delete();
            foreach ($userView as $key => $value) {
                MroomUserView::create([
                    'mroom_id' => $id,
                    'user_id' => (int)$value
                ]);
            }
            DB::commit();
            return redirect()->route('mroom.index')->with('success', 'Sửa thành công!');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::notice("Update room failed because " . $e);
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
        try {
            DB::beginTransaction();
            foreach ($data['parent'] as $key => $value) {
                // $data = [
                //     'note' => $value['note'],
                // ];
                // Cdgdx01::where('id', $key)->update($data);
                foreach ($value['children'] as $key_children => $children) {
                    // Cdgdx01::where('id', $children['id'])->update(['note' => $children['note']]);
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
        $users = User::where('active', 1)->get();
        $mrooms = MroomFile::where('mroom_id', $id)->orderBy('id', 'DESC')->get();
        $base_url = url('/');

        return $this->view('mroom.show', compact('users', 'id', 'mrooms', 'base_url'));
    }

    public function destroy($id)
    {

        try {
            DB::beginTransaction();
            //Cdgdx01::where('scoring_id', $id)->delete();
            MroomUserView::where('mroom_id', $id)->delete();
            MroomUserPermission::where('mroom_id', $id)->delete();
            Mroom::where('id', $id)->delete();
            DB::commit();
            return redirect()->back()->with('success', 'Xóa thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::notice("Delete scoring failed because " . $e);
            return back()->withInput()->with('error', ['Xóa thất bại!']);
        }
    }
    
    public function delete_fileupload($id){

        try {
            DB::beginTransaction();
            //$data = MroomFile::Find($id);
   
                MroomFile::where('id', $id)->delete();
                DB::commit();
                // flash_message(SystemDefine::DELETE_SUCCESS_MESSAGE);
                // return redirect()->back();
                return redirect()->back()->with('success', 'Xóa thành công!');
            } catch (\Exception $e) {
                DB::rollBack();
                Log::notice("Delete file failed because " . $e);
                return back()->withInput()->with('error', ['Xóa thất bại!']);
            }
    }    

    public function uploadFile($id)
    {
        $users = User::where('active', 1)->get();
        $districts = District::where('active', 1)->get(['id', 'code', 'name', 'active']);
        $mrooms = MroomFile::where('mroom_id', $id)->orderBy('id', 'DESC')->get();
        $base_url = url('/');

        return $this->view('mroom.upload_file', compact('users', 'id', 'mrooms', 'base_url'));
    }

    public function upload123(Request $request)
    {
        // if(!$checkPermission && !$isAdmin)
        // {
        //     return redirect()->back();
        // }

        try {
            $time = date("Y-m-d") ;
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();

            $filePath = $file->storeAs('documents/mroom/'.$time, $fileName, 'public');
            $mime = $file->getClientMimeType();
            $data = [
                'mroom_id' => $request->mroom_id,
                'user_id' => $request->user_id,
                'name' => $fileName,
                'url' => '/storage/' . $filePath,
                'type' => $mime,
                // 'description' => $request->description,
            ];

            MroomFile::create($data);
            return redirect()->back()->with('success', 'Upload file thành công!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', ['Upload file thất bại!']);
        }
    }

    public function upload(Request $request)
    {
       
        try {
            $files = [];
            foreach($request->file('file') as $file)
			{
                $time = date("Y-m-d") ;
                $fileName = time().rand(1,100). '_' . $file->getClientOriginalName();
                $files = $fileName;
                $filePath = $file->storeAs('documents/mroom/'.$time, $fileName, 'public');
                $mime = $file->getClientMimeType();
                $data = [
                    'mroom_id' => $request->mroom_id,
                    'user_id' => $request->user_id,
                    'name' => $files,
                    'url' => '/storage/' . $filePath,
                    'type' => $mime,                
                ];

                MroomFile::create($data);
            }
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
                'wards.name as ward_name', // Tên của xã/phường
                'users_1.name as user_1_name', // Tên của user_1
                'users_2.name as user_2_name', // Tên của user_1
                'user_boss.name as users_boss_name', // Tên của user_1
                'wards.district_id as district_id',
                'districts.name as district_name'
            )->where('scorings.id', $id)->first();
    // print_r($scoring);die();       
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
        
        $pdf = \PDF::loadView('pages.scoring.template', compact(['scoring', 'data','sumPoint']));
      //  $pdf->output();
        $domPdf = $pdf->getDomPDF();
        $canvas = $domPdf->get_canvas();
        $canvas->page_text(555, 810, "{PAGE_NUM}/{PAGE_COUNT}", 'DejaVu Serif', 9, array(.5,.5,.5));
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
                    ];

                    Cdgdx01::create($subChildren);
                }
            }
        }
    return 'success';

    }
}
