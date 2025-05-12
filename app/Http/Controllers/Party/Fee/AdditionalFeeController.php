<?php

namespace App\Http\Controllers\Party\Fee;

use App\Helpers\SystemDefine;
use App\Http\Controllers\Controller;
use App\Models\AdditionalPartyFee;
use App\Models\PartyMember;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdditionalFeeController extends Controller
{
    public string $title = 'Thu Đảng phí bổ sung';

    public function __construct()
    {
        $this->featureSlug = SystemDefine::PARTY_FEE_MEMBER_ADDITIONAL_FEATURE;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $time           = Carbon::createFromFormat('m/Y', $request->month ?? now()->format('m/Y'));
        $additionalFees = AdditionalPartyFee::with('member')
            ->active()
            ->whereMonth('time', $time)
            ->whereYear('time', $time)
            ->where('amount','>',0)
      //      ->where('status','1')->orWhere('status','3')         
            ->get();
//print_r($additionalFees);die();

        return $this->view('party.additional-fee.index', compact('additionalFees'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate(['month' => 'required|date_format:m/Y']);
        $time       = Carbon::createFromFormat('m/Y', $request->month ?? now()->format('m/Y'));
        //$members    = PartyMember::active()->whereDate('created_at', '<=', $time)->get();
          $members    = PartyMember::active()->where('status','1')->get();
        foreach ($members as $member) {
            $additionalPartyFee = new AdditionalPartyFee;
            $additionalPartyFee->fill([
                'party_member_id'   => $member->id,
                'time'              => $time,
                'old_salary'        => 0,
                'new_salary'        => 0,
                'deviation'         => 0,
                'count_months'      => 0,
                'amount'            => 0,
            ]);
            $additionalPartyFee->save();
        }

        flash_message(SystemDefine::CREATE_SUCCESS_MESSAGE);
        return back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $request->validate([
            'month'             => 'required|date_format:m/Y',
            'old_salary'        => 'required|array',
            'old_salary.*'      => 'numeric',
            'new_salary'        => 'required|array',
            'new_salary.*'      => 'numeric',
            'count_months'      => 'required|array',
            'count_months.*'    => 'numeric',
        ]);

        $time                   = Carbon::createFromFormat('m/Y', $request->month ?? now()->format('m/Y'));
        $additionalPartyFees    = AdditionalPartyFee::with('member')
            ->active()            
            ->whereMonth('time', $time)
            ->whereYear('time', $time)
            ->get();

        foreach ($additionalPartyFees as $index => $additionalPartyFee) {
            $deviation          = doubleval($request->new_salary[$index]) - doubleval($request->old_salary[$index]);
            $amount             = round($deviation
                * doubleval($additionalPartyFee->member->regional_minimum_wage)
                * (1 / 100)
                * doubleval($request->count_months[$index]),SystemDefine::PRECISION);

            $additionalPartyFee->fill([
                'time'              => $time,
                'old_salary'        => $request->old_salary[$index],
                'new_salary'        => $request->new_salary[$index],
                'deviation'         => $deviation,
                'count_months'      => $request->count_months[$index],
                'amount'            => $amount,
            ]);
            $additionalPartyFee->save();
        }

        flash_message(SystemDefine::UPDATE_SUCCESS_MESSAGE);
        return back();
    }

    public function pdf(Request $request)
    {
        $time = Carbon::createFromFormat('m/Y', $request->month ?? now()->format('m/Y'));
        $mm = $time->month;
        $yy = $time->year;
        $mm .=$yy;
        $additionalFees = AdditionalPartyFee::with('member')
            ->active()
            ->whereMonth('time', $time)
            ->whereYear('time', $time)
            ->where('amount','>',0)
            ->get();
        $pdf = Pdf::loadView('pdf.additional-party-fee', [
            'additionalFees' => $additionalFees,
            'time' => $time
        ]);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->download('Thu_dangphi_BS_'.$mm.'.pdf');
    }
}
