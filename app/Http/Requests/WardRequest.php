<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WardRequest extends FormRequest
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
        return [
            'code'  => 'required|string|max:255|unique:wards,code,' . @$this->ward->id,
            'name'  => 'required|string|max:255|unique:wards,name,' . @$this->ward->id,
            'district_id' => 'required|integer|exists:App\Models\District,id',
        ];
    }
}
