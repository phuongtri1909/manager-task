<?php

namespace App\Http\Controllers\Party\Fee;

use App\Helpers\SystemDefine;
use App\Http\Controllers\Controller;
use App\Models\AdditionalPartyFee;
use App\Models\OtherFee;
use App\Models\PartyFee;
use App\Models\ReserveFee;
use Illuminate\Http\Request;

class ReserveFeeController extends Controller
{
    public string $title = 'Tồn quỹ chi bộ';

    public function __construct()
    {
        $this->featureSlug = SystemDefine::PARTY_FEE_FEATURE;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $partyFeeTotal      = PartyFee::active()->whereYear('time', now())->sum('total');
        $additionalTotal    = AdditionalPartyFee::active()->whereYear('time', now())->sum('amount');
        $otherFeeTotal      = OtherFee::active()->whereYear('time', now())->whereType(1)->sum('amount');

        $reserveFee         = ReserveFee::whereYear('time', now())->first();
        //$revenue            = $partyFeeTotal + $additionalTotal + $otherFeeTotal;
        $revenue            = $partyFeeTotal + $otherFeeTotal;
        $expenditure        = OtherFee::active()->whereYear('time', now())->whereType(-1)->sum('amount');
        $total              = ($reserveFee->amount ?? 0) + $revenue - $expenditure;

        return $this->view('party.reserve-fee.index', compact(
            'reserveFee',
            'revenue',
            'expenditure',
            'total'
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate(['amount' => 'required|numeric']);

        $study          = new ReserveFee();
        $study->fill($request->all());
        $study->time    = now();
        $study->save();

        flash_message(SystemDefine::CREATE_SUCCESS_MESSAGE);
        return redirect()->route('party.reserve-fee.index');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ReserveFee  $reserveFee
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ReserveFee $reserveFee)
    {
        $request->validate(['amount' => 'required|numeric']);

        $reserveFee->fill($request->all());
        $reserveFee->time = now();
        $reserveFee->save();

        flash_message(SystemDefine::UPDATE_SUCCESS_MESSAGE);
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ReserveFee  $reserveFee
     * @return \Illuminate\Http\Response
     */
    public function destroy(ReserveFee $reserveFee)
    {
        $reserveFee->delete();

        flash_message(SystemDefine::DELETE_SUCCESS_MESSAGE);
        return redirect()->route('party.reserve-fee.index');
    }
}
