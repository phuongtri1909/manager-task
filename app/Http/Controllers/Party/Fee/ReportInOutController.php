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
use Illuminate\Support\Facades\DB;

class ReportInOutController extends Controller
{
    public string $title = 'Báo cáo thu nộp đảng phí';

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
        return $this->view('party.report-inout.index', $this->getDate($request->month));
    }

    public function pdf(Request $request)
    {
        $pdf = Pdf::loadView('pdf.report-inout', $this->getDate($request->month));
        $time = Carbon::createFromFormat('m/Y', $month ?? now()->format('m/Y'));
        $year = $time->year;
        $quarter = collect(SystemDefine::QUARTER)->search(fn (array $i) => in_array($time->month, $i));
        $quarter .=$year; 
        $pdf->setPaper('A4', 'portaint');
        return $pdf->download('reportinout-Q'.$quarter.'.pdf');          
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

        $partyFeeTotalInQuarter = PartyFee::active()
            ->whereIn(DB::raw('MONTH(time)'), SystemDefine::QUARTER[$quarter])
            ->whereYear('time', $time->year)
            ->sum('total');

        $additionalPartyFeeTotalInQuarter = AdditionalPartyFee::active()
            ->whereIn(DB::raw('MONTH(time)'), SystemDefine::QUARTER[$quarter])
            ->whereYear('time', $time->year)
            ->sum('amount');

        $line1II = $partyFeeTotalInQuarter + $additionalPartyFeeTotalInQuarter;

        $partyFeeTotal = PartyFee::active()
            ->whereIn(DB::raw('MONTH(time)'), $months)
            ->whereYear('time', $time->year)
            ->sum('total');

        $additionalPartyFeeTotal = AdditionalPartyFee::active()
            ->whereIn(DB::raw('MONTH(time)'), $months)
            ->whereYear('time', $time->year)
            ->sum('amount');

        $line2II = $partyFeeTotal + $additionalPartyFeeTotal;

        $line1IV = round($line1II * 30 / 100);

        $line11III = $line13III = 0;

        $line12III = $line1III = $line122III = $line1II - $line1IV;

        $line2III = $line11III + $line12III + $line13III;

        $line2IV = $line2II - $line2III;

        $membersCount = PartyMember::active()->whereDate('created_at', '<=', $time)->count();

        return [
            'membersCount'  => $membersCount,
            'year'          => $year,
            'quarter'       => $quarter,
            'line1II'       => $line1II,
            'line2II'       => $line2II,
            'line12III'     => $line12III,
            'line2III'      => $line2III,
            'line1IV'       => $line1IV,
            'line2IV'       => $line2IV,
            'line1III'      => $line1III,
            'line122III'    => $line122III,
            'time'          => $time,
        ];
    }
}
