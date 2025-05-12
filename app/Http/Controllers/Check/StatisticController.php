<?php

namespace App\Http\Controllers\Check;

use App\Helpers\SystemDefine;
use App\Http\Controllers\Controller;
use App\Http\Requests\Check\DistrictLevelRequest;
use App\Models\Check;
use App\Models\District;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StatisticController extends Controller
{
    public string $title = 'Thống kê công tác kiểm tra';

    public function __construct()
    {
        $this->featureSlug = SystemDefine::CHECK_STATISTIC_FEATURE;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $dataTableData  = $this->generateDataTableData();
        $checkList      = Check::active()->with(
            'district',
            'ward',
            'ward.district',
            'town',
            'town.ward',
            'town.ward.district',
            'household',
            'household.town',
            'household.town.ward',
            'household.town.ward.district',
            'unit',
        );
        $request->unit_check_id && $checkList = $checkList->whereUnitCheckId($request->unit_check_id);
        $checkList      = $checkList->get();

        return $this->view('check.statistic.index', compact('dataTableData', 'checkList'));
    }

    private function generateDataTableData(): array
    {
        $heads = [
            'STT',
            'Ngày KT',
            'Tên Huyện',
            'Tên Xã',
            'Hội Xã',
            'Tên tổ',
            'Hộ vay',
            'Tên ĐVUT',
            'Dư nợ',
            'Số tổ TK & VV',
            'Số hộ vay',
            'Số dư TK',
            'Đơn vị kiểm tra',
            'Tóm tắt kết quả tra',
        ];

        $config = [
            'order'     => [[0, 'asc']],
            'columns'   => array_merge(
                [['type' => 'num']],
                array_fill(0, 13, null),
            ),
            'language'  => [
                'url'   => asset('vendor/vi.json'),
            ],"scrollX" => true,
        ];

        return ['config' => $config, 'heads' => $heads];
    }
}
