<?php

namespace App\Http\Requests;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Helpers\SystemDefine;

class UserRequest extends FormRequest
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
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'can_assign_job' => $this->can_assign_job ? !!$this->can_assign_job : null,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $passwordRule = $this->isMethod('POST') ? 'required' : 'nullable';

        $ds_co_tong_hop = implode(',', SystemDefine::DS_CO_TONG_HOP);
        $ds_ma_don_vi_cong_tac = implode(',', array_column(SystemDefine::DS_DON_VI_CONG_TAC($this->co_tong_hop ?? null), 'id')); 
        $ds_ma_chuc_vu = implode(',', array_column(SystemDefine::DS_CHUC_VU($this->co_tong_hop ?? null), 'id'));

        return [
            'can_assign_job'            => 'nullable|boolean',
            'code_for_job_assignment'   => 'required_with:can_assign_job|nullable|string|max:255',
            'code'                      => 'required|string|max:255|unique:App\Models\User,code,' . @$this->user->id,
            'name'                      => 'required|string|max:255',
            'email'                     => 'required|email|max:255|unique:App\Models\User,email,' . @$this->user->id,
            'department_id'             => 'required|integer|exists:App\Models\Department,id',
            'position_id'               => 'required|integer|exists:App\Models\Position,id',
            'unit_id'                   => 'nullable|exists:App\Models\Unit,id',
            'password'                  => $passwordRule . '|min:8',
            'ward_ids'                  => 'nullable|array',
            'ward_ids.*'                => [
                // 'integer',
                'distinct',
                'exists:App\Models\Ward,id',
                Rule::unique('user_ward', 'ward_id')->where(fn (Builder $query) => $query->where('user_id', '!=', @$this->user->id))
            ],
            'permission_ids'            => 'nullable|array',
            'permission_ids.*'          => 'nullable|integer|exists:permissions,id',
            'nguoi_nhap'                => 'nullable|string|max:255',
            'id_can_bo'                 => 'nullable|string|max:255',
            'co_tong_hop'               => 'nullable|string|in:' . $ds_co_tong_hop,
            'ma_don_vi_cong_tac'        => 'nullable|integer|in:' . $ds_ma_don_vi_cong_tac,
            'ma_chuc_vu'                => 'nullable|integer|in:' . $ds_ma_chuc_vu,
        ];
    }
}
