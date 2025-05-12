<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PartyDocumentRequest extends FormRequest
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
        $isEdit = $this->method() === 'PUT';

        return [
            'name'          => 'required|string',
            'time'          => 'required|date_format:d/m/Y',
            'description'   => 'required|string',
            'direction'     => 'required|string|in:incoming_text,text_travels,other',
            'type'          => 'required|string|in:resolution,decision,other',
            'file'          => ($isEdit ? 'nullable' : 'required') . '|file|max:8000|mimes:csv,xlx,xls,xlsx,doc,docx,ppt,pptx,ods,odt,odp,pdf,jpeg,jpg,png,svg,gif,txt',
        ];
    }
}
