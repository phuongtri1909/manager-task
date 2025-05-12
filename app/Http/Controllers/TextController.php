<?php

namespace App\Http\Controllers;

use App\Helpers\SystemDefine;
use App\Http\Requests\StoreTextRequest;
use App\Http\Requests\UpdateTextRequest;
use App\Models\Comment;
use App\Models\Text;
use App\Models\TextUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class TextController extends Controller
{
    public string $title = 'Text';
    private int $processingStatusId;
    private int $completedStatusId;

    public function __construct()
    {
        $this->processingStatusId   = 1;
        $this->completedStatusId    = 4;
        $this->featureSlug          = SystemDefine::DOCUMENT_FEATURE;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // $textList           = Text::active()->with('document', 'handlers', 'creator');
        

        // if (!is_admin(auth()->user())) {
        //     $textList       = $textList->whereHas('handlers', fn (Builder $query) => $query->where('users.id', auth()->id()));
        // }

        // $textList           = $textList->orderBy('id', 'DESC')->get();
        
        // $textList       = $textList->whereHas('handlers', fn (Builder $query) => $query->where('users.id', auth()->id()));
        // echo '<pre>';
        //     print_r($textList);
        // echo '</pre>';die();

        $dataTableData      = $this->generateDataTableData();

        return $this->view('text.index', compact('dataTableData'));
    }


    public function getTextList(Request $request)
    {
        try {
            $start = $request->input('start');
            $length = $request->input('length');
            $sortColumnIndex = $request->input('order.0.column', 0);
            $sortColumnDirection = $request->input('order.0.dir', 'asc');
            $columns = $request->input('columns');
            $sortColumnName = $columns[$sortColumnIndex]['data'];

            $sortColumnNameMap = [
                'id' => 'texts.id',
                'keywords' => 'texts.keywords',
                'created_at' => 'texts.created_at',
                'status' => 'texts.status',
                'creator_name' => 'users.name',
                'document_alias' => 'document_alias',
            ];

            $sortColumnNameMapped = $sortColumnNameMap[$sortColumnName] ?? $sortColumnNameMap[0];

            $query = Text::with('document:id,alias,documentable_id,documentable_type', 'handlers', 'creator:id,name')
                ->where('users.active', 1)
                ->when($request->input('routeTarget') === 'text.index.no_process', function ($query) {
                    $query->whereHas('handlers', function ($q) {
                        $q->where('users.id', auth()->id())
                            ->where('text_user.status', 1);
                    });
                })
                ->with(['handlers' => function ($q) {
                    $q->where('users.id', auth()->id())
                        ->orderByDesc('text_user.created_at');
                        // ->limit(1);
                }])
                ->join('users', 'users.id', '=', 'texts.created_by')
                ->select('texts.*')
                ->addSelect([
                    'document_alias' => \App\Models\Document::select('alias')
                        ->whereColumn('documentable_id', 'texts.id')
                        ->where('documentable_type', Text::class)
                        ->limit(1),
                ])
                ->orderBy($sortColumnNameMapped, $sortColumnDirection);

            if (!is_admin(auth()->user())) {
                $query = $query->whereHas('handlers', fn (Builder $query) => $query->where('users.id', auth()->id()));
            }

            $recordsTotal = $query->count();

            $search = $request->input('search.value');
            if (!empty($search)) {
                $query = $query->where(function ($q) use ($search) {
                    $q->where('keywords', 'like', "%$search%")
                        ->orWhereHas('document', function ($q) use ($search) {
                            $q->where('alias', 'like', "%$search%");
                        })
                        ->orWhereHas('creator', function ($q) use ($search) {
                            $q->where('name', 'like', "%$search%");
                        });
                });
            }
            
            $recordsFilterd = $query->count();

            $queryData = $query->offset($start)
                ->limit($length)
                ->get();

            $data = collect();

            $queryData->each(function ($row) use ($data) {
                $data->push([
                    'id' => $row->id,
                    'keywords' => $row->keywords,
                    'document_alias' => $this->view('text.partials.datatable_document_alias', [
                        'textItem' => $row,
                    ])->render(),
                    'status' =>  $this->view('text.partials.datatable_status', [
                        'status' => $row->status,
                    ])->render(),
                    'creator_name' => $row->creator->name ?? '',
                    'created_at' => $row->created_at->format('d/m/Y'),
                    'actions' => $this->view('text.partials.datatable_actions', [
                        'id' => $row->id,
                    ])->render()
                ]);
            });

            return response()->json([
                'draw' => intval( $request->input('draw', 1)),
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFilterd,
                'data' => $data->values(),
            ]);
        } catch (\Exception $e) {
            // dd($e->getMessage());
            return response()->json([
                'draw' => $request->input('draw'),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);
        }
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // tạo mới chỉ có văn thư huyện || tnh nên không cần quan tâm chiều văn bản vì danh sách lấy ra là như nhau
        $userList = $this->getHandlerListUpward()->groupBy('department.id');
        return $this->view('text.form', compact('userList'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTextRequest $request)
    {
        $position                   = auth()->user()->position;
        $isClericalProvincialLevel  = ($position->is_clerical ?? null) && ($position->is_provincial_level ?? null);

        $text               = new Text;
        $text->fill($request->validated());
        $text->direction    = $isClericalProvincialLevel ? 'down' : 'up';
        $text->save();

        $text->handlers()->attach(array_fill_keys($request->handler_ids, [
            'is_read'       => false,
            'status'        => $text->status,
            'direction'     => $text->direction,
            'created_by'    => auth()->id(),
        ]));

        $text->document()->save(store_file(
            'public/documents/',
            $request->file('file'),
            $request->alias,
            $request->description
        ));

        flash_message(SystemDefine::CREATE_SUCCESS_MESSAGE);
        return redirect()->route('text.show', $text->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Text $text)
    {
        $userList               = $text->direction === 'up'
            ? $this->getHandlerListUpward()->groupBy('department.id')
            : $this->getHandlerListDownward()->groupBy('department.id');

        
        $dataTableConfig        = $this->generateSharingTable();
        $isHandler              = $text->handlers()
            ->where('users.id', auth()->id())            
            ->wherePivot('status', $this->processingStatusId)
            ->first();

        // set state is read
        $textUser               = TextUser::whereUserId(auth()->id())->whereTextId($text->id)->orderBy('created_at', 'DESC')->first();
        if ($textUser) {
            $textUser->is_read  = true;
            $textUser->save();
        }
       
        $departmentId = auth()->user()->department_id;
        if ($departmentId == 2) {
             $departmentId = true;
            
        }
//print_r($departmentId);die();
        return $this->view('text.details', compact(
            'userList',
            'text',
            'dataTableConfig',
            'isHandler',
            'departmentId',
        ));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Text $text)
    {
        $userList           = $text->direction === 'up'
            ? $this->getHandlerListUpward()->groupBy('department.id')
            : $this->getHandlerListDownward()->groupBy('department.id');

        $text->handler_ids  = $text->handlers->pluck('id')->toArray();
        $text->alias        = $text->document->alias;
       // $text->description  = $text->document->description;

        return $this->view('text.form', compact('userList', 'text'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTextRequest $request, Text $text)
    {
        $position                   = auth()->user()->position;
        $isClericalProvincialLevel  = ($position->is_clerical ?? null) && ($position->is_provincial_level ?? null);

        $text->fill($request->validated());
        $text->direction    = $isClericalProvincialLevel ? 'down' : 'up';
        $text->save();

        if ($request->file('file')) {
            $text->document()->delete();

            $text->document()->save(store_file(
                'public/documents/',
                $request->file('file'),
                $request->alias,
                $request->description
            ));
        }

        flash_message(SystemDefine::CREATE_SUCCESS_MESSAGE);
        return redirect()->route('text.show', $text->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Text $text)
    {
        $text->delete();

        flash_message(SystemDefine::DELETE_SUCCESS_MESSAGE);
        return redirect()->route('text.index');
    }

    public function share(Request $request, Text $text)
    {
        $request->validate(['handler_ids' => 'required|array', 'handler_ids.*' => 'min:1|exists:users,id']);

        // the handler hasn't finished the task yet
        $handlingAndNotProcessed = $text->handlers
            ->filter(fn (User $u) => $u->pivot->status == $this->processingStatusId && in_array($u->id, $request->handler_ids));
        if ($handlingAndNotProcessed->isNotEmpty()) {
            $names = $handlingAndNotProcessed->pluck('name')->join(', ');
            flash_message("{$names} hiện tại chưa hoàn thành công việc này.", 'error');
            return redirect()->route('text.show', $text->id);
        }

        $this->processed($text);

        $text->refresh();

        $text->handlers()->attach(array_fill_keys($request->handler_ids, [
            'is_read'       => false,
            'status'        => 1,
            'direction'     => $text->direction,
            'created_by'    => auth()->id(),
        ]));

        flash_message(SystemDefine::SHARE_SUCCESS_MESSAGE);
        return redirect()->route('text.show', $text->id);
    }

    public function processed(Text $text)
    {
        $position                   = auth()->user()->position;
        $isClericalProvincialLevel  = ($position->is_clerical ?? null) && ($position->is_provincial_level ?? null);
        $text->direction            = $isClericalProvincialLevel ? 'down' : $text->direction;
        $text->save();

        $textUser               = TextUser::whereUserId(auth()->id())->whereTextId($text->id)->orderBy('created_at', 'DESC')->first();
        $textUser->direction    = $isClericalProvincialLevel ? 'down' : $textUser->direction;
        $textUser->status       = $this->completedStatusId;
        $textUser->updated_at   = now();
        $textUser->updated_by   = auth()->id();
        $textUser->save();

        flash_message(SystemDefine::COMPLETE_SUCCESS_MESSAGE);
        return redirect()->route('text.show', $text->id);
    }

    public function comment(Request $request, Text $text)
    {
        $request->validate(['content' => 'required|string|max:10240|min:3']);

        $text->comments()->create([
            'content' => $request->content,
        ]);

        flash_message(SystemDefine::COMPLETE_SUCCESS_MESSAGE);
        return redirect()->route('text.show', $text->id);
    }

    public function destroyComment(Text $text, Comment $comment)
    {
        $comment->delete();

        flash_message(SystemDefine::DELETE_SUCCESS_MESSAGE);
        return redirect()->route('text.show', $text->id);
    }

    private function generateDataTableData(): array
    {
        $heads = [
            'STT',
            'Số/Ký hiệu',
            'Tên văn bản',
            'Trạng thái',
           // 'Người xử lý',
            'Người tạo',
            'Ngày tạo',
            [
                'label' => __('Hành động'),
                'no-export' => true, 'width' => 10
            ]
        ];

        $config = [
            'order'     => [[0, 'DESC']],
            'columns'   => array_merge(
                [['type' => 'num']],
                array_fill(0, 5, null),
                [['orderable' => false]]
            ),
            'language'  => [
                'url'   => asset('vendor/vi.json'),
            ],
            "scrollX"   => true,
        ];

        // if (request()->route()->getName() === 'text.index') {
            $config = array_merge($config, [
                'processing' => true,
                'serverSide' => true,
                'ajax' => [
                    'url' => route('text.get_text_list') . '?routeTarget=' . request()->route()->getName(),
                    'type' => 'POST',
                ],
            ]);
    
            $config['columns'] = [
                ['data' => 'id', 'name' => 'id', 'type' => 'num'],
                ['data' => 'keywords', 'name' => 'keywords'],
                ['data' => 'document_alias', 'name' => 'document_alias'],
                ['data' => 'status', 'name' => 'status'],
                ['data' => 'creator_name', 'name' => 'creator_name'],
                ['data' => 'created_at', 'name' => 'created_at'],
                ['data' => 'actions', 'name' => 'actions', 'orderable' => false],
            ];
        // }

        return ['config' => $config, 'heads' => $heads];
    }

    private function generateSharingTable(): array
    {
        $heads = [
            'STT',
            'Người xử lý',
            'Trạng thái',
            'Người bàn giao',
            'Thời gian bàn giao',
            'Thời gian cập nhật',
        ];

        $config = [
            'order'     => [[0, 'asc']],
            'columns'   => array_fill(0, 6, null),
            'language'  => [
                'url'   => asset('vendor/vi.json'),
            ],
            //"scrollX"   => true,
        ];

        return ['config' => $config, 'heads' => $heads];
    }

    private function getHandlerListDownward(): Collection
    {
        $position = auth()->user()->position;
        $department = auth()->user()->department;
        $provinceManagementLevel = 3;
      // echo $department;die();
        if (is_admin(auth()->user())) {
            return User::active()->with('department', 'position')->get()->except(auth()->id());
        }

        // vn thư
        if ($position->is_clerical) {          
            // văn thư cấp tỉnh: users là quản lý cấp tỉnh (level = 3) và là leader vì văn thư có thể năm trong level này nhưng văn thư ko là leader
            if ($position->is_provincial_level) {
                return User::active()
                    ->with('department', 'position')
                    ->whereHas('department', fn (Builder $query) => $query->where('level', $provinceManagementLevel))
                    ->whereHas('position', fn (Builder $query) => $query->where('is_leader', true))
                    ->get()->except(auth()->id());
            }

            // văn thư cấp huyện: users là leader cùng phòng ban
           $aareturn = User::active()
                ->with('department', 'position')
                ->whereHas('department', fn (Builder $query) => $query->where('id', $department->id))
                ->whereHas('position', fn (Builder $query) => $query->where('is_leader', true))
                ->get()->except(auth()->id());                
        }
      
        // lãnh đạo
        if ($position->is_leader) {            
            // quản lý cấp tỉnh: users là leader
            if ($position->is_provincial_level && $department->level === $provinceManagementLevel) {
                return User::active()
                    ->with('department', 'position')
                    ->whereHas('position', fn (Builder $query) => $query->where('is_leader', true))
                    ->get()->except(auth()->id());
            }
            // leader trong phòng và PGD: users cùng phòng ban
            return User::active()
                ->with('department', 'position')
                ->whereHas('department', fn (Builder $query) => $query->where('id', $department->id))
                ->get()->except(auth()->id());
        }

        return new Collection;
    }

    private function getHandlerListUpward(): Collection
    {
        $position = auth()->user()->position;
        $department = auth()->user()->department;
        $provinceManagementLevel = 3;
//echo $position;die();
        if (is_admin(auth()->user())) {
            return User::active()->with('department', 'position')->get()->except(auth()->id());
        }

        // văn thư (giống với hướng lên)
        if ($position->is_clerical) {
        // văn thư cấp tỉnh: users là quản lý cấp tỉnh (level = 3) và là leader vì văn thư có thể năm trong level này nhưng văn thư ko là leader
            if ($position->is_provincial_level) {
                return User::active()
                    ->with('department', 'position')
                    ->whereHas('department', fn (Builder $query) => $query->where('level', $provinceManagementLevel))
                    ->whereHas('position', fn (Builder $query) => $query->where('is_leader', true))
                    ->get()->except(auth()->id());
            }

        // văn thư cấp huyện: users là leader cùng phòng ban
            return User::active()
                ->with('department', 'position')
                ->whereHas('department', fn (Builder $query) => $query->where('id', $department->id))
                ->whereHas('position', fn (Builder $query) => $query->where('is_leader', true))
                ->get()->except(auth()->id());
        }

        // lãnh đạo
        if ($position->is_leader) {
            // quản lý cấp tỉnh: users là leader (giống với hướng lên)
            if ($position->is_provincial_level && $department->level === $provinceManagementLevel) {
                return User::active()
                    ->with('department', 'position')
                    ->whereHas('position', fn (Builder $query) => $query->where('is_leader', true))
                    ->get()->except(auth()->id());
            }
        // leader trong phòng và PGD: user là văn thư tỉnh
            // return User::active()
            //     ->with('department', 'position')
            //     ->whereHas('position', fn (Builder $query) => $query->where('is_clerical', true)->where('is_provincial_level', true))
            //     ->get()->except(auth()->id());

            return User::active()
            ->with('department', 'position')
            ->whereHas('department', fn (Builder $query) => $query->where('id', $department->id))
            ->OrwhereHas('position', fn (Builder $query) => $query->where('is_clerical', true)->where('is_provincial_level', true))
            ->get()->except(auth()->id());
        }

        return new Collection;
    }
}
