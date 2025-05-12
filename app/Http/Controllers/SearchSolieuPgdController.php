<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SolieuPgd;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Auth;
use App\Models\Khtd;
use Carbon\Carbon;
use DateTime;

class SearchSolieuPgdController extends Controller
{
    //public string $title = '';

    // public function store(Request $request)
    // {        
    //     // die(var_dump($request->all()));
    //     return $this->view('check.district.newdata', $this->getDate($request));
    //     //die(var_dump($this->getDate($request)));
    // }   
    
    public function store(Request $request)
    {
        echo ("<pre>");
            echo "s123sxsxsxsxssxsxsxsxsx".$request->district_id;
        echo ("</pre>");die();
        //  //print_r(json_encode($request->all()));
        //  die();

         $get_department_id_pgd = ($request->district_id == 6 ? '004403'
                                : ($request->district_id == 7 ? '004401'
                                : ($request->district_id == 8 ? '004402'
                                : ($request->district_id == 9 ? '004404'
                                : ($request->district_id == 10 ? '004405'
                                : ($request->district_id == 11 ? '004406'
                                : ($request->district_id == 12 ? '004407'
                                : ($request->district_id == 13 ? '004408'
                                : ($request->district_id == 14 ? '004409'
                                : ($request->district_id == 15 ? '004411'
                                : ($request->district_id == 16 ? '004412' : '004410'))))))))))); 

                                
        // $query = SolieuPgd::where('MAPGD', $get_department_id_pgd);       
       
       

        // if ($request->district_id) {
        //     $query = $query->whereHas('district', fn (Builder $query) => $query->where('id', $request->district_id));
        // }

        // if ($request->ward_id) {
        //     $query = $query->whereHas('ward', fn (Builder $query) => $query->where('id', $request->ward_id));
        // }

       
        // return back()->withInput(array_merge($request->input(), [
        //     'debt' => $dataPgd?->DUNO,        
        // ]));

        

        // return back()->withInput(array_merge($request->input(), [
        //     'debt' => $dataPgd?->DUNO,            
        // ]));

        // return back()->withInput(array_merge($request->input(), [
        //     'debt'=>'HELLO',
        // ]));    
              
        // $DataPgd = $query->first();
        // echo ("<pre>");
        //   print_r($DataPgd);
        // echo ("</pre>");die();
                   
        // return back()->withInput(array_merge($request->input(), [
        //     'town_id1' => $request->get('town_id1'),
        //     'debt' => $cn44Data?->sum_duno,
        //     'balance' => $cn44Data?->sum_sodu_tk,   
        //     'number_of_groups' => $cn44Data?->soto, 
        //     'number_of_borrowers' => $cn44Data?->sokh, 
        // ]));


        // echo json_encode(array_merge($request->input(), [
        //     'town_id1' => "Hello",
        //     'debt' => number_format($cn44Data?->sum_duno, 0, ',', '.'),
        //     'balance' => number_format($cn44Data?->sum_sodu_tk, 0, ',', '.'),   
        //     'number_of_groups' => number_format($cn44Data?->soto, 0, ',', '.'), 
        //     'number_of_borrowers' => number_format($cn44Data?->sokh, 0, ',', '.'),             
        // ]));
      

        // return response()->json(array_merge($request->input(), [
        //     'town_id1' => $request->get('town_id1'),
        //     'debt' => number_format($cn44Data?->sum_duno, 0, ',', '.'),
        //     'balance' => number_format($cn44Data?->sum_sodu_tk, 0, ',', '.'),   
        //     'number_of_groups' => number_format($cn44Data?->soto, 0, ',', '.'), 
        //     'number_of_borrowers' => number_format($cn44Data?->sokh, 0, ',', '.'),             
        // ]));

       // $sltdxa = $query->get();    

        // echo ("<pre>");
        //   print_r($sltdxa);
        // echo ("</pre>");die();

        // return back()->withInput(array_merge($request->input(), [
        //     'tenxa' => $sltdxa?->tenxa,
        //     'matd'=> $sltdxa?->matd,   
        //     'soto' => number_format($sltdxa?->soto, 0, ',', '.'), 
        //     'soho' => number_format($sltdxa?->soho, 0, ',', '.'), 
        //     'duno' => number_format($sltdxa?->duno, 0, ',', '.'),
        //     'dnoqhan' => number_format($sltdxa?->dnoqhan, 0, ',', '.'),
        //     'tlqh' => number_format($sltdxa?->dnoqhan, 2, ',', '.'),
        //     'dnokhoanh' => number_format($sltdxa?->dnokhoanh, 0, ',', '.'),
        //     'tlkh' => number_format($sltdxa?->tlkh, 2, ',', '.'),
        //     'dnbqto' => number_format($sltdxa?->dnbqto, 1, ',', '.'),
        //     'dnbqho' => number_format($sltdxa?->dnbqho, 1, ',', '.'),
        //     'dnbqxa' => number_format($sltdxa?->dnbqxa, 1, ',', '.'),
        // ]));
      //  $query = SolieuPgd::where('MAPGD', $get_department_id_pgd);

        // $data = SolieuPgd::where('MAPGD', $get_department_id_pgd)->get();

        //  echo ("<pre>");
        //   print_r($data);
        // echo ("</pre>");die();

        echo ('ok');

        $data = DB::table('MAPGD')
        ->get();
        dd($data);

          return response()->json(
        [
          'data' => $data
        //	'err_msg' => $err_msg
        ]
      );
    }


    public function test (Request $request){

      $maxDateOfSlxa = request()->has('filter_date') 
      ? Carbon::createFromFormat('d/m/Y', request()->get('filter_date'))->format('Y/m/d')
      : SolieuPgd::max('ngaybc');
      
      $get_district_id_pgd = '00' + $request->district_id;
      $data = DB::table('sltdxa')
      ->where([
        ['MAPGD', $get_district_id_pgd],
        ['NGAYBC', $maxDateOfSlxa]
      ])
      ->select(
        'tenxa',
        'matd',
        'soto',
        'soho',
        'duno',
        'dnoqhan',
        'tlqh',
        'dnokhoanh',
        'tlkh',
        'dnbqto',
        'dnbqho',
        'dnbqxa'
      )
      // ->take(10)
      ->get();
      // dd($data);

      return response()->json(
        [
          'data' => $data
        // 'err_msg' => $err_msg 
        ]
      );
    }

    public function test2(Request $request){

       
     // dd(request()->get('filter_date'));
      // $date = DateTime::createFromFormat('Y-m-d', request()->get('filter_date'));
      // $formattedDate = $date->format('Y/m/d');

      $getIcn = Auth::user()->department->branchs->pluck('code')->toArray();

       
      $get_department_id_temp = Auth::user()->department_id;
      $get_department_id = (($get_department_id_temp == 1 || $get_department_id_temp == 2) ? 5 : $get_department_id_temp);

      $maxDateOfKhtd = request()->has('filter_date') 
                        ? Carbon::createFromFormat('d/m/Y', request()->get('filter_date'))->format('Y/m/d')
                        : Khtd::max('ngaybc');
                     
                       // dd($maxDateOfKhtd);die();
      // $maxDateOfKhtd = request()->has('filter_date') 
      //                 ? Carbon::createFromFormat('d/m/Y', request()->get('filter_date'))->format('Y/m/d')
      //                 : Khtd::max('ngaybc');
      // $maxDateOfKhtd = Carbon::createFromFormat('d/m/Y', request()->get('filter_date'))->format('Y/m/d');     

      // dd($maxDateOfKhtd);
                    
      // $getPathKhtd   = Khtd::query()
      // ->select()
      // ->where('ngaybc', $maxDateOfKhtd)
      // ->where('loaibc', 'CT')
      // ->where('donvi', $get_department_id)->get();

      // $data = DB::table('khtd')
      // ->where([
      //   ['ngaybc', $formattedDate],
      //   ['loaibc', 'CT'],
      //   ['donvi',$get_department_id]
      // ])
      // ->get();

      $data = DB::table('khtd')
      ->where([
        ['ngaybc', $maxDateOfKhtd],
        ['loaibc', 'CT'],
        ['donvi',$get_department_id]
      ])
      ->get();

      return response()->json(
        [
          'data' => $data
        ]
      );
    }


    public function tab3(Request $request){     
    

      $getIcn = Auth::user()->department->branchs->pluck('code')->toArray();
 
       $get_department_id_temp = Auth::user()->department_id;
       $get_department_id = (($get_department_id_temp == 1 || $get_department_id_temp == 2) ? 5 : $get_department_id_temp);
 
       $maxDateOfKhtd = request()->has('filter_date') 
                         ? Carbon::createFromFormat('d/m/Y', request()->get('filter_date'))->format('Y/m/d')
                         : Khtd::max('ngaybc');            
 
       $data = DB::table('khtd')
       ->where([
         ['ngaybc', $maxDateOfKhtd],
         ['loaibc', 'TH'],
         ['donvi',$get_department_id]
       ])
       ->get();
 
       return response()->json(
         [
           'data' => $data
         ]
       );
     }
     public function getCapxa($id_dt)  {
               
      if ($id_dt) {
         
          $wards = Ward::where('district_id', $id_dt)->get(['id', 'code', 'name', 'district_id', 'active']); 
          
          return response()->json(['data' => $wards]);
      } else {
          return response()->json(['data' => '']);
      }

  }

}
