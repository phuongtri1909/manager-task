<?php

namespace App\Http\Requests;

use App\Helpers\SystemDefine;
use Illuminate\Foundation\Http\FormRequest;

class UpdateJobAssignmentRequest extends FormRequest
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
        $this->redirect = route('party.job-assignment.edit', $this->id);
        $count = count(SystemDefine::WORK_SCHEDULE_POSITIONS());
        return [
            'persons_count'   => "required|numeric|min:3|max:{$count}",
            'user_ids'        => "required|array|min:3|max:{$count}",
            'user_ids.*'      => 'nullable|exists:users,id',
        ];
    }
}
