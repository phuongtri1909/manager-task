<?php

namespace App\Http\Controllers\Party\Fee;

use App\Helpers\SystemDefine;
use App\Http\Controllers\Controller;
use App\Models\PartyFee;
use App\Models\PartyMember;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class PartyFeeController extends Controller
{
    public string $title = 'Thu đảng phí';

    public function __construct()
    {
        $this->featureSlug = SystemDefine::PARTY_FEE_MEMBER_FEATURE;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */ 

    public function index(Request $request)
    {
       
        $userLogin = Auth::user()->id;
        $isAdmin = is_admin(auth()->user());
        $featureSlug = "thu_dang_phi";
       

        $canModify = User::where('id', $userLogin)
        ->whereHas('permissions', function ($query) use ($featureSlug) {
            $query->where('feature_slug', $featureSlug)->where('permission_slug', 'sua');
        })
        ->first();
    //    print_r($canEdit);die();

        $canDeleterec = User::where('id', $userLogin)
        ->whereHas('permissions', function ($query) use ($featureSlug) {
            $query->where('feature_slug', $featureSlug)->where('permission_slug', 'xoa');
        })
        ->first();

        
        $time           = Carbon::createFromFormat('m/Y', $request->month ?? now()->format('m/Y'));
        $partyFees      = PartyFee::with('member')
            ->active()            
            ->whereMonth('time', $time)
            ->whereYear('time', $time)
            ->orderBy('sx','ASC')
            ->get();
//print_r($partyFees);die();
        return $this->view('party.party-fee.index', compact('partyFees','isAdmin','canModify','canDeleterec'));
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
        $time        = Carbon::createFromFormat('m/Y', $request->month ?? now()->format('m/Y'));              
        // ->firstOfMonth()
        //$members    = PartyMember::active()->whereDate('created_at', '<=', $time)->get();
        $members    = PartyMember::active()->where('status','1')->orWhere('status','3')->get();
       // print_r($members);die();
        foreach ($members as $member) {          
            // ROUND(Mức lương tối thiểu vùng * (Hệ số lương cấp bậc + hệ số lương chức vụ + Hệ số lương trách nhiệm + Hệ số lương độc hại) + Phụ cấp khu vực), -2)
            $income         = round(
                doubleval($member->regional_minimum_wage)
                    * (doubleval($member->reserve_code) + doubleval($member->position_salary_coefficient) + doubleval($member->responsibility_salary_coefficient)+ doubleval($member->toxic_salary_coefficient))
                    + doubleval($member->regional_allowance),
                SystemDefine::PRECISION
            );
            // ROUND((Ct 2 * 1%), -2)
            $fee            = $member->status == 3 ? 0 : round($income * (1 / 100), SystemDefine::PRECISION);
            $feeClone       = $member->status == 3 ? 0 : $fee;                    
            $previousFee    = $time->month === 1 ? PartyFee::with('member')
                                ->active()
                                ->whereMonth('time', '12')
                                ->whereYear('time', $time->year - 1)
                                ->where('party_member_id', $member->id)
                                ->value('fee_clone') : 0;         
          
            //echo $previousFee;die();   
           // $previousFee    = $fee;
            $total          = $fee + $previousFee;

            $partyFee = new PartyFee;
            $partyFee->fill([
                'sx'                => $member->sx,
                'party_member_id'   => $member->id,
                'time'              => $time,
                'income'            => $member->free_party_fee ? 0 : $income,
                'fee'               => $member->free_party_fee ? 0 : $fee,
                'fee_clone'         => $member->free_party_fee ? 0 : $feeClone,
                'previous_fee'      => $member->free_party_fee ? 0 : $previousFee,
                'total'             => $member->free_party_fee ? 0 : $total,
            ]);
            $partyFee->save();
        }

        flash_message(SystemDefine::CREATE_SUCCESS_MESSAGE);
        return back();
    }


    public function editpreviousFee(PartyFee $partyfee)
    {
      //$mm = $partyfee;
      //print_r($request);die();
      $members    = PartyMember::active()->where('status','1')->get();
      return $this->view('party.party-fee.form', compact('members','partyfee'));
      
    }

    public function updatepreviousFee(Request $request, $id)
    {
        // $request->validate([
        //     'previous_fee'      => 'required|array',
        //     'previous_fee.*'    => 'numeric',
        //     'fee_clone'         => 'required|array',
        //     'fee_clone.*'       => 'numeric',
        // ]);



        $partyFees  = PartyFee::with('member')
            ->active()           
            ->where('id', $id)                    
            ->first();
           
                $feeClone               = doubleval($partyFees->fee_clone ?? 0);
                $previousFee            = doubleval($request->previous_fee ?? $partyFees->previous_fee);
                $total                  = $feeClone + $previousFee;
              
                $partyFees->fee_clone    = $feeClone;
                $partyFees->previous_fee = $previousFee;
                $partyFees->total        = $total;
                $partyFees->save();
            

        flash_message(SystemDefine::UPDATE_SUCCESS_MESSAGE);
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
            'previous_fee'      => 'required|array',
            'previous_fee.*'    => 'numeric',
            'fee_clone'         => 'required|array',
            'fee_clone.*'       => 'numeric',
        ]);



        $time       = Carbon::createFromFormat('m/Y', $request->month ?? now()->format('m/Y'));
        $partyFees  = PartyFee::with('member')
            ->active()           
            ->whereMonth('time', $time)
            ->whereYear('time', $time)
            ->get();

        foreach ($partyFees as $index => $partyFee) {
            $feeClone               = doubleval($request->fee_clone[$index] ?? $partyFee->fee_clone);
            $previousFee            = doubleval($request->previous_fee[$index] ?? $partyFee->previous_fee);
            $total                  = $feeClone + $previousFee;

            $partyFee->fee_clone    = $feeClone;
            $partyFee->previous_fee = $previousFee;
            $partyFee->total        = $total;
            $partyFee->save();
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
        $partyFees = PartyFee::with('member')
            ->active()         
            ->whereMonth('time', $time)
            ->whereYear('time', $time)
            ->orderBy('sx','ASC')
            ->get();
        $pdf = Pdf::loadView('pdf.party-fee', [
            'partyFees' => $partyFees,
            'time' => $time
        ]);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->download('Thu_dang_phi-'.$mm.'.pdf');
    }

//27-7-2023
        
    public function delete(Request $request)
    {
        $time           = Carbon::createFromFormat('m/Y', $request->month ?? now()->format('m/Y'));            
        $partyFees      = PartyFee::with('member')
            ->active()               
            ->whereMonth('time', $time)
            ->whereYear('time', $time)
            ->delete();

            flash_message(SystemDefine::DELETE_SUCCESS_MESSAGE);
            return back();
    }

    public function destroy($id)
    {
       
        $time           = Carbon::createFromFormat('m/Y', $request->month ?? now()->format('m/Y'));            
        $partyFees      = PartyFee::with('member')
            ->active()     
            ->where('id', $id)
            ->delete();

            flash_message(SystemDefine::DELETE_SUCCESS_MESSAGE);
            return back();
    }

//
}