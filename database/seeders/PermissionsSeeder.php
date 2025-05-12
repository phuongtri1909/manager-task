<?php

namespace Database\Seeders;

use App\Helpers\SystemDefine;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $featureList = [
            // module quản lý phòng ban/phân quyền
            [
                'name' => 'phân quyền quản trị',
                'slug' => SystemDefine::DEPARTMENT_FEATURE
            ],

            // module quản lý nhân viên
            [
                'name' => 'quản lý nhân viên',
                'slug' => SystemDefine::USER_FEATURE
            ],

            // module quản lý văn bản
            [
                'name' => 'quản lý văn bản',
                'slug' => SystemDefine::DOCUMENT_FEATURE
            ],

            // module quản lý chi bộ
            [
                'name' => 'quản lý họp chi bộ',
                'slug' => SystemDefine::PARTY_MEETING_FEATURE
            ],
            [
                'name' => 'quản lý văn bản chi bộ',
                'slug' => SystemDefine::PARTY_TEXT_FEATURE
            ],
            [
                'name' => 'kiểm tra đảng viên',
                'slug' => SystemDefine::PARTY_CHECK_FEATURE
            ],
            [
                'name' => 'học tập tấm gương Bác',
                'slug' => SystemDefine::PARTY_STUDY_FEATURE
            ],
            [
                'name' => 'quản lý đảng viên',
                'slug' => SystemDefine::PARTY_MEMBER_FEATURE
            ],
            [
                'name' => 'quản lý phân công giao dịch xã',
                'slug' => SystemDefine::PARTY_JOB_ASSIGNMENT_FEATURE
            ],

            // module quản lý quỹ chi bộ
            [
                'name' => 'tồn quỹ',
                'slug' => SystemDefine::PARTY_FEE_FEATURE
            ],
            [
                'name' => 'chi thu khác',
                'slug' => SystemDefine::PARTY_FEE_OTHER_FEATURE
            ],
            [
                'name' => 'thu đảng phí',
                'slug' => SystemDefine::PARTY_FEE_MEMBER_FEATURE
            ],
            [
                'name' => 'thu đảng phí bổ sung',
                'slug' => SystemDefine::PARTY_FEE_MEMBER_ADDITIONAL_FEATURE
            ],
            [
                'name' => 'báo cáo thu nộp bảng phí',
                'slug' => SystemDefine::PARTY_FEE_REPORT_FEATURE
            ],
            [
                'name' => 'bảng tổng hợp thu nộp đảng phí hàng tháng',
                'slug' => SystemDefine::PARTY_FEE_REPORT_BY_MONTH_FEATURE
            ],

            // module khen thưởng
            [
                'name' => 'quyết định khen thưởng',
                'slug' => SystemDefine::DECIDE_REWARD_FEATURE
            ],
            [
                'name' => 'tra cứu - cá nhân',
                'slug' => SystemDefine::DECIDE_REWARD_PERSON_LOOKUP_FEATURE
            ],
            [
                'name' => 'tra cứu - tập thể',
                'slug' => SystemDefine::DECIDE_REWARD_COLLECTIVE_SEARCH_FEATURE
            ],

            // module quản lý danh mục
            [
                'name' => 'quản lý quận/huyện',
                'slug' => SystemDefine::CATEGORY_DISTRICT_FEATURE
            ],
            [
                'name' => 'quản lý phường/xã',
                'slug' => SystemDefine::CATEGORY_WARD_FEATURE
            ],
            [
                'name' => 'quản lý tổ',
                'slug' => SystemDefine::CATEGORY_TOWN_FEATURE
            ],
            [
                'name' => 'quản lý hộ',
                'slug' => SystemDefine::CATEGORY_HOUSEHOLD_FEATURE
            ],
            [
                'name' => 'quản lý đơn vị',
                'slug' => SystemDefine::CATEGORY_UNIT_FEATURE
            ],

            // module công tác kiểm tra
            [
                'name' => 'công tác kiểm tra cấp huyện',
                'slug' => SystemDefine::CHECK_DISTRICT_LEVEL_FEATURE
            ],
            [
                'name' => 'công tác kiểm tra cấp xã',
                'slug' => SystemDefine::CHECK_WARD_LEVEL_FEATURE
            ],
            [
                'name' => 'công tác kiểm tra cấp tổ',
                'slug' => SystemDefine::CHECK_TOWN_LEVEL_FEATURE
            ],
            [
                'name' => 'công tác kiểm tra hộ vay',
                'slug' => SystemDefine::CHECK_HOUSEHOLD_LEVEL_FEATURE
            ],
            [
                'name' => 'thống kê công tác kiểm tra',
                'slug' => SystemDefine::CHECK_STATISTIC_FEATURE
            ],
        ];

        $permissionList = [
            [
                'name' => 'Truy cập',
                'slug' => SystemDefine::ACCESS_PERMISSION
            ],
            [
                'name' => 'Thêm mới',
                'slug' => SystemDefine::CREATE_PERMISSION
            ],
            [
                'name' => 'Sửa',
                'slug' => SystemDefine::EDIT_PERMISSION
            ],
            [
                'name' => 'Xoá',
                'slug' => SystemDefine::DELETE_PERMISSION
            ],
            [
                'name' => 'Duyệt',
                'slug' => SystemDefine::ACCEPT_PERMISSION
            ],
            [
                'name' => 'Xem tất cả',
                'slug' => SystemDefine::VIEW_PERMISSION
            ],
        ];

        foreach ($featureList as $feature) {
            foreach ($permissionList as $permission) {
                Permission::create([
                    'feature_slug' => $feature['slug'],
                    'permission_slug' => $permission['slug'],
                    'name' => $permission['name'] . ' ' . $feature['name'],
                ]);
            }
        }
    }
}
