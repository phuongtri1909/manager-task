<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DecideRewardRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * 'unit_id' => 'required|exists:App\Models\Unit,id',
     * @return array
     */
    public function rules()
    {
        return [
            'dicision_number' => 'required',
            'date' => 'required',
            'unit_id' => 'required',
            'year' => 'required',
            'signer' => 'required',
            'signer_position' => 'required',
            'type' => 'required',
            'reward_form' => 'required',
            'content' => 'nullable',
           // 'is_personal' => 'required',
        ];
    }
}
