<?php

namespace App\Http\Requests\Check;

use App\Helpers\SystemDefine;
use Illuminate\Foundation\Http\FormRequest;

class TownLevelRequest extends FormRequest
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
            'town_id'               => 'required|exists:towns,id',
            'unit_id'               => 'required|exists:units,id',
            'time'                  => 'required|date_format:d/m/Y',
            'debt'                  => "required|numeric|min:0|max:${unsignedDoubleLength}",
            'balance'               => "required|numeric|min:0|max:${unsignedDoubleLength}",
            'number_of_groups'      => "required|integer|min:0|max:{$unsignedIntLength}",
            'number_of_borrowers'   => "required|integer|min:0|max:{$unsignedIntLength}",
            'unit_check_id'         => 'required|integer|in:' . collect(SystemDefine::UNIT_CHECK())->pluck('id')->join(','),
            'description'           => 'nullable|string',
        ];
    }
}
