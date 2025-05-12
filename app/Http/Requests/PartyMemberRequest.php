<?php

namespace App\Http\Requests;

use App\Helpers\SystemDefine;
use Illuminate\Foundation\Http\FormRequest;

class PartyMemberRequest extends FormRequest
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
            'user_id'                           => 'nullable|exists:users,id',
            'nation_id'                         => 'required|exists:nations,id',
            'religion_id'                       => 'required|exists:religions,id',
            'party_id'                          => 'required|exists:parties,id',
            'name'                              => 'required',
            'avatar'                            => 'required_without:id|image',
            'gender'                            => 'required|in:1,2',
            'date_of_birth'                     => 'required|date_format:d/m/Y',
            'alias'                             => 'nullable',
            'residence'                         => 'nullable',
            'shelter'                           => 'nullable',
            'job'                               => 'nullable',
            'education_level'                   => 'nullable',
            'vocational_education'              => 'nullable',
            'postgraduate'                      => 'nullable',
            'foreign_language'                  => 'nullable',
            'information_technology'            => 'nullable',
            'joining_date'                      => 'nullable|date_format:d/m/Y',
            'joining_place'                     => 'nullable',
            'recognition_date'                  => 'nullable|date_format:d/m/Y',
            'recognition_place_1'               => 'nullable',
            'recognition_place_2'               => 'nullable',
            'academic_rank'                     => 'nullable|in:' . collect(SystemDefine::ACADEMIC_RANKS())->pluck('id')->join(','),
            'political_theory'                  => 'nullable|in:' . collect(SystemDefine::POLITICAL_THEORIES())->pluck('id')->join(','),
            'party_position'                    => 'nullable|in:' . collect(SystemDefine::PARTY_POSITIONS())->pluck('id')->join(','),
            'union_position'                    => 'nullable|in:' . collect(SystemDefine::UNION_POSITIONS())->pluck('id')->join(','),
            'status'                            => 'nullable|in:' . collect(SystemDefine::USER_STATUS())->pluck('id')->join(','),
            'official_code'                     => 'nullable',
            'reserve_code'                      => 'nullable',
            'position_salary_coefficient'       => 'nullable|numeric|min:0',
            'responsibility_salary_coefficient' => 'nullable|numeric|min:0',
            'toxic_salary_coefficient'          => 'nullable|numeric|min:0',
            'regional_allowance'                => 'nullable|numeric|min:0',
            'regional_minimum_wage'             => 'nullable|numeric|min:0',
            'free_party_fee'                    => 'nullable|in:0,1',
        ];
    }

    public function messages()
    {
        return [
            'alias.required' => 'Bí danh là trường bắt buộc'
        ];
    }
}
