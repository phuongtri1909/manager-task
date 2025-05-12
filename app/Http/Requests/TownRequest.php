<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TownRequest extends FormRequest
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
            'code'  => 'required|string|max:255|unique:towns,code,' . @$this->town->id,
            'name'  => 'required|string|max:255|unique:towns,name,' . @$this->town->id,
            'ward_id' => 'required|integer|exists:App\Models\Ward,id',
        ];
    }
}
