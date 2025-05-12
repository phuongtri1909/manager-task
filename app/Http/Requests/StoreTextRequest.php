<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTextRequest extends FormRequest
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
            'handler_ids'       => 'required|array|min:1',
            'handler_ids.*'     => 'exists:users,id',
            'agency'            => 'required|integer',
            'date'              => 'required|date_format:d/m/Y',
            'status'            => 'required|integer',
            'alias'             => 'required|string',
            'description'       => 'nullable|string',
            'file'              => 'required|file|max:8000|mimes:csv,xlx,xls,xlsx,doc,docx,ppt,pptx,ods,odt,odp,pdf,jpeg,jpg,png,svg,gif,txt',
            'keywords'          => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'alias.required' => 'Tiêu đề là trường bắt buộc'
        ];
    }
}
