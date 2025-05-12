<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\Dienbao;
use App\Models\Branch;
use App\Models\Khtd;
use App\Models\SolieuPgd;
use DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\Facade\Dompdf;
use Barryvdh\DomPDF\Facade\Options;

use App\Models\District;
use App\Models\Ward;

class HomeController extends Controller
{
    public string $title = 'QUẢN LÝ NỘI BỘ';

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $dataSelect = ['id', 'stt', 'ctdb'];
        $getIcn = Auth::user()->department->branchs->pluck('code')->toArray();
       
        $get_department_id_temp = Auth::user()->department_id;
        $get_department_id = (($get_department_id_temp == 1 || $get_department_id_temp == 2) ? 5 : $get_department_id_temp);
        $get_department_id_pgd = ($get_department_id_temp == 6 ? '004403'
                                 : ($get_department_id_temp == 7 ? '004401'
                                 : ($get_department_id_temp == 8 ? '004402'
                                 : ($get_department_id_temp == 9 ? '004404'
                                 : ($get_department_id_temp == 10 ? '004405'
                                //  : ($get_department_id_temp == 11 ? '004406'
                                //  : ($get_department_id_temp == 12 ? '004407'
                                 : ($get_department_id_temp == 13 ? '004408'
                                 : ($get_department_id_temp == 14 ? '004409'
                                 : ($get_department_id_temp == 15 ? '004411'
                                 : ($get_department_id_temp == 16 ? '004412' : '004410')))))))));

        $get_district_id_pgd = ($get_department_id_temp == 6 ? '4403'
                                 : ($get_department_id_temp == 7 ? '4401'
                                 : ($get_department_id_temp == 8 ? '4402'
                                 : ($get_department_id_temp == 9 ? '4404'
                                 : ($get_department_id_temp == 10 ? '4405'
                                //  : ($get_department_id_temp == 11 ? '4406'
                                //  : ($get_department_id_temp == 12 ? '4407'
                                 : ($get_department_id_temp == 13 ? '4408'
                                 : ($get_department_id_temp == 14 ? '4409'
                                 : ($get_department_id_temp == 15 ? '4411'
                                 : ($get_department_id_temp == 16 ? '4412' : '4410')))))))));                         
        
 
        //$get_department_id = Auth::user()->department_id;
       // echo $get_department_id;die();

        if (!empty($getIcn)) {
            $dataSelect = array_merge($dataSelect, $getIcn);
        }

        $branch = Branch::query()->whereIn('code', $getIcn)->get();

        $configTable = $this->generateDataTableDienBaoData($getIcn, $branch);

        $maxDateOfData = request()->has('date') 
                        ? Carbon::createFromFormat('d/m/Y', request()->get('date'))->format('Y/m/d')
                        : Dienbao::max('ngay');

        $getDataDienBao = Dienbao::query()
                                ->select()
                                ->where('ngay', $maxDateOfData)
                                ->orderBy('KH', 'ASC')
                                ->get();

        $maxDateKhtd = request()->has('datekhtd') 
                        ? Carbon::createFromFormat('d/m/Y', request()->get('datekhtd'))->format('Y/m/d')
                        : Khtd::max('ngaybc');
                 
        $maxDateBcnh = request()->has('datebcnh') 
                        ? Carbon::createFromFormat('d/m/Y', request()->get('datebcnh'))->format('Y/m/d')
                        : Khtd::max('ngaybc');                
        
        $maxDateSlxa = request()->has('dateslxa') 
                        ? Carbon::createFromFormat('d/m/Y', request()->get('dateslxa'))->format('Y/m/d')
                        : SolieuPgd::max('ngaybc');
                        
        // $getPathKhtd   = Khtd::query()
        //                         ->select()
        //                         ->where('ngaybc', $maxDateOfKhtd)
        //                         ->where('loaibc', 'CT')
        //                         ->where('donvi', $get_department_id)->get();
               
        // $getPathbcnhanh   = Khtd::query()
        //                         ->select()
        //                         ->where('ngaybc', $maxDateOfKhtd)
        //                         ->where('loaibc', 'TH')
        //                         ->where('donvi', $get_department_id)->get();   
                                
        // $getDataSolieuPgd = SolieuPgd::query()
        //                         ->select()
        //                         ->where('KU_MAPGD',$get_department_id_pgd)
        //                         ->GROUPBY ('CHTRINH')
        //                         ->orderBy('CHTRINH', 'ASC')
        //                         ->get();

        $districts = District::where('active', 1);
        if ($get_district_id_pgd != '4410')
        {
            $districts->where('code', $get_district_id_pgd);               
        }

        $districts = $districts->where('code', '!=', 'CN44')->get(['id', 'code', 'name', 'active']);      
        
        //echo $districts;die();        
        $dataTableSlxa      = $this->generateDataTableSlxa();          
       
        return $this->view('home.index', [
            'configTable'  => $configTable,
            'data'         => $getDataDienBao,
            'totalCol'     => count($dataSelect),
            'maxDate'      => $maxDateOfData,
            'maxDateKhtd'  => $maxDateKhtd,
            'maxDateBcnh'  => $maxDateBcnh,    
            'maxDateSlxa'  => $maxDateSlxa,      
            'districts'    => $districts,
            'dataTableSlxa'=> $dataTableSlxa,
        ]);
    }

    /**
     * generate data like heading, title of data table
     *
     * @return array
     */
    private function generateDataTableDienBaoData($icn, $branch): array
    {
        $heads = [
            'Chỉ tiêu diện báo',
        ];

        if (!empty($icn)) {
            $heads = array_merge($heads, $icn);
        }

        $cloneHead = $heads;

        foreach ($heads as $key => $value) {
            if ($branch->where('code', $value)->count()) {
                $heads[$key] = $branch->where('code', $value)->first()->name;
            }
        }
        
        $config = [
            'order'     => [[0, 'asc']],
            'columns'   => array_merge(
                [['type' => 'num']],
                array_fill(0, (count($heads) - 2), null),
                [['orderable' => false]]
            ),
            'language'  => [
                'url'   => asset('vendor/vi.json'),
            ],
        ];

        return ['config' => $config, 'heads' => $heads, 'headCheck' => $cloneHead];
    }

    /**
     * 
     * dowload-pdf
     */
    public function downloadPdf()
    {
        $dataSelect = ['id', 'stt', 'ctdb'];
        $getIcn = Auth::user()->department->branchs->pluck('code')->toArray();

        if (!empty($getIcn)) {
            $dataSelect = array_merge($dataSelect, $getIcn);
        }

        $branch = Branch::query()->whereIn('code', $getIcn)->get();

        $configTable = $this->generateDataTableDienBaoPdf($getIcn, $branch);
        
        $maxDateOfData = request()->has('date') 
            ? Carbon::createFromFormat('d/m/Y', request()->get('date'))->format('Y/m/d')
            : Dienbao::max('ngay');

        $getDataDienBao = Dienbao::query()
            ->select()
            ->where('ngay', $maxDateOfData)
            ->get();

        $pdf = PDF::loadView('pdf.dienbao', [
            'headtable' => $configTable['heads'],
            'headcheck' => $configTable['headClone'],
            'data'      => $getDataDienBao,
            'date'      => $maxDateOfData          
        ]);

        $domPdf = $pdf->getDomPDF();
        $canvas = $domPdf->get_canvas();
        $canvas->page_text(555, 810, "{PAGE_NUM}/{PAGE_COUNT}", 'timesnewroman', 9, array(.5,.5,.5));
        $pdf->setPaper('A3', 'landscape');

        return $pdf->stream('pdf.dienbao');
    }

    private function generateDataTableDienBaoPdf($icn, $branch): array
    {
        $heads = [
            'Chỉ tiêu điện báo',
        ];

        if (!empty($icn)) {
            $heads = array_merge($heads, $icn);
        }

        $headDefault = $heads;

        foreach ($heads as $key => $value) {
            if ($branch->where('code', $value)->count()) {
                $heads[$key] = $branch->where('code', $value)->first()->name;
            }
        }

        return ['heads' => $heads, 'headClone' => $headDefault];
    }

    
    private function generateDataTableslxa(): array
    {
        $heads = [
            'STT',
            'Tên xã/phường',
            'Tên CBTD',
            'Số tổ TK&VV',
            'Số hộ còn dư nợ',
            'Dư nợ',
            'Dư nợ nợ quá hạn',
            'Tỷ lệ quá hạn',
            'Dư nợ khoanh',
            'Tỷ lệ nợ khoanh',
            'Dư nợ bình quân/tổ',
            'Dư nợ bình quân/hộ',
            'Dư nợ bình quân/xã',
        ];

        $config = [
            'order'     => [[0, 'asc']],
            'columns'   => array_merge(
                [['type' => 'num']],
                array_fill(0, 11, null),
                [['orderable' => false]]
            ),
            'language'  => [
                'url'   => asset('vendor/vi.json'),
            ],
        ];

        return ['config' => $config, 'heads' => $heads];
    }
   
}
