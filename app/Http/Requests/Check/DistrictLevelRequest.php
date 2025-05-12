<?php

namespace App\Http\Requests\Check;

use App\Helpers\SystemDefine;
use Illuminate\Foundation\Http\FormRequest;

class DistrictLevelRequest extends FormRequest
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
     *
     * @return array
     */
    public function rules()
    {
        $unsignedDoubleLength   = pow(2, 8 * 8);
        $unsignedIntLength      = pow(2, 8 * 4);

        return [
            'district_id'           => 'required|exists:districts,id',
            'unit_id'               => 'required|exists:units,id',
            'time'                  => 'required|date_format:d/m/Y',
            'debt'                  => "required|numeric|min:0|max:${unsignedDoubleLength}",
            'balance'               => "required|numeric|min:0|max:${unsignedDoubleLength}",
            'number_of_groups'      => "required|integer|min:0|max:{$unsignedIntLength}",
            'number_of_borrowers'   => "required|integer|min:0|max:{$unsignedIntLength}",
            'unit_check_id'         => 'nullable|integer|in:' . collect(SystemDefine::UNIT_CHECK())->pluck('id')->join(','),
            'description'           => 'nullable|string',
        ];
    }
}
