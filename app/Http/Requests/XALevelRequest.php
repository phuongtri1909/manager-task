<?php

namespace App\Http\Requests;

use App\Helpers\SystemDefine;
use Illuminate\Foundation\Http\FormRequest;

class XALevelRequest extends FormRequest
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
            'ward_id'               => 'required|exists:wards,code',
            'unit_id'               => 'required|exists:units,code',
            'time'                  => 'required|date_format:d/m/Y',
            'debt'                  => 'nullable|string',
            'balance'               => 'nullable|string',
            'number_of_groups'      => 'nullable|string',
            'number_of_borrowers'   => 'nullable|string',
            'unit_check_id'         => 'required|integer|in:' . collect(SystemDefine::UNIT_CHECK())->pluck('id')->join(','),
            'description'           => 'nullable|string',
        ];
    }
}
