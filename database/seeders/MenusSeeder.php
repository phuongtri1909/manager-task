<?php

namespace Database\Seeders;

use App\Helpers\SystemDefine;
use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $menuList = [
            [
                'type'         => 'navbar-search',
                'text'         => 'search',
                'shift'        => 'ml-2',
                'topnav_right' => true,
            ],
            [
                'type'         => 'fullscreen-widget',
                'topnav_right' => true,
            ],
            // Sidebar items:
            [
                'type' => 'sidebar-menu-search',
                'text' => 'search',
            ],
            [
                'text'          => 'Phân quyền hệ thống',
                'url'           => 'departments',
                'icon'          => 'fas fa-fw fa-users',
                'can'           => [SystemDefine::DEPARTMENT_FEATURE . '_' . SystemDefine::ACCESS_PERMISSION],
            ],
            [
                'text'          => 'Quản lý nhân viên',
                'url'           => 'users',
                'icon'          => 'fas fa-fw fa-user',
                'can'           => [SystemDefine::USER_FEATURE . '_' . SystemDefine::ACCESS_PERMISSION],
            ],
            [
                'text'          => 'Quản lý văn bản',
                'icon'          => 'far fa-file-alt',
                'url'           => '#',
                'can'           => [SystemDefine::DOCUMENT_FEATURE . '_' . SystemDefine::ACCESS_PERMISSION],
            ],
            [
                'text'          => 'Quản lý chi bộ',
                'icon'          => 'fas fa-file-invoice-dollar',
                'url'           => '#',
            ],
            [
                'parent_id'     => 7,
                'text'          => 'Quản lý quỹ chi bộ',
                'url'           => '#',
                'shift'         => 'ml-2',
            ],
            [
                'parent_id'     => 6,
                'text'          => 'Toàn bộ',
                'url'           => 'text',
                'can'           => [SystemDefine::DOCUMENT_FEATURE . '_' . SystemDefine::ACCESS_PERMISSION],
                'shift'         => 'ml-2',
            ],
            [
                'parent_id'     => 6,
                'text'          => 'Chưa vào sổ',
                'url'           => 'text/no-process',
                'can'           => [SystemDefine::DOCUMENT_FEATURE . '_' . SystemDefine::ACCESS_PERMISSION],
                'shift'         => 'ml-2',
            ],
            [
                'parent_id'     => 6,
                'text'          => 'Tạo mới',
                'url'           => 'text/create',
                'can'           => [SystemDefine::DOCUMENT_FOR_CLERICAL_FEATURE . '_' . SystemDefine::ACCESS_PERMISSION],
                'shift'         => 'ml-2',
            ],
            [
                'parent_id'     => 7,
                'text'          => 'Quản lý họp chi bộ',
                'url'           => 'party/meeting',
                'can'           => [SystemDefine::PARTY_MEETING_FEATURE . '_' . SystemDefine::ACCESS_PERMISSION],
                'shift'         => 'ml-2',
            ],
            [
                'parent_id'     => 7,
                'text'          => 'Quản lý văn bản chi bộ',
                'url'           => 'party/documents',
                'can'           => [SystemDefine::PARTY_TEXT_FEATURE . '_' . SystemDefine::ACCESS_PERMISSION],
                'shift'         => 'ml-2',
            ],
            [
                'parent_id'     => 7,
                'text'          => 'Kiểm tra đảng viên',
                'url'           => 'party/checks',
                'can'           => [SystemDefine::PARTY_CHECK_FEATURE . '_' . SystemDefine::ACCESS_PERMISSION],
                'shift'         => 'ml-2',
            ],
            [
                'parent_id'     => 7,
                'text'          => 'Học tập tấm gương Bác',
                'url'           => 'party/study',
                'can'           => [SystemDefine::PARTY_STUDY_FEATURE . '_' . SystemDefine::ACCESS_PERMISSION],
                'shift'         => 'ml-2',
            ],
            [
                'parent_id'     => 7,
                'text'          => 'Quản lý đảng viên',
                'url'           => 'party/members',
                'can'           => [SystemDefine::PARTY_MEMBER_FEATURE . '_' . SystemDefine::ACCESS_PERMISSION],
                'shift'         => 'ml-2',
            ],
            [
                // 'parent_id'     => 7,
                'icon'          => 'fas fa-file-invoice-dollar',
                'text'          => 'Phân công giao dịch xã',
                'url'           => 'party/job-assignment',
                'can'           => [SystemDefine::PARTY_JOB_ASSIGNMENT_FEATURE . '_' . SystemDefine::ACCESS_PERMISSION],
                // 'shift'         => 'ml-2',
            ],
            [
                'parent_id'     => 8,
                'text'          => 'Tồn quỹ',
                'shift'         => 'ml-3',
                'url'           => 'party/reserve-fee',
                'can'           => [SystemDefine::PARTY_FEE_FEATURE . '_' . SystemDefine::ACCESS_PERMISSION],
            ],
            [
                'parent_id'     => 8,
                'text'          => 'Thu trong kỳ',
                'url'           => '#',
                'shift'         => 'ml-3',
            ],
            [
                'parent_id'     => 8,
                'text'          => 'Chi – Thu khác',
                'shift'         => 'ml-3',
                'url'           => 'party/other-fee',
                'can'           => [SystemDefine::PARTY_FEE_OTHER_FEATURE . '_' . SystemDefine::ACCESS_PERMISSION],
            ],
            [
                'parent_id'     => 8,
                'text'          => 'Thống kê - Báo cáo',
                'url'           => '#',
                'shift'         => 'ml-3',
            ],
            [
                'parent_id'     => 19,
                'text'          => 'Thu đảng phí',
                'shift'         => 'ml-4',
                'url'           => 'party/party-fee',
                'can'           => [SystemDefine::PARTY_FEE_MEMBER_FEATURE . '_' . SystemDefine::ACCESS_PERMISSION],
            ],
            [
                'parent_id'     => 19,
                'text'          => 'Thu đảng phí bổ sung',
                'shift'         => 'ml-4',
                'url'           => 'party/additional-party-fee',
                'can'           => [SystemDefine::PARTY_FEE_MEMBER_ADDITIONAL_FEATURE . '_' . SystemDefine::ACCESS_PERMISSION],
            ],
            [
                'parent_id'     => 21,
                'text'          => 'Báo cáo thu nộp bảng phí',
                'shift'         => 'ml-4',
                'url'           => 'party/report-inout',
                'can'           => [SystemDefine::PARTY_FEE_REPORT_FEATURE . '_' . SystemDefine::ACCESS_PERMISSION],
            ],
            [
                'parent_id'     => 21,
                'text'          => 'Bảng tổng hợp thu nộp đảng phí hàng tháng',
                'shift'         => 'ml-4',
                'url'           => 'party/synthetic-monthly',
                'can'           => [SystemDefine::PARTY_FEE_REPORT_BY_MONTH_FEATURE . '_' . SystemDefine::ACCESS_PERMISSION],
            ],
            [
                'text'          => 'Quản lý danh mục',
                'icon'          => 'fab fa-meetup',
            ],
            [
                'parent_id'     => 26,
                'text'          => 'Quận/huyện',
                'url'           => 'district',
                'shift'         => 'ml-2',
                'can'           => [SystemDefine::CATEGORY_DISTRICT_FEATURE . '_' . SystemDefine::ACCESS_PERMISSION],
            ],
            [
                'parent_id'     => 26,
                'text'          => 'Phường/xã',
                'url'           => 'ward',
                'shift'         => 'ml-2',
                'can'           => [SystemDefine::CATEGORY_WARD_FEATURE . '_' . SystemDefine::ACCESS_PERMISSION],
            ],
            [
                'parent_id'     => 26,
                'text'          => 'Hội uỷ thác',
                'url'           => 'unit',
                'shift'         => 'ml-2',
                'can'           => [SystemDefine::CATEGORY_UNIT_FEATURE . '_' . SystemDefine::ACCESS_PERMISSION],
            ],
            [
                'parent_id'     => 26,
                'text'          => 'Tổ',
                'url'           => 'town',
                'shift'         => 'ml-2',
                'can'           => [SystemDefine::CATEGORY_TOWN_FEATURE . '_' . SystemDefine::ACCESS_PERMISSION],
            ],
            [
                'parent_id'     => 26,
                'text'          => 'Hộ',
                'url'           => 'household',
                'shift'         => 'ml-2',
                'can'           => [SystemDefine::CATEGORY_HOUSEHOLD_FEATURE . '_' . SystemDefine::ACCESS_PERMISSION],
            ],
            [
                'text'          => 'Quản lý khen thưởng',
                'icon'          => 'fa fa-trophy',
            ],
            [
                'parent_id'     => 32,
                'text'          => 'Quyết định khen thưởng',
                'url'           => 'decide-reward',
                'shift'         => 'ml-2',
                'can'           => [SystemDefine::DECIDE_REWARD_FEATURE . '_' . SystemDefine::ACCESS_PERMISSION],
            ],
            [
                'parent_id'     => 32,
                'text'          => 'Tra cứu - Cá nhân',
                'url'           => 'personal-lookup',
                'shift'         => 'ml-2',
                'can'           => [SystemDefine::DECIDE_REWARD_PERSON_LOOKUP_FEATURE . '_' . SystemDefine::ACCESS_PERMISSION],
            ],
            [
                'parent_id'     => 32,
                'text'          => 'Tra cứu - Tập thể',
                'url'           => 'collective-search',
                'shift'         => 'ml-2',
                'can'           => [SystemDefine::DECIDE_REWARD_COLLECTIVE_SEARCH_FEATURE . '_' . SystemDefine::ACCESS_PERMISSION],
            ],
            [
                'text'          => 'Quản lý công tác kiểm tra',
                'icon'          => 'fa fa-check',
            ],
            [
                'parent_id'     => 36,
                'text'          => 'Nhập dữ liệu',
                'shift'         => 'ml-2',
            ],
            [
                'parent_id'     => 37,
                'text'          => 'Cấp huyện',
                'url'           => 'checks/districts',
                'shift'         => 'ml-4',
                'can'           => [SystemDefine::CHECK_DISTRICT_LEVEL_FEATURE . '_' . SystemDefine::ACCESS_PERMISSION],
            ],
            [
                'parent_id'     => 37,
                'text'          => 'Cấp xã',
                'url'           => 'checks/wards',
                'shift'         => 'ml-4',
                'can'           => [SystemDefine::CHECK_WARD_LEVEL_FEATURE . '_' . SystemDefine::ACCESS_PERMISSION],
            ],
            [
                'parent_id'     => 37,
                'text'          => 'Cấp tổ',
                'url'           => 'checks/towns',
                'shift'         => 'ml-4',
                'can'           => [SystemDefine::CHECK_TOWN_LEVEL_FEATURE . '_' . SystemDefine::ACCESS_PERMISSION],
            ],
            [
                'parent_id'     => 37,
                'text'          => 'Hộ vay',
                'url'           => 'checks/households',
                'shift'         => 'ml-4',
                'can'           => [SystemDefine::CHECK_HOUSEHOLD_LEVEL_FEATURE . '_' . SystemDefine::ACCESS_PERMISSION],
            ],
            [
                'parent_id'     => 36,
                'text'          => 'Báo cáo',
                'url'           => 'checks/statistics',
                'shift'         => 'ml-2',
                'can'           => [SystemDefine::CHECK_STATISTIC_FEATURE . '_' . SystemDefine::ACCESS_PERMISSION],
            ],
        ];

        foreach ($menuList as $menu) {
            Menu::create(['config' => $menu, 'created_by' => 1, 'parent_id' => @$menu['parent_id']]);
        }
    }
}
