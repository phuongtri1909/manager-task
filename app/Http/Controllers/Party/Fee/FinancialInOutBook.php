<?php

namespace App\Http\Controllers\Party\Fee;

use App\Helpers\SystemDefine;
use App\Http\Controllers\Controller;
use App\Models\AdditionalPartyFee;
use App\Models\PartyFee;
use App\Models\OtherFee;
use App\Models\ReserveFee;
use App\Models\PartyMember;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class FinancialInOutBook extends Controller
{
    public string $title = 'Sổ thu chi tài chính';

    public function __construct()
    {
        $this->featureSlug = SystemDefine::PARTY_FEE_REPORT_FEATURE;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $this->view('party.report-FinancialInOutBook.index', $this->getDate($request->month));
    }

    public function pdf(Request $request)
    {
        $pdf = Pdf::loadView('pdf.report-FinancialInOutBook', $this->getDate($request->month));        
        $time = Carbon::createFromFormat('m/Y', $month ?? now()->format('m/Y'));
        $year = $time->year;
        //$quarter = collect(SystemDefine::QUARTER)->search(fn (array $i) => in_array($time->month, $i));
        $quarter = (substr($request->month,0,2) == '12' ? 4 : (substr($request->month,0,2) == '09' ? 3 : (substr($request->month,0,2) == '06' ? 2 : 1)));
        //$quarter .=$year;        
        $pdf->setPaper('A4', 'landscape');
            
        return $pdf->download('InOutBook-Q'.$quarter.'.'.$year.'.pdf');        
    }

    private function getDate(?string $month): array
    {
        $time = Carbon::createFromFormat('m/Y', $month ?? now()->format('m/Y'));
        $year = $time->year;
        $quarter = collect(SystemDefine::QUARTER)->search(fn (array $i) => in_array($time->month, $i));
        //echo $quarter;die();
        $months = match ($quarter) {
            1 => SystemDefine::QUARTER[1],
            2 => [...SystemDefine::QUARTER[1], ...SystemDefine::QUARTER[2]],
            3 => [...SystemDefine::QUARTER[1], ...SystemDefine::QUARTER[2], ...SystemDefine::QUARTER[3]],
            4 => [...SystemDefine::QUARTER[1], ...SystemDefine::QUARTER[2], ...SystemDefine::QUARTER[3], ...SystemDefine::QUARTER[4]],
        };
       
        if ($quarter == 1) {
            $reserveFee         = ReserveFee::whereYear('time', now())->where('deleted_at', '=', Null)->first();
            }
        if ($quarter == 2) {
            $reserveFee         = OtherFee::active()
                                    ->select('tonquy as amount')                                 
                                    ->WhereIn('time', OtherFee::SelectRaw('MAX(time) as MAX')
                                    ->where(DB::raw('MONTH(time)'), '<', 4)
                                    ->where(DB::raw('MONTH(time)'), '>=', 1)
                                    ->whereYear('time', $time->year))                                                                                
                                    ->first(); 
          
                if($reserveFee->amount == 0){
                    $reserveFee  = ReserveFee::whereYear('time', now())->where('deleted_at', '=', Null)->first(); 
                }                
        }
        
        if ($quarter == 3) {
             $reserveFee         = OtherFee::active()
                                    ->select('tonquy as amount')                                 
                                    ->WhereIn('time', OtherFee::SelectRaw('MAX(time) as MAX')
                                    ->where(DB::raw('MONTH(time)'), '<', 7)
                                    ->where(DB::raw('MONTH(time)'), '>=', 1)
                                    ->whereYear('time', $time->year))                                                                                
                                    ->first(); 

                        if($reserveFee->amount == 0){
                        $reserveFee  = ReserveFee::whereYear('time', now())->where('deleted_at', '=', Null)->first(); 
                }                
        }            
        if ($quarter == 4){
            $reserveFee         = OtherFee::active()
                                    ->select('tonquy as amount')                                 
                                    ->WhereIn('time', OtherFee::SelectRaw('MAX(time) as MAX')
                                    ->where(DB::raw('MONTH(time)'), '<', 10)
                                    ->where(DB::raw('MONTH(time)'), '>=', 1)
                                    ->whereYear('time', $time->year))                                                                                
                                    ->first(); 

                        if($reserveFee->amount == 0){
                        $reserveFee  = ReserveFee::whereYear('time', now())->where('deleted_at', '=', Null)->first(); 
                }                
        } 
        
        //Ton quy trong ky
        $otherFeePSIn      = OtherFee::active()
                                ->whereIn(DB::raw('MONTH(time)'), SystemDefine::QUARTER[$quarter])
                                ->whereYear('time', $time->year)
                                ->whereType(1)->sum('amount');
                            
        $otherFeePSOut     = OtherFee::active()
                                ->whereIn(DB::raw('MONTH(time)'), SystemDefine::QUARTER[$quarter])
                                ->whereYear('time', $time->year)
                                ->whereType(-1)->sum('amount');
        $tonquytrongky      = ($otherFeePSIn + $otherFeePSOut >0 ? (($reserveFee->amount ?? 0) + $otherFeePSIn - $otherFeePSOut) : 0);
        
        //Luy ke
        $otherFeeLKIn      = OtherFee::active()
                                ->whereYear('time', $time->year)
                                ->whereMonth('time', '<=', $time->month)
                                ->whereType(1)->sum('amount');
                            
        $otherFeeLKOut     = OtherFee::active()
                                ->whereYear('time', $time->year)
                                ->whereMonth('time', '<=', $time->month)
                                ->whereType(-1)->sum('amount');
        $tonquyluyke       = ($otherFeeLKIn + $otherFeeLKOut >0 ? (($reserveFee->amount ?? 0) + $otherFeeLKIn - $otherFeeLKOut) : 0);


        //echo $reserveFee->amount;die();
        $OtherFeeResults = OtherFee::selectRaw('time, description,tonquy,
        (case when direction = "tc0" then amount end) as dangphi,
        (case when direction = "tc1" then  amount end) as kinhphi,
        (case when direction = "tc2" then  amount end) as thukhac,
        (case when direction in ("tc0","tc1","tc2") then amount end) as tongthu,
        (case when direction = "tc3" then  amount end) as baochi,
        (case when direction = "tc4" then  amount end) as daihoi,
        (case when direction = "tc5" then  amount end) as khenthuong,
        (case when direction = "tc6" then  amount end) as hotro,
        (case when direction = "tc7" then  amount end) as phucap,
        (case when direction = "tc8" then  amount end) as dangphinop,
        (case when direction = "tc9" then  amount end) as chikhac,       
        (case when direction in ("tc3","tc4","tc5","tc6","tc7","tc8","tc9") then amount end) as tongchi')        
            ->active()
            ->whereIn(DB::raw('MONTH(time)'), SystemDefine::QUARTER[$quarter])
            ->whereYear('time', $time->year)
            ->orderBy('time', 'ASC')
            ->get();
       
        //print_r($OtherFeeResults);die();
       $OtherFeeResultstotalPS = OtherFee::selectRaw('time, description,
       sum(case when direction = "tc0" then amount end) as psdangphi,
       sum(case when direction = "tc1" then  amount end) as pskinhphi,
       sum(case when direction = "tc2" then  amount end) as psthukhac,
       sum(case when direction in ("tc0","tc1","tc2") then amount end) as pstongthu,
       sum(case when direction = "tc3" then  amount end) as psbaochi,
       sum(case when direction = "tc4" then  amount end) as psdaihoi,
       sum(case when direction = "tc5" then  amount end) as pskhenthuong,
       sum(case when direction = "tc6" then  amount end) as pshotro,
       sum(case when direction = "tc7" then  amount end) as psphucap,
       sum(case when direction = "tc8" then  amount end) as psdangphinop,
       sum(case when direction = "tc9" then  amount end) as pschikhac,       
       sum(case when direction in ("tc3","tc4","tc5","tc6","tc7","tc8","tc9") then amount end) as pstongchi')        
           ->active()
           ->whereIn(DB::raw('MONTH(time)'), SystemDefine::QUARTER[$quarter])
           ->whereYear('time', $time->year)
           ->get();
//echo $OtherFeeResultstotalPS;
    $OtherFeeResultstotalLK = OtherFee::selectRaw('time, description,
    sum(case when direction = "tc0" then amount end) as lkdangphi,
    sum(case when direction = "tc1" then  amount end) as lkkinhphi,
    sum(case when direction = "tc2" then  amount end) as lkthukhac,
    sum(case when direction in ("tc0","tc1","tc2") then amount end) as lktongthu,
    sum(case when direction = "tc3" then  amount end) as lkbaochi,
    sum(case when direction = "tc4" then  amount end) as lkdaihoi,
    sum(case when direction = "tc5" then  amount end) as lkkhenthuong,
    sum(case when direction = "tc6" then  amount end) as lkhotro,
    sum(case when direction = "tc7" then  amount end) as lkphucap,
    sum(case when direction = "tc8" then  amount end) as lkdangphinop,
    sum(case when direction = "tc9" then  amount end) as lkchikhac,       
    sum(case when direction in ("tc3","tc4","tc5","tc6","tc7","tc8","tc9") then amount end) as lktongchi')        
        ->active()        
        ->whereYear('time', $time->year)
        ->whereMonth('time', '<=', $time->month)
        ->get();
   

    //    echo $OtherFeeResultstotalLK;die();    
      //  $OtherFeeTotalInQuartertongthu = $OtherFeeTotalInQuarterdangphi + $OtherFeeTotalInQuarterkinhphi + ;

        // $line1II = $partyFeeTotalInQuarter + $additionalPartyFeeTotalInQuarter;

        // $partyFeeTotal = PartyFee::active()
        //     ->whereIn(DB::raw('MONTH(time)'), $months)
        //     ->whereYear('time', $time->year)
        //     ->sum('total');

        // $additionalPartyFeeTotal = AdditionalPartyFee::active()
        //     ->whereIn(DB::raw('MONTH(time)'), $months)
        //     ->whereYear('time', $time->year)
        //     ->sum('amount');

        // $line2II = $partyFeeTotal + $additionalPartyFeeTotal;

        // $line1IV = round($line1II * 30 / 100);

        // $line11III = $line13III = 0;

        // $line12III = $line1III = $line122III = $line1II - $line1IV;

        // $line2III = $line11III + $line12III + $line13III;

        // $line2IV = $line2II - $line2III;

        $membersCount = PartyMember::active()->whereDate('created_at', '<=', $time)->count();
        //$time = Carbon::createFromFormat('Y-m-d', $time->format('Y-m-d'));
        $time = date('Y-m-t');

        return [
            'membersCount'  => $membersCount,
            'year'          => $year,
            'quarter'       => $quarter,
            // 'line1II'       => $line1II,
            // 'line2II'       => $line2II,
            // 'line12III'     => $line12III,
            // 'line2III'      => $line2III,
            // 'line1IV'       => $line1IV,
            // 'line2IV'       => $line2IV,
            // 'line1III'      => $line1III,
            // 'line122III'    => $line122III,
            'tonquytrongky'     => $tonquytrongky,
            'tonquyluyke'     => $tonquyluyke,
            'time'          => $time,
            'reserveFee'    => $reserveFee,
            'OtherFeeResults' => $OtherFeeResults,
            'OtherFeeResultstotalPS' => $OtherFeeResultstotalPS,
            'OtherFeeResultstotalLK' => $OtherFeeResultstotalLK,
        ];
    }
}
