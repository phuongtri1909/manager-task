<?php

namespace App\Helpers;

class SystemDefine
{
    const ADMIN_DEPARTMENT = 'admin';

    const ACCESS_PERMISSION = 'truy_cap';
    const CREATE_PERMISSION = 'them';
    const EDIT_PERMISSION = 'sua';
    const DELETE_PERMISSION = 'xoa';
    const ACCEPT_PERMISSION = 'duyet';
    const VIEW_PERMISSION = 'xem_tat_ca';

    const DEPARTMENT_FEATURE = 'phan_quyen_quan_tri';
    const USER_FEATURE = 'quan_ly_nhan_vien';
    const DOCUMENT_FEATURE = 'quan_ly_tai_lieu';
    const DOCUMENT_FOR_CLERICAL_FEATURE = 'quan_ly_tai_lieu_van_thu';
    const PARTY_MEETING_FEATURE = 'quan_ly_hop_chi_bo';
    const PARTY_TEXT_FEATURE = 'quan_ly_van_ban_chi_bo';
    const PARTY_CHECK_FEATURE = 'kiem_tra_chi_bo';
    const PARTY_STUDY_FEATURE = 'hoc_tap_tam_guong_bac';
    const PARTY_MEMBER_FEATURE = 'quan_ly_dang_vien';
    const PARTY_JOB_ASSIGNMENT_FEATURE = 'quan_ly_phan_cong_cong_tac';
    const PARTY_FEE_FEATURE = 'ton_quy';
    const PARTY_FEE_OTHER_FEATURE = 'chi_thu_khac';
    const PARTY_FEE_MEMBER_FEATURE = 'thu_dang_phi';
    const PARTY_FEE_MEMBER_ADDITIONAL_FEATURE = 'thu_dang_phi_bo_sung';
    const PARTY_FEE_REPORT_FEATURE = 'thong_ke_bao_cao';
    const PARTY_FEE_REPORT_BY_MONTH_FEATURE = 'thong_ke_bao_cao_theo_thang';
    const CATEGORY_DISTRICT_FEATURE = 'quan_ly_huyen';
    const CATEGORY_WARD_FEATURE = 'quan_ly_xa';
    const CATEGORY_UNIT_FEATURE = 'don_vi';
    const CATEGORY_TOWN_FEATURE = 'quan_ly_to';
    const CATEGORY_HOUSEHOLD_FEATURE = 'quan_ly_ho';
    const DECIDE_REWARD_FEATURE = 'quyet_dinh_khen_thuong';
    const DECIDE_REWARD_PERSON_LOOKUP_FEATURE = 'tra_cuu_ca_nhan';
    const DECIDE_REWARD_COLLECTIVE_SEARCH_FEATURE = 'tra_cuu_tap_the';
    const CHECK_DISTRICT_LEVEL_FEATURE = 'kiem_tra_cap_huyen';
    const CHECK_WARD_LEVEL_FEATURE = 'kiem_tra_cap_xa';
    const CHECK_TOWN_LEVEL_FEATURE = 'kiem_tra_cap_to';
    const CHECK_HOUSEHOLD_LEVEL_FEATURE = 'kiem_tra_ho_vay';
    const CHECK_STATISTIC_FEATURE = 'kiem_tra_thong_ke';
    const CHECK_GDX_FEATURE = 'quan_ly_kiem_tra_gdx';
    const CHECK_GDX_IP_FEATURE = 'quan_ly_giam_sat_gdx_ip';
    const CHECK_MROOM_FEATURE = 'quan_ly_phong_hop_khong_giay';

    const PRECISION = -2;
    const CREATE_SUCCESS_MESSAGE = 'TẠO MỚI THÀNH CÔNG';
    const UPDATE_SUCCESS_MESSAGE = 'CẬP NHẬT THÀNH CÔNG';
    const DELETE_SUCCESS_MESSAGE = 'XÓA THÀNH CÔNG';
    const SHARE_SUCCESS_MESSAGE = 'CHIA SẺ THÀNH CÔNG';
    const COMPLETE_SUCCESS_MESSAGE = 'HOÀN THÀNH';
    const QUARTER = [
        1 => [1, 2, 3],
        2 => [4, 5, 6],
        3 => [7, 8, 9],
        4 => [10, 11, 12],
    ];

    CONST DS_CO_TONG_HOP = ['S', 'M'];

    static function ACADEMIC_RANKS(string|int $id = null)
    {
        $items = [
            ['id' => 1, 'name' => 'Thạc sĩ'],
            ['id' => 2, 'name' => 'Tiên sĩ'],
            ['id' => 3, 'name' => 'Giáo sĩ'],
        ];

        return empty($id) ? $items : collect($items)->where('id', $id)->first();
    }

    static function POLITICAL_THEORIES(string|int $id = null)
    {
        $items = [
            ['id' => 1, 'name' => 'Trung cấp'],
            ['id' => 2, 'name' => 'Cao cấp'],
            ['id' => 3, 'name' => 'Sơ cấp'],
        ];

        return empty($id) ? $items : collect($items)->where('id', $id)->first();
    }

    static function PARTY_BADGES(string|int $id = null)
    {
        $items = [
            ['id' => 1, 'name' => '20 năm'],
            ['id' => 2, 'name' => '25 năm'],
            ['id' => 3, 'name' => '30 năm'],
        ];

        return empty($id) ? $items : collect($items)->where('id', $id)->first();
    }

    static function PARTY_POSITIONS(string|int $id = null)
    {
        $items = [
            ['id' => 1, 'name' => 'Bí thư'],
            ['id' => 2, 'name' => 'Phó bí thư'],
            ['id' => 3, 'name' => 'Chi ủy viên'],
            ['id' => 4, 'name' => 'Đảng viên'],
        ];

        return empty($id) ? $items : collect($items)->where('id', $id)->first();
    }

    static function UNION_POSITIONS(string|int $id = null)
    {
        $items = [
            ['id' => 1, 'name' => 'Chủ tịch CDCS'],
            ['id' => 2, 'name' => 'Phó chủ tịch CDCS'],
            ['id' => 3, 'name' => 'Ủy viên BCH, Ủy viên BTV, Trưởng ban Nữ công'],
            ['id' => 4, 'name' => 'Ủy viên BCH, Chủ nhiệm UBKT'],
            ['id' => 5, 'name' => 'Ủy viên BCH'],
            ['id' => 6, 'name' => 'Đoàn viên'],
        ];

        return empty($id) ? $items : collect($items)->where('id', $id)->first();
    }

    static function USER_STATUS(string|int $id = null)
    {
        $items = [
            ['id' => 1, 'name' => 'Hoạt động'],
            ['id' => 2, 'name' => 'Không hoạt động'],
            ['id' => 3, 'name' => 'Truy thu đảng phí'],
        ];

        return empty($id) ? $items : collect($items)->where('id', $id)->first();
    }

    static function USER_GENDER(string|int $id = null)
    {
        $items = [
            ['id' => 1, 'name' => 'Nam'],
            ['id' => 2, 'name' => 'Nữ'],
        ];

        return empty($id) ? $items : collect($items)->where('id', $id)->first();
    }

    static function CONFIRMATION(string|int $id = null)
    {
        $items = [
            ['id' => 1, 'name' => 'Có'],
            ['id' => 0, 'name' => 'Không']
        ];

        return empty($id) ? $items : collect($items)->where('id', $id)->first();
    }

    static function TEXT_STATUS(string|int $id = null)
    {
        $items = [
            ['id' => 1, 'name' => 'Đang xử lý', 'bg' => 'primary'],
            ['id' => 2, 'name' => 'Kết thúc xử lý', 'bg' => 'info'],
            ['id' => 3, 'name' => 'Không xử lý', 'bg' => 'danger'],
            ['id' => 4, 'name' => 'Hoàn thành xử lý', 'bg' => 'success'],
            ['id' => 5, 'name' => 'Văn bản bị thu hồi', 'bg' => 'warning'],
        ];

        return empty($id) ? $items : collect($items)->where('id', $id)->first();
    }

    static function DEPARTMENT_LEVEL(string|int $id = null)
    {
        $items = [
            ['id' => 1, 'name' => 'Cấp huyện', 'bg' => 'primary'],
            ['id' => 2, 'name' => 'Cấp tỉnh', 'bg' => 'success'],
            ['id' => 3, 'name' => 'Quản lý cấp tỉnh', 'bg' => 'warning'],
        ];
        return empty($id) ? $items : collect($items)->where('id', $id)->first();
    }

    static function AGENCIES(string|int $id = null)
    {
        $items = [
            ['id' => 1, 'name' => 'Phòng HCTC'],
            ['id' => 2, 'name' => 'Phòng KHNV'],
            ['id' => 3, 'name' => 'Phòng KTNQ'],
            ['id' => 4, 'name' => 'Phòng Tin học'],
            ['id' => 5, 'name' => 'Phòng KTKSNB'],
            ['id' => 6, 'name' => 'Phòng giao dịch'],
            ['id' => 7, 'name' => 'Ủy ban nhân dân'],
            ['id' => 8, 'name' => 'Hội đoàn thể'],
            ['id' => 9, 'name' => 'Khác'],
        ];

        return empty($id) ? $items : collect($items)->where('id', $id)->first();
    }

    static function REWARD_FORM(string|int $id = null)
    {
        $items = [
            ['id' => 1, 'name' => 'Giấy khen'],
            ['id' => 2, 'name' => 'Bằng khen'],
            ['id' => 3, 'name' => 'Huân chương'],
            ['id' => 4, 'name' => 'Huy chương'],
            ['id' => 5, 'name' => 'HTSX'],
            ['id' => 6, 'name' => 'HTT'],
            ['id' => 7, 'name' => 'HT'],
            ['id' => 8, 'name' => 'Cờ thi đua'],
        ];

        return empty($id) ? $items : collect($items)->where('id', $id)->first();
    }
//25-04-2023

static function SUPERIOR_UNIT(string|int $id = null)
{
    $items = [
        ['id' => 1, 'name' => 'Tổng LĐLĐ'],
        ['id' => 2, 'name' => 'Công đoàn NHVN'],
        ['id' => 3, 'name' => 'Công đoàn NHCSXH'],
        ['id' => 4, 'name' => 'LĐLĐ tỉnh'],
        ['id' => 5, 'name' => 'UBND tỉnh'],
    ];

    return empty($id) ? $items : collect($items)->where('id', $id)->first();
}
//
    static function TYPE_REWARD(string|int $id = null)
    {
        $items = [
            ['id' => 1, 'name' => 'Cá nhân'],
            ['id' => 2, 'name' => 'Tập thể'],
        ];

        return empty($id) ? $items : collect($items)->where('id', $id)->first();
    }

    static function UNIT_CHECK(string|int $id = null)
    {
        $items = [
            ['id' => 1, 'name' => 'Ban đại diện'],
            ['id' => 2, 'name' => 'Toàn diện (tỉnh)'],
            ['id' => 3, 'name' => 'Toàn diện (huyện)'],
            ['id' => 4, 'name' => 'Hội cấp tỉnh'],
            ['id' => 5, 'name' => 'Hội cấp huyện'],
            ['id' => 6, 'name' => 'Hội cấp xã'],
        ];
        return empty($id) ? $items : collect($items)->where('id', $id)->first();
    }

    static function CODES_FOR_JOB_ASSIGNMENT(string|int $id = null)
    {
        $items = [
            ['id' => 'td', 'name' => 'TD'],
            ['id' => 'kt', 'name' => 'KT'],
            ['id' => 'ks', 'name' => 'KS'],
            ['id' => 'hc', 'name' => 'HC'],
            ['id' => 'th', 'name' => 'TH'],
            ['id' => 'ld', 'name' => 'LD'],
            ['id' => 'lx', 'name' => 'LX'],
            ['id' => 'bv', 'name' => 'BV'],
            ['id' => 'tq', 'name' => 'TQ'],
            ['id' => 'tkt', 'name' => 'TKT'],
            ['id' => 'tp', 'name' => 'TP'],
            ['id' => 'pp', 'name' => 'PP'],
            ['id' => 'gdv', 'name' => 'GDV'],
          
        ];

        return empty($id) ? $items : collect($items)->where('id', $id)->first();
    }

    static function WORK_SCHEDULE_POSITIONS()
    {
        return [
            ['id' => 1, 'priority' => 1, 'name' => 'Tổ trưởng'],
            ['id' => 2, 'priority' => 6, 'name' => 'Kiểm soát'],
            ['id' => 3, 'priority' => 7, 'name' => 'Giao dịch viên chính'],
            ['id' => 4, 'priority' => 8, 'name' => 'Giao dịch viên 1'],
            ['id' => 5, 'priority' => 9, 'name' => 'Giao dịch viên 2'],
            ['id' => 6, 'priority' => 10, 'name' => 'Giao dịch viên 3'],
            ['id' => 7, 'priority' => 2, 'name' => 'Lái xe'],
            ['id' => 8, 'priority' => 3, 'name' => 'Bảo vệ'],
            ['id' => 9, 'priority' => 4, 'name' => 'Cán bộ lãnh đạo phiên giao dịch'],
            ['id' => 10, 'priority' => 5, 'name' => 'Cán bộ lãnh đạo giám sát trực tiếp'],
        ];
    }

    static function WORK_SCHEDULE_POSITIONS_FOR_LEVEL_1()
    {
        return [
            ['id' => 1, 'priority' => 1, 'condition' => ['exists_ward_id' => true]],
            ['id' => 2, 'priority' => 2, 'condition' => ['code_for_job_assignment' => 'td']],
            ['id' => 3, 'priority' => 7, 'condition' => ['code_for_job_assignment' => 'kt']],
            ['id' => 4, 'priority' => 8],
            ['id' => 5, 'priority' => 9],
            ['id' => 6, 'priority' => 10],
            ['id' => 7, 'priority' => 3, 'coincides_with_id' => 2],
            ['id' => 8, 'priority' => 4, 'coincides_with_id' => 1, 'condition' => ['code_for_job_assignment' => 'bv']],
            ['id' => 9, 'priority' => 5, 'condition' => ['code_for_job_assignment' => 'ld']],
            ['id' => 10, 'priority' => 6, 'coincides_with_id' => 10],
        ];
    }

    static function WORK_SCHEDULE_POSITIONS_FOR_LEVEL_2_3()
    {
        return [
            ['id' => 1, 'priority' => 1, 'condition' => ['exists_ward_id' => true]],
            ['id' => 2, 'priority' => 6, 'coincides_with_id' => 1],
            ['id' => 3, 'priority' => 7, 'condition' => ['code_for_job_assignment' => 'kt']],
            ['id' => 4, 'priority' => 8, 'condition' => ['code_for_job_assignment' => 'gdv']],
            ['id' => 5, 'priority' => 9, 'condition' => ['code_for_job_assignment' => 'gdv']],
            ['id' => 6, 'priority' => 10, 'condition' => ['code_for_job_assignment' => 'gdv']],
            ['id' => 7, 'priority' => 2, 'condition' => ['code_for_job_assignment' => 'lx']],
            ['id' => 8, 'priority' => 3, 'coincides_with_id' => 7],
            ['id' => 9, 'priority' => 4, 'condition' => ['is_leader' => true]],
            ['id' => 10, 'priority' => 5, 'coincides_with_id' => 10],
        ];
    }
    static function WORK_SCHEDULE_POSITIONS_FOR_LEVEL_3_4()
    {
        return [
            ['id' => 1, 'priority' => 1, 'condition' => ['exists_ward_id' => true]],
            ['id' => 2, 'priority' => 6, 'condition' => ['code_for_job_assignment' => 'td']],
            ['id' => 3, 'priority' => 7, 'condition' => ['code_for_job_assignment' => 'kt']],
            ['id' => 4, 'priority' => 8],
            ['id' => 5, 'priority' => 9],
            ['id' => 6, 'priority' => 10],
            ['id' => 7, 'priority' => 2, 'condition' => ['code_for_job_assignment' => 'lx']],
            ['id' => 8, 'priority' => 3, 'coincides_with_id' => 7],
            ['id' => 9, 'priority' => 4, 'condition' => ['is_leader' => true]],
            ['id' => 10, 'priority' => 5, 'condition' => ['is_leader' => true]],
        ];
    }

    static function DEREC_VB(string|int $id = null)
    {
        $items = [
            ['id' => 'incoming_text', 'name' => 'Văn bản đến'],
            ['id' => 'text_travels', 'name' => 'Văn bản đi'],
            ['id' => 'other', 'name' => 'Khác'],            
        ];
        return empty($id) ? $items : collect($items)->where('id', $id)->first();
    }

    static function TYPE_VB(string|int $id = null)
    {
        $items = [
            ['id' => 'resolution', 'name' => 'Nghị quyết'],
            ['id' => 'decision', 'name' => 'Quyết định'],
            ['id' => 'plan', 'name' => 'Kế hoạch'],
            ['id' => 'other', 'name' => 'Loại VB khác'],            
        ];
        return empty($id) ? $items : collect($items)->where('id', $id)->first();
    }

    static function OTHER_FEES_TYPE(string|int $id = null)
    {
        $items = [
            ['id' => 'tc0', 'name' => 'Đảng phí'],
            ['id' => 'tc1', 'name' => 'Kinh phí được cấp'],
            ['id' => 'tc2', 'name' => 'Thu khác'],
            ['id' => 'tc3', 'name' => 'Báo, Tạp chí'],
            ['id' => 'tc4', 'name' => 'Đại hội'],
            ['id' => 'tc5', 'name' => 'Khen thưởng'],
            ['id' => 'tc6', 'name' => 'Chi hỗ trợ'],
            ['id' => 'tc7', 'name' => 'PC cấp ủy'],
            ['id' => 'tc8', 'name' => 'Đảng phí nộp cấp trên'],
            ['id' => 'tc9', 'name' => 'Chi khác'],            
        ];
        return empty($id) ? $items : collect($items)->where('id', $id)->first();
    }

    static function ROOM_PLACE(string|int $id = null)
    {
        $items = [
            ['id' => 1, 'name' => 'Hội trường nhỏ (Nhà trên)'],
            ['id' => 2, 'name' => 'Hội trường lớn (Nhà dưới)'],           
        ];

        return empty($id) ? $items : collect($items)->where('id', $id)->first();
    }

    static function NAME_FILE_COL($id)
    {
        $items = [
            0 =>  'file',   
            1 =>  'file_second',   
            2 =>  'file_third',   
        ];

        return $items[$id];
    }

    static function DS_DON_VI_CONG_TAC($type = null, $id = null) {
        $items = [
            'S' => [
                ['id' => 1, 'name' => "Tổng hợp"],
                ['id' => 2, 'name' => "Kế toán Ngân quỹ"],
                ['id' => 3, 'name' => "Kế hoạch nghiệp vụ"],
                ['id' => 4, 'name' => "Giám đốc"],
            ],
            'M' => [
                ['id' => 1, 'name' => "Cán bộ"],
                ['id' => 2, 'name' => "Phó phòng"],
                ['id' => 3, 'name' => "Trưởng phòng"],
                ['id' => 4, 'name' => "Phó giám đốc"],
                ['id' => 5, 'name' => "Giám đốc"],
            ],
        ];        

        if (is_null($type)) {
            return $items;
        }

        if (!isset($items[$type])) {
            return [];
        }

        if (is_null($id)) {
            return $items[$type];
        }

        return $items[$type][$id] ?? null;
    }

    static function DS_CHUC_VU($type = null, $id = null) {
        $items = [
            'S' => [
                ['id' => 1, 'name' => "Cán bộ"],
                ['id' => 2, 'name' => "Tổ trưởng/Trưởng phòng"],
                ['id' => 3, 'name' => "Giám đốc"],
                ['id' => 4, 'name' => "Phó giám đốc"],
            ],
            'M' => [
                ['id' => 1, 'name' => "Tin học"],
                ['id' => 2, 'name' => "Hành chính tổ chức"],
                ['id' => 3, 'name' => "Kế toán Ngân quỹ"],
                ['id' => 4, 'name' => "Kế hoạch nghiệp vụ"],
                ['id' => 5, 'name' => "Tổng hợp"],
                ['id' => 6, 'name' => "Giám đốc"],
                ['id' => 7, 'name' => "Kiểm tra KSNB"],
            ],
        ];

        if (is_null($type)) {
            return $items;
        }

        if (!isset($items[$type])) {
            return [];
        }

        if (is_null($id)) {
            return $items[$type];
        }

        return $items[$type][$id] ?? null;
    }

}
