<?php

namespace App\Http\Controllers\Party\Fee;

use App\Helpers\SystemDefine;
use App\Http\Controllers\Controller;
use App\Models\AdditionalPartyFee;
use App\Models\PartyFee;
use App\Models\PartyMember;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class SyntheticMonthlyController extends Controller
{
    public string $title = 'Báo cáo thu nộp đảng phí';

    public function __construct()
    {
        $this->featureSlug = SystemDefine::PARTY_FEE_REPORT_BY_MONTH_FEATURE;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $this->view('party.synthetic-monthly.index', $this->getDate($request->month));
    }

    public function pdf(Request $request)
    {
        $pdf = Pdf::loadView('pdf.synthetic-monthly', $this->getDate($request->month));
        $time = Carbon::createFromFormat('m/Y', $month ?? now()->format('m/Y'));
        $mm = $time->month;
        $yy = $time->year;        
        $mm .=$yy;
        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->download('syntheticmonth-'.$mm.'.pdf');
    }

    private function getDate(?string $month): array
    {
        $time = Carbon::createFromFormat('m/Y', $month ?? now()->format('m/Y'));

        // $feePerQuarter = SystemDefine::QUARTER;
        // $data = [];
        // foreach($feePerQuarter as $key => $value) {
        //     foreach ($value as $keyVa => $month) {
        //         $rs = [
        //             1           => null,
        //             4           => null,
        //             5           => null,
        //             6           => null,
        //             7           => null,
        //             'handler'   => null,
        //             'date'      => null,
        //             'month'     => $month,
        //             'quarter'   => $key,
        //         ];

        //         if ($month != $time->format('m')) {
        //             $data[$key][] = $rs;
        //         }

        //         $partyFeeTotalInQuarter = PartyFee::active()
        //             ->whereMonth('time', $time)
        //             ->whereYear('time', $time)
        //             ->sum('total');
                    
        //         $additionalPartyFeeTotalInQuarter = AdditionalPartyFee::active()
        //             ->whereMonth('time', $time)
        //             ->whereYear('time', $time)
        //             ->sum('amount');

        //         $membersCountPerMonth = PartyFee::active()
        //             ->whereMonth('time', '=', $time)
        //             ->whereYear('time', '=', $time)
        //             ->where('fee_clone', '>', 0)                            
        //             ->count();
            
        //         $firstPartyFeePerMonth = PartyFee::active()
        //             ->whereMonth('time', $time)
        //             ->whereYear('time', $time)
        //             ->first();
                
        //         $handler = $firstPartyFeePerMonth->updater ?? $firstPartyFeePerMonth->creator ?? null;

        //         $date = $firstPartyFeePerMonth->time ?? null;
        //         $c1 = $membersCountPerMonth;                    
        //         $c4 = $partyFeeTotalInQuarter + $additionalPartyFeeTotalInQuarter;
        //         $c5 = $c4 * 70 / 100;
        //         $c6 = $c7 = $c4 - $c5;
                
        //         $data[$data][] = [
        //             1           => $c1,
        //             4           => $c4,
        //             5           => $c5,
        //             6           => $c6,
        //             7           => $c7,
        //             'handler'   => $handler,
        //             'date'      => $date,
        //             'month'     => $month,
        //             'quarter'   => $key,
        //         ];
        //     }
        // }

        $feePerQuarter = collect(SystemDefine::QUARTER)
            ->map(function (array $quarter, int $quarterNumber) use ($time) {
                return collect($quarter)->map(function (int $month) use ($time, $quarterNumber) { 
                    $rs = [
                        1           => null,
                        4           => null,
                        5           => null,
                        6           => null,
                        7           => null,
                        'handler'   => null,
                        'date'      => null,
                        'month'     => $month,
                        'quarter'   => $quarterNumber,
                    ];

                    if ($month > (int)$time->format('m')) {
                        return $rs;
                    }

                    $partyFeeTotalInQuarter = PartyFee::active()                        
                        ->whereMonth('time', $month)                       
                        ->whereYear('time', $time)                        
                        ->sum('total');
                   // print_r($partyFeeTotalInQuarter);die();
                    $additionalPartyFeeTotalInQuarter = AdditionalPartyFee::active()                    
                        ->whereMonth('time', $month)
                        ->whereYear('time', $time)
                        ->where('amount', '>', 0)
                        ->sum('amount');

                    // $membersCountPerMonth = PartyMember::active()
                    //     ->whereMonth('created_at', '<=', $time)
                    //     ->whereYear('created_at', '<=', $time)
                    //     ->count();
                    $membersCountPerMonth = PartyFee::active()
                        ->whereMonth('time', '=', $month)
                        ->whereYear('time', '=', $time)
                        ->where('fee_clone', '>', 0)                            
                        ->count();
                
                    $firstPartyFeePerMonth = PartyFee::active()
                        ->whereMonth('time', $month)
                        ->whereYear('time', $time)
                        ->first();

                    $handler = $firstPartyFeePerMonth->updater ?? $firstPartyFeePerMonth->creator ?? null;

                    $date = $firstPartyFeePerMonth->time ?? null;

                    $c1 = $membersCountPerMonth;                    
                   // $c4 = $partyFeeTotalInQuarter + $additionalPartyFeeTotalInQuarter;
                   $c4 = $partyFeeTotalInQuarter;//Đã bổ sung trực tiếp trong tháng
                    $c5 = $c4 * 70 / 100;
                    $c6 = $c7 = $c4 - $c5;
                  //  print_r($c4);die();
                    return [
                        1           => $c1,
                        4           => $c4,
                        5           => $c5,
                        6           => $c6,
                        7           => $c7,
                        'handler'   => $handler,
                        'date'      => $date,
                        'month'     => $month,
                        'quarter'   => $quarterNumber,
                    ];
                });
            });
//print_r($feePerQuarter);die();

        $total4 = collect(@$feePerQuarter)->reduce(fn ($t, $i) => $t + @$i->sum(4), 0);
        $total5 = collect(@$feePerQuarter)->reduce(fn ($t, $i) => $t + @$i->sum(5), 0);
        $total6 = collect(@$feePerQuarter)->reduce(fn ($t, $i) => $t + @$i->sum(6), 0);
        $total7 = collect(@$feePerQuarter)->reduce(fn ($t, $i) => $t + @$i->sum(7), 0);
        $countMembers = PartyMember::active()
            ->whereMonth('created_at', '<=', 12)
            ->whereYear('created_at', '<=', $time)
            ->count();
        $countFreePartyFee = PartyMember::active()
            ->whereMonth('created_at', '<=', 12)
            ->whereYear('created_at', '<=', $time)
            ->count();
         //   var_dump(json_encode($feePerQuarter));   die();

         return [
            'time'          => $time,
            'year'          => $time,
            'feePerQuarter' => $feePerQuarter,
            'total4'        => $total4,
            'total5'        => $total5,
            'total6'        => $total6,
            'total7'        => $total7,
            'countMembers'  => $countMembers,
            'countFreePartyFee' => $countFreePartyFee
        ];
    }
}
  
