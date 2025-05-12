<?php

namespace App\Http\Requests;

use App\Helpers\SystemDefine;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreJobAssignmentRequest extends FormRequest
{
    protected $redirectRoute = 'party.job-assignment.create';

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
        $count = count(SystemDefine::WORK_SCHEDULE_POSITIONS());
        return [
            'date'              => 'required|date_format:d/m/Y',
            'persons_count'     => "required|numeric|min:3|max:{$count}",
            'ward_id'           => [
                'required',
                'exists:wards,id',
                Rule::unique('job_assignments', 'ward_id')->where(function (Builder $query) {
                    $query->whereDate('date', Carbon::createFromFormat('d/m/Y', $this->date));
                })
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'ward_id.unique' => 'Xã đã tồn tại phân công vào ngày này.'
        ];
    }
}
