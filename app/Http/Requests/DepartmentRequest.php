<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DepartmentRequest extends FormRequest
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
            'name'      => 'required|string|max:255|unique:App\Models\Department,name,' . @$this->department->id,
            'parent_id' => 'nullable|integer|exists:App\Models\Department,id',
            'level'     => 'nullable|integer|min:1|max:3'
        ];
    }
}
