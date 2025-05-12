<?php

namespace App\Http\Controllers\Party\Fee;

use App\Helpers\SystemDefine;
use App\Http\Controllers\Controller;
use App\Models\AdditionalPartyFee;
use App\Models\PartyFee;
use App\Models\OtherFee;
use App\Models\ReserveFee;
use App\Models\PartyMember;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class ReportFinancial extends Controller
{
    public string $title = 'Báo cáo thu chi tài chính';

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
        return $this->view('party.report-Financial.index', $this->getDate($request->month));
    }

    public function pdf(Request $request)
    {
        $pdf = Pdf::loadView('pdf.report-Financial', $this->getDate($request->month));
        $time = Carbon::createFromFormat('m/Y', $month ?? now()->format('m/Y'));
        $year = $time->year;
        $quarter = collect(SystemDefine::QUARTER)->search(fn (array $i) => in_array($time->month, $i));
        $quarter .=$year; 
        $pdf->setPaper('A4', 'portrait');
            
        return $pdf->download('report-Financial-Q'.$quarter.'.pdf');        
    }

    private function getDate(?string $month): array
    {
        $time = Carbon::createFromFormat('m/Y', $month ?? now()->format('m/Y'));
        $year = $time->year;
        $quarter = collect(SystemDefine::QUARTER)->search(fn (array $i) => in_array($time->month, $i));
        $months = match ($quarter) {
            1 => SystemDefine::QUARTER[1],
            2 => [...SystemDefine::QUARTER[1], ...SystemDefine::QUARTER[2]],
            3 => [...SystemDefine::QUARTER[1], ...SystemDefine::QUARTER[2], ...SystemDefine::QUARTER[3]],
            4 => [...SystemDefine::QUARTER[1], ...SystemDefine::QUARTER[2], ...SystemDefine::QUARTER[3], ...SystemDefine::QUARTER[4]],
        };
        
        if ($quarter == 1){
            $reserveFee         = ReserveFee::whereYear('time', now())->first();
            }
        if ($quarter == 2) {
            $reserveFee         = OtherFee::active()->select('tonquy as amount', OtherFee::raw('MIN(time) as mintime'))
                 ->whereIn(DB::raw('MONTH(time)'), SystemDefine::QUARTER[$quarter])
                 ->whereYear('time', $time->year)
                 ->first();               
                                
                if(($reserveFee->amount == null || $reserveFee->mintime == null)){
                    $reserveFee         = OtherFee::active()->select('tonquy as amount', OtherFee::raw('MAX(time) as maxtime'))
                        ->whereIn(DB::raw('MONTH(time)'), SystemDefine::QUARTER[$quarter-1])
                        ->whereYear('time', $time->year)
                        ->first(); 
                }
                if(($reserveFee->amount == null || $reserveFee->maxtime == null)){
                    $reserveFee  = ReserveFee::whereYear('time', now())->first(); 
                }                
        }
        
        if ($quarter == 3){
            $reserveFee         = OtherFee::active()->select('tonquy as amount', OtherFee::raw('MIN(time) as mintime'))
                ->whereIn(DB::raw('MONTH(time)'), SystemDefine::QUARTER[$quarter])
                ->whereYear('time', $time->year)
                ->first();
                if(($reserveFee->amount == null || $reserveFee->mintime == null)){
                        $reserveFee         = OtherFee::active()->select('tonquy as amount', OtherFee::raw('MAX(time) as maxtime'))
                        ->whereIn(DB::raw('MONTH(time)'), SystemDefine::QUARTER[$quarter-1])
                        ->whereYear('time', $time->year)
                        ->first();  
                    }
                    if(($reserveFee->amount == null || $reserveFee->maxtime == null)){
                        $reserveFee         = OtherFee::active()->select('tonquy as amount', OtherFee::raw('MAX(time) as maxtime'))
                        ->whereIn(DB::raw('MONTH(time)'), SystemDefine::QUARTER[$quarter-2])
                        ->whereYear('time', $time->year)
                        ->first();  
                    }
                    if(($reserveFee->amount == null || $reserveFee->maxtime == null)){
                        $reserveFee  = ReserveFee::whereYear('time', now())->first();
                    }
        }            
        if ($quarter == 4){
                $reserveFee         = OtherFee::active()->select('tonquy as amount', OtherFee::raw('MIN(time) as mintime'))
                ->whereIn(DB::raw('MONTH(time)'), SystemDefine::QUARTER[$quarter])
                ->whereYear('time', $time->year)
                ->first();
                if(($reserveFee->amount == null || $reserveFee->mintime == null)){
                        $reserveFee         = OtherFee::active()->select('tonquy as amount', OtherFee::raw('MAX(time) as maxtime'))
                        ->whereIn(DB::raw('MONTH(time)'), SystemDefine::QUARTER[$quarter-1])
                        ->whereYear('time', $time->year)
                        ->first(); 
                     } 
                if(($reserveFee->amount == null || $reserveFee->maxtime == null)){
                        $reserveFee         = OtherFee::active()->select('tonquy as amount', OtherFee::raw('MAX(time) as maxtime'))
                        ->whereIn(DB::raw('MONTH(time)'), SystemDefine::QUARTER[$quarter-2])
                        ->whereYear('time', $time->year)
                        ->first();  
                    }
                if(($reserveFee->amount == null || $reserveFee->maxtime == null)){
                        $reserveFee         = OtherFee::active()->select('tonquy as amount', OtherFee::raw('MAX(time) as maxtime'))
                        ->whereIn(DB::raw('MONTH(time)'), SystemDefine::QUARTER[$quarter-3])
                        ->whereYear('time', $time->year)
                        ->first();   
                    }       
                if(($reserveFee->amount == null || $reserveFee->mintime == null)){
                        $reserveFee  = ReserveFee::whereYear('time', now())->first();
                    }
        }
        
        //Ton quy trong quy
        $otherFeePSIn      = OtherFee::active()
                                ->whereIn(DB::raw('MONTH(time)'), SystemDefine::QUARTER[$quarter])
                                ->whereYear('time', $time->year)
                                ->whereType(1)->sum('amount');
                            
        $otherFeePSOut     = OtherFee::active()
                                ->whereIn(DB::raw('MONTH(time)'), SystemDefine::QUARTER[$quarter])
                                ->whereYear('time', $time->year)
                                ->whereType(-1)->sum('amount');
        //$tonquytrongky      = ($otherFeePSIn + $otherFeePSOut >0 ? (($reserveFee->amount ?? 0) + $otherFeePSIn - $otherFeePSOut) : 0);
        $tonquytrongky      = $reserveFee->amount + $otherFeePSIn - $otherFeePSOut;
        
        //Luy ke
        $otherFeeLKIn      = OtherFee::active()
                                ->whereYear('time', $time->year)
                                ->whereType(1)->sum('amount');
                            
        $otherFeeLKOut     = OtherFee::active()
                                ->whereYear('time', $time->year)
                                ->whereType(-1)->sum('amount');
        //$tonquyluyke       = ($otherFeeLKIn + $otherFeeLKOut >0 ? (($reserveFee->amount ?? 0) + $otherFeeLKIn - $otherFeeLKOut) : 0);
        $tonquyluyke       = $reserveFee->amount + $otherFeeLKIn - $otherFeeLKOut;

        $memberListDV     = PartyMember::active()->where('status', 1)->count();
        $memberListCUV  = PartyMember::active()->whereIn('party_position', ['1','2','3'])->where('status', 1)->count();       
        $memberListCBCNV    = User::active()->where(DB::raw('substr(code, 1, 6)'), '=', 'TD4410')->count();
        
        //Thu trong ky
        $OtherFeedangphiquy = OtherFee::active()  
                            ->whereIn(DB::raw('MONTH(time)'), SystemDefine::QUARTER[$quarter])
                            ->whereYear('time', $time->year)
                            ->where(DB::raw('direction'), '=', 'tc0')  
                            ->sum('amount');

        $OtherFeekinhphiquy = OtherFee::active()  
                            ->whereIn(DB::raw('MONTH(time)'), SystemDefine::QUARTER[$quarter])
                            ->whereYear('time', $time->year)
                            ->where(DB::raw('direction'), 'tc1')
                            ->sum('amount'); 
        $OtherFeethukhacquy = OtherFee::active()  
                            ->whereIn(DB::raw('MONTH(time)'), SystemDefine::QUARTER[$quarter])
                            ->whereYear('time', $time->year)
                            ->where(DB::raw('direction'), 'tc2')
                            ->sum('amount');
        //Tổng thu trong kỳ                   
        $OtherFeeTotalInQuartertongthu =  $tonquytrongky + $OtherFeedangphiquy + $OtherFeekinhphiquy + $OtherFeethukhacquy;                    
        //Chi trong ky
        $OtherFeebaochiquy = OtherFee::active()  
                            ->whereIn(DB::raw('MONTH(time)'), SystemDefine::QUARTER[$quarter])
                            ->whereYear('time', $time->year)
                            ->where(DB::raw('direction'), 'tc3')
                            ->sum('amount');
        $OtherFeedaihoiquy = OtherFee::active()  
                            ->whereIn(DB::raw('MONTH(time)'), SystemDefine::QUARTER[$quarter])
                            ->whereYear('time', $time->year)
                            ->where(DB::raw('direction'), 'tc4')
                            ->sum('amount'); 
        $OtherFeekhenthuongquy = OtherFee::active()  
                            ->whereIn(DB::raw('MONTH(time)'), SystemDefine::QUARTER[$quarter])
                            ->whereYear('time', $time->year)
                            ->where(DB::raw('direction'), 'tc5')
                            ->sum('amount');                    
        $OtherFeehotroquy = OtherFee::active()  
                            ->whereIn(DB::raw('MONTH(time)'), SystemDefine::QUARTER[$quarter])
                            ->whereYear('time', $time->year)
                            ->where(DB::raw('direction'), 'tc6')
                            ->sum('amount');                            
        $OtherFeephucapquy = OtherFee::active()  
                            ->whereIn(DB::raw('MONTH(time)'), SystemDefine::QUARTER[$quarter])
                            ->whereYear('time', $time->year)
                            ->where(DB::raw('direction'), 'tc7')
                            ->sum('amount'); 
        $OtherFeechikhacquy = OtherFee::active()  
                            ->whereIn(DB::raw('MONTH(time)'), SystemDefine::QUARTER[$quarter])
                            ->whereYear('time', $time->year)
                            ->where(DB::raw('direction'), 'tc9')
                            ->sum('amount');  
        
        //Tổng chi trong kỳ
        $OtherFeeTotalInQuartertongchi =  $OtherFeebaochiquy + $OtherFeedaihoiquy + $OtherFeekhenthuongquy + $OtherFeehotroquy + $OtherFeephucapquy + $OtherFeechikhacquy;                          
        //Đảng phí nộp cấp trên                                    
        $OtherFeedpnopquy   = OtherFee::active()  
                            ->whereIn(DB::raw('MONTH(time)'), SystemDefine::QUARTER[$quarter])
                            ->whereYear('time', $time->year)
                            ->where(DB::raw('direction'), 'tc8')
                            ->sum('amount');                    
        
        //Luy ke thu
        $OtherFeedangphilk = OtherFee::active()  
                            ->whereYear('time', $time->year)
                            ->where(DB::raw('direction'), 'tc0')
                            ->sum('amount');
        $OtherFeekinhphilk = OtherFee::active()  
                            ->whereYear('time', $time->year)
                            ->where(DB::raw('direction'), 'tc1')
                            ->sum('amount');
        $OtherFeethukhaclk = OtherFee::active()                                   
                            ->whereYear('time', $time->year)
                            ->where(DB::raw('direction'), 'tc2')
                            ->sum('amount');
        $OtherFeeTotalInQuartertongthulk =  $tonquyluyke + $OtherFeedangphilk + $OtherFeekinhphilk + $OtherFeethukhaclk;                    
        //Luy ke Chi
        $OtherFeebaochilk = OtherFee::active()  
                            ->whereYear('time', $time->year)
                            ->where(DB::raw('direction'), 'tc3')
                            ->sum('amount');
        $OtherFeedaihoilk = OtherFee::active()  
                            ->whereYear('time', $time->year)
                            ->where(DB::raw('direction'), 'tc4')   
                            ->sum('amount'); 
        $OtherFeekhenthuonglk = OtherFee::active()  
                            ->whereYear('time', $time->year)
                            ->where(DB::raw('direction'), 'tc5')
                            ->sum('amount');                    
        $OtherFeehotrolk = OtherFee::active()  
                            ->whereYear('time', $time->year)
                            ->where(DB::raw('direction'), 'tc6')    
                            ->sum('amount');                            
        $OtherFeephucaplk = OtherFee::active()  
                            ->whereYear('time', $time->year)
                            ->where(DB::raw('direction'), 'tc7')   
                            ->sum('amount');                            
        $OtherFeechikhaclk = OtherFee::active()  
                            ->whereYear('time', $time->year)
                            ->where(DB::raw('direction'), 'tc9')  
                            ->sum('amount');
        $OtherFeeTotaltongchilk =  $OtherFeebaochilk + $OtherFeedaihoilk + $OtherFeekhenthuonglk + $OtherFeehotrolk + $OtherFeephucaplk + $OtherFeechikhaclk;
        //Lũy kế đảng phí nộp cấp trên
        $OtherFeedpnoplk = OtherFee::active()  
                            ->whereYear('time', $time->year)
                            ->where(DB::raw('direction'), 'tc8') 
                            ->sum('amount');
        //Kinh phí còn lại chuyển kỳ sau      
        $OtherFeeTotalkynay= $OtherFeeTotalInQuartertongthu - $OtherFeeTotalInQuartertongchi - $OtherFeedpnopquy;            
        $OtherFeeTotalluyke= $OtherFeeTotalInQuartertongthulk - $OtherFeeTotaltongchilk - $OtherFeedpnoplk;
        return [
            'year'          => $year,
            'quarter'       => $quarter,
            'memberListDV'  => $memberListDV,
            'memberListCUV' => $memberListCUV,
            'memberListCBCNV' => $memberListCBCNV, 
            'OtherFeedangphiquy' => $OtherFeedangphiquy,
            'OtherFeekinhphiquy' => $OtherFeekinhphiquy,  
            'OtherFeethukhacquy' => $OtherFeethukhacquy,
            'OtherFeeTotalInQuartertongthu' => $OtherFeeTotalInQuartertongthu,
            'OtherFeedangphilk' => $OtherFeedangphilk,
            'OtherFeekinhphilk' => $OtherFeekinhphilk, 
            'OtherFeethukhaclk' => $OtherFeethukhaclk,
            'OtherFeeTotalInQuartertongthulk' => $OtherFeeTotalInQuartertongthulk,
            'OtherFeebaochiquy' => $OtherFeebaochiquy,
            'OtherFeedaihoiquy' => $OtherFeedaihoiquy,
            'OtherFeekhenthuongquy' => $OtherFeekhenthuongquy,
            'OtherFeehotroquy' => $OtherFeehotroquy,
            'OtherFeephucapquy' => $OtherFeephucapquy,
            'OtherFeechikhacquy' => $OtherFeechikhacquy,
            'OtherFeeTotalInQuartertongchi' =>  $OtherFeeTotalInQuartertongchi,
            'OtherFeedpnopquy' => $OtherFeedpnopquy,
            'OtherFeebaochilk' => $OtherFeebaochilk,
            'OtherFeedaihoilk' => $OtherFeedaihoilk, 
            'OtherFeekhenthuonglk' => $OtherFeekhenthuonglk,                    
            'OtherFeehotrolk' => $OtherFeehotrolk,                            
            'OtherFeephucaplk' => $OtherFeephucaplk, 
            'OtherFeedpnoplk' => $OtherFeedpnoplk,                    
            'OtherFeechikhaclk' => $OtherFeechikhaclk,
            'OtherFeeTotaltongchilk' => $OtherFeeTotaltongchilk,
            'tonquytrongky'     => $tonquytrongky,
            'tonquyluyke'     => $tonquyluyke,
            'time'          => $time,
            'reserveFee'    => $reserveFee,  
            'OtherFeeTotalkynay' => $OtherFeeTotalkynay,
            'OtherFeeTotalluyke' => $OtherFeeTotalluyke,          
        ];
    }
}
