<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DocumentRequest extends FormRequest
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
            'alias'         => 'required|string',
            'file'          => 'required|file|max:8000|mimes:csv,xlx,xls,xlsx,doc,docx,ppt,pptx,ods,odt,odp,pdf,jpeg,jpg,png,svg,gif,txt',
            'description'   => 'nullable|string'
        ];
    }
}
