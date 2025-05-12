<?php

namespace App\Http\Controllers\Check;

use App\Helpers\SystemDefine;
use App\Http\Controllers\Controller;
use App\Http\Requests\Check\DistrictLevelRequest;
use App\Models\District;
use App\Models\Ward;
use App\Models\Town;
use App\Models\borrow;
use App\Models\Unit;
use App\Models\CN44Data;
use App\Models\Check;

use Carbon\Carbon;
use Illuminate\Http\Request;

class DistrictLevelController extends Controller
{
    public string $title = 'Quản lý công tác kiểm tra cấp huyện';

    public function __construct()
    {
        $this->featureSlug = SystemDefine::CHECK_DISTRICT_LEVEL_FEATURE;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $dataTableData  = $this->generateDataTableData();
        $checkList      = Check::active()->with(
            'district',
            'ward',
            'ward.district',
            'town',
            'town.ward',
            'town.ward.district',
            'borrow',
            'borrow.town',
            'borrow.town.ward',
            'borrow.town.ward.district',
            'unit',
        )->whereNotNull('district_id')->get();

        return $this->view('check.district.index', compact('dataTableData', 'checkList'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $districts  = District::active()->get();
        $wards  = Ward::active()->get();
        $towns = Town::active()->get();
    
        $borrows = Borrow::all('ma_xa','ku_mato','ku_makh','ten_kh');
        $units      = Unit::active()->get();  
       
        //return $this->view('check.district.form', compact('districts','wards','towns','borrows', 'units'));
        return $this->view('check.district.form', compact('districts','wards','towns','units'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(DistrictLevelRequest $request)
    {
        $check          = new Check();
        $check->fill($request->validated());
        $check->time    = Carbon::createFromFormat('d/m/Y', $request->time);   
      //  echo $check;die();
    //Replace trực tiếp trên chuổi 
        //$check = str_replace(".", "", $check);
    //Replace theo thuộc tính cần          
        $check->debt = str_replace(".","", $check->debt);    
        $check->balance = str_replace(".","", $check->balance);
        $check->number_of_groups = str_replace(".","", $check->number_of_groups);
        $check->number_of_borrowers = str_replace(".","", $check->number_of_borrowers);
    //Replace theo thuộc tính cần
        //$json_array = json_decode($check, true);
            // foreach($json_array as $key => &$string) {
            // if($key == 'debt') {
            //     $string = str_replace(".", "", $string);
            //     }
            // if($key == 'balance') {
            //     $string = str_replace(".", "", $string);
            //     }    
            // }
        //$check = json_encode($json_array);      
     
       // $check->save();

        flash_message(SystemDefine::CREATE_SUCCESS_MESSAGE);
        return redirect()->route('districts.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Check  $check
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $check = Check::find($id);
        $districts  = District::active()->get();
        $units      = Unit::active()->get();

        return $this->view('check.district.form', compact('districts', 'units', 'check'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(DistrictLevelRequest $request, $id)
    {
        $check = Check::find($id);
        $check->fill($request->validated());
        $check->time    = Carbon::createFromFormat('d/m/Y', $request->time);
       
        $check->debt = str_replace(".","", $check->debt);    
        $check->balance = str_replace(".","", $check->balance);
        $check->number_of_groups = str_replace(".","", $check->number_of_groups);
        $check->number_of_borrowers = str_replace(".","", $check->number_of_borrowers);
        $check->save();

        flash_message(SystemDefine::CREATE_SUCCESS_MESSAGE);
        return redirect()->route('districts.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Check  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $check = Check::find($id);
        $check->delete();

        flash_message(SystemDefine::DELETE_SUCCESS_MESSAGE);
        return redirect()->route('districts.index');
    }

    private function generateDataTableData(): array
    {
        $heads = [
            'STT',
            'Ngày KT',
            'Tên Huyện',
            'Tên ĐVUT',
            'Dư nợ',
            'Số tổ TK & VV',
            'Số hộ vay',
            'Số dư TK',
            'Đơn vị kiểm tra',
            'Tóm tắt kết quả tra',
            [
                'label' => __('Hành động'),
                'no-export' => true, 'width' => 10
            ]
        ];

        $config = [
            'order'     => [[0, 'asc']],
            'columns'   => array_merge(
                [['type' => 'num']],
                array_fill(0, 9, null),
                [['orderable' => false]]
            ),
            'language'  => [
                'url'   => asset('vendor/vi.json'),
            ],"scrollX" => true,
        ];

        return ['config' => $config, 'heads' => $heads];
    }

    public function get_wards(Request $request){
        $data = $request->all();     
       // $ward_id = $request->get('ward_id');
        $select_wards = Ward::where('district_id',$data['district_id'])->orderby('code','ASC')->get();    
        $output = '';
                $output.='<option></option>';
                foreach($select_wards as $key => $ward){
                $output.='<option value="'.$ward->code.'">'.$ward->name.'</option>';
                }
               
            echo $output;
    }

    public function get_towns(Request $request){

        $data = $request->all();
        $select_towns = Town::where('ward_id',$data['ward_id'])->orderby('code','ASC')->get();
        $output = '';
                $output.='<option></option>';
                foreach($select_towns as $key => $town){
                $output.='<option value="'.$town->code.'">'.$town->name.'</option>';
                }
            echo $output;
    }

    public function get_borrows(Request $request){
        $data = $request->all();          
        $select_borrows = CN44Data::where('MAPGD',$data["district_id"])->where('MA_XA',$data["ward_id"])->where('KU_MATO',$data['town_id'])->orderby('KU_MATO','ASC')->get();
        $output = '';
                $output.='<option></option>';
                foreach($select_borrows as $key => $borrow){
                $output.='<option value="'.$borrow->KU_MAKH.'">'.$borrow->TEN_KH.'</option>';
                }
            echo $output;     
      
    }

    public function get_dvut(Request $request){
        $data = $request->all();          
        $select_dvut = CN44Data::where('MAPGD',$data["district_id"])->where('MA_XA',$data["ward_id"])->where('KU_MATO',$data['ku_mato'])->where('KU_MAKH',$data['ku_makh'])->orderby('KU_MAKH','ASC')->get();
        $output = '';
               // $output.='<option></option>';
                foreach($select_dvut as $key => $dvut){
                $output.='<option value="'.$dvut->MA_DVUT.'">'.$dvut->TEN_DVUT.'</option>';
                }
            echo $output;     
      
    }

}
