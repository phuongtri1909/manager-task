<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use App\Models\TaskUser;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Models\TaskDepartment;
use App\Models\TaskUserAttachment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Traits\RoleBasedRedirects;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    use RoleBasedRedirects;
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource for admin.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        return $this->redirectBasedOnRole($user);
    }

    public function indexAdmin(Request $request)
    {
        $user = Auth::user();

        // Admin can see all tasks
        $tasks = Task::with('creator', 'creator.role', 'departments', 'users');

        // Apply filters
        if ($request->filled('title')) {
            $tasks->where('title', 'like', '%' . $request->title . '%');
        }

        if ($request->filled('department_id')) {
            $departmentId = $request->department_id;
            $tasks->whereHas('departments', function ($q) use ($departmentId) {
                $q->where('departments.id', $departmentId);
            });
        }

        if ($request->filled('creator_role')) {
            $roleId = $request->creator_role;
            $tasks->whereHas('creator', function ($q) use ($roleId) {
                $q->where('role_id', $roleId);
            });
        }

        if ($request->filled('overdue')) {
            $now = now();
            if ($request->overdue == '1') {
                $tasks->where('deadline', '<', $now);
            } else {
                $tasks->where(function ($query) use ($now) {
                    $query->where('deadline', '>=', $now);
                });
            }
        }

        // Get paginated results
        $tasks = $tasks->latest()->paginate(10);

        $departments = Department::all();
        $roles = Role::orderBy('level', 'desc')->get();

        return view('manager_task.tasks.index', compact('tasks', 'departments', 'roles'));
    }

    /**
     * Display tasks for managers to monitor (managed view)
     */
    public function managedTasks(Request $request)
    {
        $user = Auth::user();

        // Check if user has appropriate role
        if (!($user->isDirector() || $user->isDeputyDirector() || $user->isDepartmentHead() || $user->isDeputyDepartmentHead())) {
            return $this->redirectBasedOnRole($user, 'Bạn không có quyền xem mục này!');
        }

        $query = Task::query();
        $departments = collect();
        $roles = collect();

        // For Director and Deputy Director - view tasks from all managers
        if ($user->isDirector() || $user->isDeputyDirector()) {
            // Get management role IDs (Director, Deputy Director, Department Head, Deputy Department Head)
            $managerRoleIds = Role::whereIn('slug', [
                'director',
                'deputy-director',
                'department-head',
                'deputy-department-head'
            ])->pluck('id');

            // Add role filter options for management roles only
            $roles = Role::whereIn('slug', [
                'director',
                'deputy-director',
                'department-head',
                'deputy-department-head'
            ])->orderBy('level', 'desc')->get();

            // Get all departments for filtering
            $departments = Department::orderBy('name')->get();

            // Base query - tasks created by managers
            $query->whereIn('created_by', function ($q) use ($managerRoleIds) {
                $q->select('id')->from('users')->whereIn('role_id', $managerRoleIds);
            });

            // Apply creator role filter
            if ($request->filled('creator_role')) {
                $roleId = $request->creator_role;
                $query->whereHas('creator', function ($q) use ($roleId) {
                    $q->where('role_id', $roleId);
                });
            }

            // Apply department filter
            if ($request->filled('department_id')) {
                $departmentId = $request->department_id;
                $query->where(function ($q) use ($departmentId) {
                    $q->whereHas('departments', function ($subquery) use ($departmentId) {
                        $subquery->where('departments.id', $departmentId);
                    })
                        ->orWhereHas('users', function ($subquery) use ($departmentId) {
                            $subquery->where('users.department_id', $departmentId);
                        });
                });
            }
        }
        // For Department Heads and Deputy Department Heads - only their own department
        else if ($user->isDepartmentHead() || $user->isDeputyDepartmentHead()) {
            $departmentId = $user->department_id;

            // Only their department for filtering
            $departments = Department::where('id', $departmentId)->get();

            // Role options for filtering - only department head and deputy
            $roles = Role::whereIn('slug', ['department-head', 'deputy-department-head'])
                ->orderBy('level', 'desc')
                ->get();

            // Get tasks where:
            // 1. Tasks assigned to the department OR
            // 2. Tasks assigned to users from the department
            $query->where(function ($q) use ($departmentId) {
                $q->whereHas('departments', function ($subquery) use ($departmentId) {
                    $subquery->where('departments.id', $departmentId);
                })
                    ->orWhereHas('users', function ($subquery) use ($departmentId) {
                        $subquery->where('users.department_id', $departmentId);
                    });
            });

            // Apply creator role filter - only within their department
            if ($request->filled('creator_role')) {
                $roleId = $request->creator_role;
                $query->whereHas('creator', function ($q) use ($roleId, $departmentId) {
                    $q->where('role_id', $roleId)
                        ->where('department_id', $departmentId);
                });
            }
        }

        // Apply common filters
        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        if ($request->filled('overdue')) {
            $now = now();
            if ($request->overdue == '1') {
                $query->where('deadline', '<', $now);
            } else {
                $query->where(function ($q) use ($now) {
                    $q->where('deadline', '>=', $now);
                });
            }
        }

        $tasks = $query->with(['creator', 'creator.role', 'departments', 'users', 'attachments'])
            ->latest()
            ->paginate(10);

        return view('manager_task.tasks.managed', compact('tasks', 'departments', 'roles'));
    }

    /**
     * Display tasks assigned by the current user (tasks they created)
     */
    public function assignedTasks(Request $request)
    {
        $user = Auth::user();

        if (!$user->canAssignTasks()) {
            return redirect()->route('tasks.received')
                ->with('error', 'Bạn không có quyền xem mục này!');
        }

        $query = Task::where('created_by', $user->id);

        if ($user->isDirector() || $user->isDeputyDirector()) {
            $departments = Department::orderBy('name')->get();
        } elseif ($user->isDepartmentHead() || $user->isDeputyDepartmentHead()) {
            $departments = Department::where('id', $user->department_id)->get();
        } else {
            $departments = collect();
        }

        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        if ($request->filled('department_id')) {
            $departmentId = $request->department_id;
            $query->whereHas('departments', function ($q) use ($departmentId) {
                $q->where('departments.id', $departmentId);
            });
        }

        if ($request->filled('overdue')) {
            $now = now();
            if ($request->overdue == '1') {
                $query->where('deadline', '<', $now)
                    ->where(function ($q) {
                        $q->whereDoesntHave('users', function ($subq) {
                            $subq->where('task_user.status', 'completed');
                        })
                            ->orWhereHas('users', function ($subq) {
                                $subq->where('task_user.status', '!=', 'completed');
                            });
                    });
            } else {
                $query->where(function ($q) use ($now) {
                    $q->where('deadline', '>=', $now)
                        ->orWhereHas('users', function ($subq) {
                            $subq->where('task_user.status', 'completed');
                        });
                });
            }
        }

        $tasks = $query->with(['creator', 'departments', 'users', 'attachments'])
            ->latest()
            ->paginate(10);

        return view('manager_task.tasks.assigned', compact('tasks', 'departments'));
    }

    /**
     * Display tasks assigned to the current user (received tasks)
     */
    public function receivedTasks(Request $request)
    {
        $user = Auth::user();
        $query = Task::query();

        // 1. Tasks where the current user is directly assigned as an individual
        $query->whereHas('users', function ($q) use ($user) {
            $q->where('users.id', $user->id);
        });

        // 2. For department heads and deputy heads, we need to respect the include_department_heads flag
        if ($user->isDepartmentHead() || $user->isDeputyDepartmentHead()) {
            $query->orWhere(function ($q) use ($user) {
                // Only include department tasks where:
                // - The task is assigned to their department AND
                // - The include_department_heads flag is TRUE
                $q->whereHas('departments', function ($subq) use ($user) {
                    $subq->where('departments.id', $user->department_id)
                        ->where('task_departments.include_department_heads', true);
                });
            });
        }
        // 3. For non-department heads with a department, include department tasks
        elseif ($user->department_id && !$user->isStaff()) {
            $query->orWhereHas('departments', function ($q) use ($user) {
                $q->where('departments.id', $user->department_id);
            });
        }

        // Apply filters
        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        // Filter by task status in the pivot table
        if ($request->filled('status')) {
            $status = $request->status;
            $query->whereHas('users', function ($q) use ($user, $status) {
                $q->where('users.id', $user->id)
                    ->where('task_user.status', $status);
            });
        }

        // Filter by task type (department or individual)
        if ($request->filled('task_type')) {
            $type = $request->task_type;
            if ($type === 'department') {
                $query->where('for_departments', true);
            } else if ($type === 'individual') {
                $query->where('for_departments', false);
            }
        }

        // Filter by overdue status
        if ($request->filled('overdue')) {
            $now = now();
            if ($request->overdue == '1') {
                $query->where('deadline', '<', $now)
                    ->whereHas('users', function ($q) use ($user) {
                        $q->where('users.id', $user->id)
                            ->whereNotIn('task_user.status', ['completed', 'approved']);
                    });
            } else {
                $query->where(function ($q) use ($now, $user) {
                    $q->where('deadline', '>=', $now)
                        ->orWhereHas('users', function ($subq) use ($user) {
                            $subq->where('users.id', $user->id)
                                ->whereIn('task_user.status', ['completed', 'approved']);
                        });
                });
            }
        }

        // Get tasks with eager loading
        $tasks = $query->with([
            'creator',
            'departments',
            'attachments',
            'users' => function ($q) use ($user) {
                $q->where('users.id', $user->id);
            }
        ])
            ->latest()
            ->paginate(10);

        // Status options for filter dropdown
        $statusOptions = [
            'sending' => 'Chưa xem',
            'viewed' => 'Đã xem',
            'in_progress' => 'Đang thực hiện',
            'completed' => 'Hoàn thành',
            'approved' => 'Đã phê duyệt',
            'approval_rejected' => 'Từ chối kết quả',
            'rejected' => 'Đã hủy'
        ];

        return view('manager_task.tasks.received', compact('tasks', 'statusOptions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();

        // Admin cannot create tasks, only management roles with specific permission can
        if ($user->isAdmin()) {
            return redirect()->route('tasks.index')->with('error', 'Quản trị viên không có quyền tạo công việc!');
        }

        // Only users with can_assign_task permission can create tasks
        if (!$user->canAssignTasks()) {
            return redirect()->route('tasks.index')->with('error', 'Bạn không có quyền tạo công việc! Vui lòng liên hệ admin để được cấp quyền.');
        }

        // Check if user has correct role
        if (!($user->isDirector() || $user->isDeputyDirector() || $user->isDepartmentHead() || $user->isDeputyDepartmentHead())) {
            return redirect()->route('tasks.index')->with('error', 'Chỉ Giám đốc, Phó giám đốc, Trưởng phòng và Phó trưởng phòng có quyền tạo công việc!');
        }

        $departments = $this->getAvailableDepartments($user);
        $users = $this->getAvailableUsers($user);

        return view('manager_task.tasks.create', compact('departments', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {


        $user = Auth::user();

        // Kiểm tra quyền cơ bản
        if ($user->isAdmin()) {
            return redirect()->route('tasks.index')->with('error', 'Quản trị viên không có quyền tạo công việc!')->withInput();
        }

        if (!$user->canAssignTasks()) {
            return redirect()->route('tasks.index')->with('error', 'Bạn không có quyền tạo công việc! Vui lòng liên hệ admin để được cấp quyền.')->withInput();
        }

        // Kiểm tra vai trò và phạm vi quyền hạn
        if (!($user->isDirector() || $user->isDeputyDirector() || $user->isDepartmentHead() || $user->isDeputyDepartmentHead())) {
            return redirect()->route('tasks.index')->with('error', 'Chỉ Giám đốc, Phó giám đốc, Trưởng phòng và Phó trưởng phòng có quyền tạo công việc!')->withInput();
        }

        // Kiểm tra quyền giao việc cho phòng ban
        if ($user->isDeputyDepartmentHead() && $request->has('for_departments')) {
            return redirect()->back()->with('error', 'Phó phòng chỉ có thể giao việc cho các cá nhân trong phòng, không thể giao cho phòng ban.')->withInput();
        }

        // Kiểm tra quyền giao việc cho người dùng
        if ($request->has('users')) {
            $selectedUsers = User::whereIn('id', $request->users)->get();

            foreach ($selectedUsers as $selectedUser) {
                // Giám đốc và Phó giám đốc có thể giao việc cho bất kỳ ai
                if ($user->isDirector() || $user->isDeputyDirector()) {
                    continue;
                }

                // Trưởng phòng chỉ có thể giao việc trong phòng của mình
                if ($user->isDepartmentHead() && $selectedUser->department_id !== $user->department_id) {
                    return redirect()->back()->with('error', 'Trưởng phòng chỉ có thể giao việc cho nhân viên trong phòng của mình.')->withInput();
                }

                // Phó phòng chỉ có thể giao việc cho nhân viên (không phải trưởng/phó phòng) trong phòng
                if ($user->isDeputyDepartmentHead()) {
                    if ($selectedUser->department_id !== $user->department_id) {
                        return redirect()->back()->with('error', 'Phó phòng chỉ có thể giao việc cho nhân viên trong phòng của mình.')->withInput();
                    }

                    if (!$selectedUser->isStaff()) {
                        return redirect()->back()->with('error', 'Phó phòng chỉ có thể giao việc cho nhân viên, không thể giao cho trưởng/phó phòng.');
                    }
                }

                // Kiểm tra cấp bậc để tránh giao việc cho cấp trên
                if ($selectedUser->role->level > $user->role->level) {
                    return redirect()->back()->with('error', 'Không thể giao việc cho người có chức vụ cao hơn.')->withInput();
                }
            }
        }

        // Kiểm tra quyền giao việc cho phòng ban
        if ($request->has('departments')) {
            $departmentIds = $request->departments;

            // Trưởng phòng chỉ có thể giao việc cho phòng của mình
            if ($user->isDepartmentHead() && (count($departmentIds) > 1 || !in_array($user->department_id, $departmentIds))) {
                return redirect()->back()->with('error', 'Trưởng phòng chỉ có thể giao việc cho phòng ban của mình.')->withInput();
            }

            // Phó trưởng phòng không thể giao việc cho phòng ban
            if ($user->isDeputyDepartmentHead()) {
                return redirect()->back()->with('error', 'Phó phòng không có quyền giao việc cho phòng ban.')->withInput();
            }
        }

        // Validate dữ liệu đầu vào
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'required|date|after:now',
            'departments' => $request->boolean('for_departments') ? 'required|array' : 'nullable|array',
            'users' => !$request->boolean('for_departments') ? 'required|array' : 'nullable|array',
            'include_department_heads' => 'boolean|nullable',
            'for_departments' => 'boolean|nullable',
            'attachments' => 'nullable|array',
            'attachments.*' => 'nullable|file|mimes:doc,docx,pdf,xlsx,xls,mp4',
        ], [
            'title.required' => 'Tiêu đề là bắt buộc',
            'title.string' => 'Tiêu đề phải là chuỗi',
            'title.max' => 'Tiêu đề không được vượt quá 255 ký tự',
            'deadline.required' => 'Ngày hạn là bắt buộc',
            'deadline.date' => 'Ngày hạn phải là ngày',
            'deadline.after' => 'Ngày hạn phải sau ngày hiện tại',
            'departments.required_without' => 'Phải chọn ít nhất một phòng ban hoặc người dùng',
            'departments.required' => 'Phải chọn ít nhất một phòng ban',
            'users.required' => 'Phải chọn ít nhất một người thực hiện',
            'users.required_without' => 'Phải chọn ít nhất một phòng ban hoặc người dùng',
            'include_department_heads.boolean' => 'Trường bao gồm trưởng/phó phòng phải là boolean',
            'for_departments.boolean' => 'Cột phòng là bắt buộc',
            'attachments.*.mimes' => 'Tệp đính kèm phải có định dạng: doc, docx, pdf, xlsx, xls, mp4',
            'attachments.*.file' => 'Tệp đính kèm phải là tệp',
        ]);


        // Xử lý lưu dữ liệu trong transaction
        DB::beginTransaction();

        try {
            // Tạo task với thông tin người tạo và cập nhật
            $task = Task::create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'deadline' => $validated['deadline'],
                'created_by' => $user->id,
                'updated_by' => $user->id,
                'for_departments' => $request->boolean('for_departments', false),
                'include_department_heads' => $request->boolean('include_department_heads', false),
            ]);

            // If task is for departments
            if ($request->boolean('for_departments')) {
                if ($request->has('departments') && is_array($request->departments)) {
                    // Create TaskDepartment records
                    foreach ($request->departments as $departmentId) {
                        $includeDeptHeads = $request->boolean('include_department_heads', false);

                        // Create task_department record
                        $taskDepartment = new TaskDepartment([
                            'department_id' => $departmentId,
                            'include_department_heads' => $includeDeptHeads,
                        ]);

                        $task->taskDepartments()->save($taskDepartment);

                        // Get all users from this department
                        $departmentUsers = User::where('department_id', $departmentId)->get();

                        foreach ($departmentUsers as $deptUser) {
                            if (
                                !$includeDeptHeads &&
                                ($deptUser->isDepartmentHead() || $deptUser->isDeputyDepartmentHead())
                            ) {
                                continue;
                            }

                            // Create task_user record
                            $task->users()->attach($deptUser->id, [
                                'status' => TaskUser::STATUS_SENDING,
                                'assigned_by' => $user->id,
                                'assigned_at' => now(),
                            ]);
                        }
                    }
                }
            }
            // If task is for specific users
            else if ($request->has('users') && is_array($request->users)) {
                foreach ($request->users as $userId) {
                    // Create task_user record
                    $task->users()->attach($userId, [
                        'status' => TaskUser::STATUS_SENDING,
                        'assigned_by' => $user->id,
                        'assigned_at' => now(),
                    ]);
                }
            }

            // Xử lý file đính kèm
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->storeAs('task_attachments', $filename, 'public');

                    $task->attachments()->create([
                        'filename' => $filename,
                        'original_filename' => $file->getClientOriginalName(),
                        'file_path' => 'storage/task_attachments/' . $filename,
                        'file_type' => $file->getClientOriginalExtension(),
                        'file_size' => $file->getSize(),
                        'uploaded_by' => $user->id
                    ]);
                }
            }

            DB::commit();



            return redirect()->route('tasks.index')->with('success', 'Công việc đã được tạo thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Đã xảy ra lỗi: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */

    public function show(Task $task)
    {
        $user = Auth::user();
        $userDepartmentId = $user->department_id;

        // Xác định quyền của người dùng
        $canSeeAllDetails = $user->isAdmin() || $user->isDirector() || $user->isDeputyDirector();

        $isManager = $user->isDepartmentHead() || $user->isDeputyDepartmentHead();

        $isStaff = $user->isStaff();

        $isTaskCreator = $task->created_by === $user->id;

        $canEdit = $user->isAdmin() || $isTaskCreator;

        // Kiểm tra quyền truy cập
        $hasAccess = false;

        if ($canSeeAllDetails) {
            $hasAccess = true;
        } elseif ($isTaskCreator) {
            $hasAccess = true;
        } elseif ($task->users()->where('users.id', $user->id)->exists()) {
            $hasAccess = true;
        } elseif ($isManager && $task->departments()->where('departments.id', $userDepartmentId)->exists()) {
            $hasAccess = true;
        }

        // If no access, redirect with error
        if (!$hasAccess) {
            return redirect()->back()
                ->with('error', 'Bạn không có quyền xem công việc này!');
        }

        // Mark as viewed if the current user is assigned to this task
        if (
            $task->users()->where('users.id', $user->id)->exists() &&
            is_null($task->users()->where('users.id', $user->id)->first()->pivot->viewed_at)
        ) {
            $task->users()->updateExistingPivot($user->id, [
                'viewed_at' => now(),
                'status' => TaskUser::STATUS_VIEWED,
            ]);
        }

        $canUpdateStatus = $task->users()->where('users.id', $user->id)->exists();
        $taskUserAttachments = [];
        $currentCompletionAttempts = [];
        // Load relationships based on permissions
        if ($canSeeAllDetails || $isTaskCreator) {
            // Load all data for admins, directors, and task creators
            $task->load([
                'creator',
                'departments',
                'users',
                'users.department',
                'users.role',
                'attachments',
                'extensions'
            ]);

            foreach ($task->users as $taskUser) {
                $pivotId = $taskUser->pivot->task_id;
                $currentAttempt = $taskUser->pivot->completion_attempt ?? 0;
                $currentCompletionAttempts[$pivotId] = $currentAttempt;

                // Lấy tất cả file đính kèm của task_user này
                $attachments = TaskUserAttachment::where('task_user_id', $pivotId)
                    ->where('is_active', true)
                    ->orderBy('created_at', 'desc')
                    ->get();
                if ($attachments->count() > 0) {
                    $taskUserAttachments[$pivotId] = $attachments;
                }
            }
        } elseif ($isManager) {
            // Department heads only see users from their department
            $task->load([
                'creator',
                'departments',
                'attachments',
                'users' => function ($query) use ($userDepartmentId) {
                    $query->where('users.department_id', $userDepartmentId);
                },
                'users.department',
                'users.role',
                'extensions' => function ($query) use ($userDepartmentId, $user) {
                    $query->where(function ($q) use ($userDepartmentId, $user) {
                        $q->whereHas('user', function ($userQuery) use ($userDepartmentId) {
                            $userQuery->where('department_id', $userDepartmentId);
                        })
                            ->orWhere('user_id', $user->id);
                    });
                }
            ]);

            foreach ($task->users as $taskUser) {
                if ($taskUser->department_id == $userDepartmentId) {
                    $pivotId = $taskUser->pivot->task_id;


                    $currentAttempt = $taskUser->pivot->completion_attempt ?? 0;
                    $currentCompletionAttempts[$pivotId] = $currentAttempt;

                    $attachments = TaskUserAttachment::where('task_user_id', $pivotId)
                        ->where('is_active', true)
                        ->orderBy('created_at', 'desc')
                        ->get();

                    if ($attachments->count() > 0) {
                        $taskUserAttachments[$pivotId] = $attachments;
                    }
                }
            }
        } else {
            // Staff only see their own data
            $task->load([
                'creator',
                'departments',
                'attachments',
                'users' => function ($query) use ($user) {
                    $query->where('users.id', $user->id);
                },
                'users.department',
                'users.role',
                'extensions' => function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                }
            ]);

            if ($task->users->count() > 0) {
                foreach ($task->users as $taskUser) {
                    if ($taskUser->id == $user->id) {
                        $pivotId = $taskUser->pivot->task_id;
                        $currentAttempt = $taskUser->pivot->completion_attempt ?? 0;
                        $currentCompletionAttempts[$pivotId] = $currentAttempt;

                        $attachments = TaskUserAttachment::where('task_user_id', $pivotId)
                            ->where('is_active', true)
                            ->orderBy('created_at', 'desc')
                            ->get();



                        if ($attachments->count() > 0) {
                            $taskUserAttachments[$pivotId] = $attachments;
                        }

                        break;
                    }
                }
            }
        }

        return view('manager_task.tasks.show', compact(
            'task',
            'canUpdateStatus',
            'canSeeAllDetails',
            'isManager',
            'isStaff',
            'userDepartmentId',
            'canEdit',
            'taskUserAttachments',
            'currentCompletionAttempts'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        $user = Auth::user();

        // Only admin can edit tasks
        if (!$user->isAdmin()) {
            return redirect()->route('tasks.index')->with('error', 'Chỉ quản trị viên mới có quyền chỉnh sửa công việc!');
        }

        $departments = $this->getAvailableDepartments($user);
        $users = $this->getAvailableUsers($user);
        $selectedDepartments = $task->departments->pluck('id')->toArray();
        $selectedUsers = $task->users->pluck('id')->toArray();

        return view('manager_task.tasks.edit', compact('task', 'departments', 'users', 'selectedDepartments', 'selectedUsers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        $user = Auth::user();

        // Only admin can edit tasks
        if (!$user->isAdmin()) {
            return redirect()->route('tasks.show', $task)->with('error', 'Bạn không có quyền chỉnh sửa công việc!');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'required|date',
            'departments' => 'required_without:users|array',
            'users' => 'required_without:departments|array',
            'include_department_heads' => 'boolean',
            'for_departments' => 'boolean',
            'attachments' => 'nullable|array',
            'attachments.*' => 'nullable|file|mimes:doc,docx,pdf,xlsx,xls,mp4',
        ], [
            'title.required' => 'Tiêu đề là bắt buộc',
            'title.string' => 'Tiêu đề phải là chuỗi',
            'title.max' => 'Tiêu đề không được vượt quá 255 ký tự',
            'deadline.required' => 'Ngày hạn là bắt buộc',
            'deadline.date' => 'Ngày hạn phải là ngày',
            'departments.required_without' => 'Phải chọn ít nhất một phòng ban',
            'users.required_without' => 'Phải chọn ít nhất một người',
            'include_department_heads.boolean' => 'Trường bao gồm trưởng/phó phòng phải là boolean',
            'for_departments.boolean' => 'Cột phòng là bắt buộc',
            'attachments.*.mimes' => 'Tệp đính kèm phải có định dạng: doc, docx, pdf, xlsx, xls, mp4',
        ]);

        DB::beginTransaction();

        try {
            $task->update([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'deadline' => $validated['deadline'],
                'updated_by' => $user->id,
                'for_departments' => $request->has('for_departments'),
                'include_department_heads' => $request->has('include_department_heads'),
            ]);

            // Update departments
            $task->departments()->detach();
            if ($request->has('departments')) {
                $task->departments()->attach($request->departments, ['status' => 'pending']);

                // Auto-assign to department heads if include_department_heads is true
                if ($request->has('include_department_heads')) {
                    $departmentHeads = User::whereIn('department_id', $request->departments)
                        ->whereHas('role', function ($query) {
                            $query->whereIn('slug', ['department-head', 'deputy-department-head']);
                        })
                        ->pluck('id');

                    if ($departmentHeads->count() > 0) {
                        $task->users()->attach($departmentHeads, ['status' => 'pending']);
                    }
                }
            }

            // Update users
            $task->users()->detach();
            if ($request->has('users')) {
                $task->users()->attach($request->users, ['status' => 'pending']);
            }

            // Remove selected attachments
            if ($request->has('remove_attachments')) {
                foreach ($request->remove_attachments as $attachmentId) {
                    $attachment = $task->attachments()->find($attachmentId);
                    if ($attachment) {
                        // Delete the file from storage
                        if (file_exists(public_path($attachment->file_path))) {
                            unlink(public_path($attachment->file_path));
                        }
                        // Delete the record
                        $attachment->delete();
                    }
                }
            }

            // Process new file attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    // Generate a unique filename
                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

                    // Store the file
                    $file->storeAs('task_attachments', $filename, 'public');

                    // Create attachment record
                    $task->attachments()->create([
                        'filename' => $filename,
                        'original_filename' => $file->getClientOriginalName(),
                        'file_path' => 'storage/task_attachments/' . $filename,
                        'file_type' => $file->getClientOriginalExtension(),
                        'file_size' => $file->getSize(),
                        'uploaded_by' => $user->id,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('tasks.show', $task)->with('success', 'Công việc đã được cập nhật thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Đã xảy ra lỗi: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $user = Auth::user();

        // Only admin can delete tasks
        if (!$user->isAdmin()) {
            return redirect()->route('tasks.index')->with('error', 'Chỉ quản trị viên mới có quyền xóa công việc!');
        }

        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Công việc đã được xóa thành công!');
    }

    public function pendingApproval(Request $request)
    {
        $user = Auth::user();

        if (!$user->canAssignTasks()) {
            return redirect()->route('tasks.received')
                ->with('error', 'Bạn không có quyền xem mục này!');
        }

        $query = DB::table('task_user')
            ->join('tasks', 'task_user.task_id', '=', 'tasks.id')
            ->join('users', 'task_user.user_id', '=', 'users.id')
            ->leftJoin('departments', 'users.department_id', '=', 'departments.id')
            ->select(
                'task_user.*',
                'task_user.id as task_user_id',
                'task_user.completion_attempt',
                'tasks.title',
                'tasks.description',
                'tasks.deadline',
                'users.id as user_id',
                'users.name as user_name',
                'departments.name as department_name'
            )
            ->where('tasks.created_by', $user->id)
            ->where('task_user.status', 'completed')
            ->whereNull('task_user.approved_at');

        // Áp dụng các filter nếu có
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('tasks.title', 'like', "%{$search}%")
                    ->orWhere('users.name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('department_id')) {
            $query->where('users.department_id', $request->department_id);
        }

        if ($request->filled('date_from')) {
            $query->where('task_user.completion_date', '>=', $request->date_from . ' 00:00:00');
        }

        if ($request->filled('date_to')) {
            $query->where('task_user.completion_date', '<=', $request->date_to . ' 23:59:59');
        }

        // Sắp xếp và phân trang
        $pendingTasks = $query->orderBy('task_user.completion_date', 'desc')
            ->paginate(15);


        $taskUserIds = $pendingTasks->pluck('task_user_id')->toArray();
        $taskUserAttachments = [];
        $taskUserAttemptsMap = [];

        foreach ($pendingTasks as $task) {
            $taskUserAttemptsMap[$task->task_user_id] = $task->completion_attempt ?? 0;
        }

        if (!empty($taskUserIds)) {
            // Lấy tất cả file đính kèm cho các task_user
            $attachments = TaskUserAttachment::whereIn('task_user_id', $taskUserIds)
                ->where('is_active', true)
                ->orderBy('created_at', 'desc')
                ->get();

            // Nhóm file đính kèm theo task_user_id và lọc theo completion_attempt hiện tại
            foreach ($attachments as $attachment) {
                $taskUserId = $attachment->task_user_id;
                $currentAttempt = $taskUserAttemptsMap[$taskUserId] ?? 0;

                // Chỉ lấy file đính kèm của lần gửi duyệt hiện tại
                if ($attachment->completion_attempt == $currentAttempt) {
                    $taskUserAttachments[$taskUserId][] = $attachment;
                }
            }
        }

        // Lấy danh sách các phòng ban cho filter
        if ($user->isDirector() || $user->isDeputyDirector()) {
            $departments = Department::orderBy('name')->get();
        } else {
            $departments = Department::where('id', $user->department_id)->get();
        }

        return view('manager_task.tasks.pending_approval', compact(
            'pendingTasks',
            'departments',
            'taskUserAttachments',
            'taskUserAttemptsMap'
        ));
    }

    public function approveStatus(Request $request, Task $task, $assigneeId)
    {
        $user = Auth::user();

        if ($task->created_by !== $user->id && !$user->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền phê duyệt hoặc từ chối kết quả công việc này!'
                ], 403);
            }

            return redirect()->route('tasks.show', $task)
                ->with('error', 'Bạn không có quyền phê duyệt hoặc từ chối kết quả công việc này!');
        }

        $taskUser = TaskUser::where('task_id', $task->id)
            ->where('user_id', $assigneeId)
            ->first();

        if (!$taskUser) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy thông tin phân công!'
                ], 404);
            }

            return redirect()->route('tasks.show', $task)
                ->with('error', 'Không tìm thấy thông tin phân công!');
        }

        // Validate status and rejection reason
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'status' => 'required|in:' .
                TaskUser::STATUS_APPROVED . ',' .
                TaskUser::STATUS_APPROVAL_REJECTED . ',' .
                TaskUser::STATUS_REJECTED,
            'rejection_reason' => 'required_if:status,' . TaskUser::STATUS_APPROVAL_REJECTED . ',' . TaskUser::STATUS_REJECTED,
        ], [
            'status.required' => 'Trạng thái là bắt buộc',
            'status.in' => 'Trạng thái không hợp lệ',
            'rejection_reason.required_if' => 'Lý do từ chối là bắt buộc khi từ chối kết quả',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();

        // Update the status
        $updateData = [
            'status' => $validated['status'],
        ];

        $updateData['approved_rejected'] = $taskUser->approved_rejected ?? 0;

        // Handle rejection reason for approval_rejected or rejected status
        if (in_array($validated['status'], [TaskUser::STATUS_APPROVAL_REJECTED, TaskUser::STATUS_REJECTED])) {
            // Lấy dữ liệu từ chối hiện tại (nếu có)
            $currentReasonData = json_decode($taskUser->approved_rejected_reason, true) ?: [];
            $history = isset($currentReasonData['history']) ? $currentReasonData['history'] : [];

            // Tạo mục từ chối mới
            $newRejection = [
                'message' => $request->input('rejection_reason'),
                'rejected_at' => now()->format('Y-m-d H:i:s'),
                'rejected_by' => $user->name
            ];

            // Thêm vào lịch sử
            $history[] = $newRejection;

            // Tăng bộ đếm số lần từ chối
            $updateData['approved_rejected'] = ($taskUser->approved_rejected ?? 0) + 1;

            // Lưu lịch sử từ chối vào JSON
            $updateData['approved_rejected_reason'] = json_encode([
                'message' => $request->input('rejection_reason'), // Lý do hiện tại
                'history' => $history // Tất cả lịch sử
            ]);
        } else {
            $updateData = [
                'status' => $validated['status'],
                'approved_by' => $user->id,
                'approved_at' => now(),
            ];
        }

        $taskUser->update($updateData);

        $messages = [
            TaskUser::STATUS_APPROVED => 'Kết quả công việc đã được phê duyệt!',
            TaskUser::STATUS_APPROVAL_REJECTED => 'Đã từ chối kết quả công việc và yêu cầu thực hiện lại! (Lần thứ ' .  $taskUser->approved_rejected . ')',
            TaskUser::STATUS_REJECTED => 'Công việc đã bị hủy!'
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $messages[$validated['status']],
                'data' => [
                    'status' => $validated['status'],
                    'approved_at' => now()->format('d/m/Y H:i'),
                    'approved_by' => $user->name
                ]
            ]);
        }

        return redirect()->route('tasks.show', $task)
            ->with('success', $messages[$validated['status']]);
    }

    public function getRejectionHistory(Task $task, $userId)
    {
        $user = Auth::user();

        // Kiểm tra quyền truy cập
        if ($task->created_by != $user->id && !$user->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $taskUser = TaskUser::where('task_id', $task->id)
            ->where('user_id', $userId)
            ->first();

        if (!$taskUser) {
            return response()->json(['error' => 'Not found'], 404);
        }

        // Parse rejection reason (JSON)
        $rejectionData = [];
        if ($taskUser->approved_rejected_reason) {
            try {
                $rejectionData = json_decode($taskUser->approved_rejected_reason, true) ?? [];
            } catch (\Exception $e) {
                $rejectionData = ['message' => $taskUser->approved_rejected_reason];
            }
        }

        return response()->json($rejectionData);
    }

    /**
     * Update task status by assignee
     */
    public function updateStatus(Request $request, Task $task)
    {
        $user = Auth::user();

        // Kiểm tra quyền
        if (!$task->users()->where('users.id', $user->id)->exists()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không được phân công công việc này!'
                ], 403);
            }
            return redirect()->route('tasks.show', $task)->with('error', 'Bạn không được phân công công việc này!');
        }

        $taskUser = $task->users()->where('users.id', $user->id)->first()->pivot;

        // Kiểm tra trạng thái hiện tại
        if (
            in_array($taskUser->status, [TaskUser::STATUS_APPROVED, TaskUser::STATUS_REJECTED]) &&
            $taskUser->status !== TaskUser::STATUS_APPROVAL_REJECTED
        ) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể cập nhật trạng thái khi công việc đã được phê duyệt hoặc từ chối!'
                ], 403);
            }
            return redirect()->route('tasks.show', $task)
                ->with('error', 'Không thể cập nhật trạng thái khi công việc đã được phê duyệt hoặc từ chối!');
        }

        // Xác thực dữ liệu
        $rules = ['status' => 'required|in:in_progress,completed'];

        // Nếu trạng thái là hoàn thành, yêu cầu phải có file đính kèm
        if ($request->status === 'completed') {
            $rules['completion_files'] = 'required|array|min:1';
            $rules['completion_files.*'] = 'file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,mp4,zip,rar|max:10240';
        }

        try {
            $validated = $request->validate($rules, [
                'completion_files.required' => 'Vui lòng đính kèm ít nhất một file kết quả khi hoàn thành công việc.',
                'completion_files.array' => 'Định dạng file không hợp lệ.',
                'completion_files.min' => 'Vui lòng đính kèm ít nhất một file kết quả khi hoàn thành công việc.',
                'completion_files.*.file' => 'Tệp không hợp lệ.',
                'completion_files.*.mimes' => 'Định dạng tệp không được hỗ trợ.',
                'completion_files.*.max' => 'Kích thước tệp không được vượt quá 10MB.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        // Xử lý trong transaction
        DB::beginTransaction();

        try {
            $previousStatus = $taskUser->status;
            $currentAttemptNumber = $taskUser->completion_attempt ?? 0;

            // Nếu từ trạng thái khác chuyển sang completed, tăng số lần hoàn thành
            if ($validated['status'] === TaskUser::STATUS_COMPLETED && $previousStatus !== TaskUser::STATUS_COMPLETED) {
                $currentAttemptNumber++;

                // Cập nhật trạng thái và lần hoàn thành
                $task->users()->updateExistingPivot($user->id, [
                    'status' => TaskUser::STATUS_COMPLETED,
                    'completion_date' => now(),
                    'completion_attempt' => $currentAttemptNumber
                ]);

                // Upload files
                if ($request->hasFile('completion_files')) {
                    $taskUserId = DB::table('task_user')
                        ->where('task_id', $task->id)
                        ->where('user_id', $user->id)
                        ->value('id');

                    foreach ($request->file('completion_files') as $file) {
                        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                        $file->storeAs('task_user_attachments', $filename, 'public');

                        DB::table('task_user_attachments')->insert([
                            'task_user_id' => $taskUserId,
                            'filename' => $filename,
                            'original_filename' => $file->getClientOriginalName(),
                            'file_path' => 'storage/task_user_attachments/' . $filename,
                            'file_type' => $file->getClientOriginalExtension(),
                            'file_size' => $file->getSize(),
                            'uploaded_by' => $user->id,
                            'completion_attempt' => $currentAttemptNumber,
                            'description' => $request->file_description,
                            'is_active' => true,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }
            }
            // Nếu từ completed chuyển sang in_progress
            else if ($validated['status'] === TaskUser::STATUS_IN_PROGRESS && in_array($previousStatus, [TaskUser::STATUS_COMPLETED])) {
                // Cập nhật trạng thái
                $task->users()->updateExistingPivot($user->id, [
                    'status' => TaskUser::STATUS_IN_PROGRESS,
                    'completion_date' => null
                ]);

                // Đánh dấu các file của lần hoàn thành hiện tại là không còn active
                $taskUserId = DB::table('task_user')
                    ->where('task_id', $task->id)
                    ->where('user_id', $user->id)
                    ->value('id');

                DB::table('task_user_attachments')
                    ->where('task_user_id', $taskUserId)
                    ->where('completion_attempt', $currentAttemptNumber)
                    ->update([
                        'is_active' => false,
                        'updated_at' => now()
                    ]);
            }
            // Các trường hợp khác chỉ cập nhật trạng thái
            else {
                $task->users()->updateExistingPivot($user->id, [
                    'status' => $validated['status']
                ]);
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Trạng thái công việc đã được cập nhật thành công.',
                    'data' => [
                        'status' => $validated['status'],
                        'completion_date' => $validated['status'] === TaskUser::STATUS_COMPLETED ? now()->format('d/m/Y H:i') : null
                    ]
                ]);
            }

            return redirect()->route('tasks.show', $task)
                ->with('success', 'Trạng thái công việc đã được cập nhật thành công.');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Đã xảy ra lỗi: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function downloadUserAttachment($id)
    {
        $attachment = TaskUserAttachment::findOrFail($id);
        $taskUser = $attachment->taskUser;
        $task = Task::findOrFail($taskUser->task_id);
        $uploader = User::findOrFail($taskUser->user_id);

        // Lấy thông tin người đang đăng nhập
        $user = Auth::user();
        $canAccess = false;

        // 1. Admin, Giám đốc, Phó giám đốc xem được tất cả
        if ($user->isAdmin() || $user->isDirector() || $user->isDeputyDirector()) {
            $canAccess = true;
        }


        // 2. Người upload file luôn có quyền xem file của mình
        elseif ($taskUser->user_id == $user->id) {
            $canAccess = true;
        }
        // 3. Người tạo task có quyền xem file của người thực hiện
        elseif ($task->created_by == $user->id) {
            $canAccess = true;
        }
        // 4. Trưởng phòng và Phó phòng xem được file của nhân viên trong phòng mình
        elseif (($user->isDepartmentHead() || $user->isDeputyDepartmentHead()) &&
            $uploader->department_id == $user->department_id
        ) {
            $canAccess = true;
        }

        // Nếu không có quyền truy cập
        if (!$canAccess) {
            return redirect()->back()->with('error', 'Bạn không có quyền tải file này!');
        }

        // Kiểm tra file có tồn tại không
        if (!file_exists(public_path($attachment->file_path))) {
            return redirect()->back()->with('error', 'Tệp đính kèm không tồn tại!');
        }

        // Trả về file để tải xuống
        return response()->download(public_path($attachment->file_path), $attachment->original_filename);
    }

    /**
     * Approve task completion
     */
    public function approveCompletion(Request $request, Task $task, User $assignee)
    {
        $user = Auth::user();

        // Check if user has permission to approve
        $canApprove = false;

        if ($user->isAdmin() || $task->created_by === $user->id) {
            $canApprove = true;
        } elseif ($user->isDepartmentHead() && $assignee->department_id === $user->department_id) {
            $canApprove = true;
        } elseif ($user->isDeputyDepartmentHead() && $assignee->department_id === $user->department_id && $assignee->isStaff()) {
            $canApprove = true;
        }

        if (!$canApprove) {
            return redirect()->route('tasks.show', $task)->with('error', 'Bạn không có quyền phê duyệt công việc này!');
        }

        $task->users()->updateExistingPivot($assignee->id, [
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return redirect()->route('tasks.show', $task)->with('success', 'Công việc đã được phê duyệt thành công!');
    }

    public function rejectCompletion(Request $request, Task $task, User $assignee)
    {
        $user = Auth::user();

        // Check if user has permission to approve/reject
        $canApprove = false;

        if ($user->isAdmin() || $task->created_by === $user->id) {
            $canApprove = true;
        } elseif ($user->isDepartmentHead() && $assignee->department_id === $user->department_id) {
            $canApprove = true;
        } elseif ($user->isDeputyDepartmentHead() && $assignee->department_id === $user->department_id && $assignee->isStaff()) {
            $canApprove = true;
        }

        if (!$canApprove) {
            return redirect()->route('tasks.show', $task)->with('error', 'Bạn không có quyền từ chối kết quả công việc này!');
        }

        // Validate rejection reason
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        // Store rejection details
        $task->users()->updateExistingPivot($assignee->id, [
            'status' => TaskUser::STATUS_APPROVAL_REJECTED,
            'approved_by' => $user->id,
            'approved_at' => now(),
            'approved_rejected' => true,
            'approved_rejected_reason' => json_encode([
                'message' => $validated['rejection_reason'],
                'rejected_at' => now()->format('Y-m-d H:i:s')
            ])
        ]);

        return redirect()->route('tasks.show', $task)->with('success', 'Đã từ chối kết quả công việc!');
    }

    /**
     * Get departments available to the current user
     */
    private function getAvailableDepartments(User $user)
    {
        if ($user->isAdmin() || $user->isDirector() || $user->isDeputyDirector()) {
            // Admin, Director and Deputy Director have access to all departments
            return Department::all();
        } elseif ($user->isDepartmentHead() || $user->isDeputyDepartmentHead()) {
            // Department Heads and Deputy Heads can only create tasks for their own department
            return Department::where('id', $user->department_id)->get();
        }

        return collect();
    }

    /**
     * Get users available to the current user
     */
    private function getAvailableUsers(User $user)
    {
        if ($user->isAdmin()) {
            // Quản trị viên có thể giao việc cho bất kỳ ai
            return User::where('id', '!=', $user->id)->get();
        } elseif ($user->isDirector()) {
            // Giám đốc và Phó Giám đốc có thể giao việc cho bất kỳ ai
            return User::where('id', '!=', $user->id)
                ->where('role_id', '!=', $user->role_id) // không giao việc cho chính mình
                ->whereHas('role', function ($query) {
                    $query->whereIn('slug', ['deputy-director', 'department-head', 'deputy-department-head', 'staff']);
                })
                ->get();
        } elseif ($user->isDeputyDirector()) {
            return User::where('id', '!=', $user->id)
                ->whereHas('role', function ($query) {
                    $query->whereIn('slug', ['department-head', 'deputy-department-head', 'staff']);
                })
                ->get();
        } elseif ($user->isDepartmentHead()) {
            // Trưởng phòng có thể giao việc cho bất kỳ ai trong phòng của mình, bao gồm cả Phó phòng
            return User::where('department_id', $user->department_id)
                ->where('id', '!=', $user->id)
                ->whereHas('role', function ($query) {
                    $query->whereIn('slug', ['deputy-department-head', 'staff']);
                })
                ->get();
        } elseif ($user->isDeputyDepartmentHead()) {
            // Phó phòng chỉ có thể giao việc cho nhân viên trong phòng của mình
            return User::where('department_id', $user->department_id)
                ->whereHas('role', function ($query) {
                    $query->where('slug', 'staff');
                })
                ->get();
        }

        return collect();
    }

    /**
     * Task statistics by month and year
     */
    public function statistics(Request $request)
    {
        $user = Auth::user();

        // Kiểm tra quyền truy cập
        if (
            !in_array($user->role->slug, ['admin', 'director', 'deputy-director', 'department-head', 'deputy-department-head', 'staff'])
        ) {
            return redirect()->route('tasks.index')->with('error', 'Bạn không có quyền xem thống kê!');
        }

        $year = $request->input('year', date('Y'));
        $month = $request->input('month', date('m'));
        $selectedTaskId = $request->input('task_id');

        // Get start and end dates for queries
        $startDate = "$year-$month-01 00:00:00";
        $endDate = date('Y-m-t 23:59:59', strtotime($startDate));

        // Phân quyền truy cập dữ liệu theo vai trò
        $queryScope = $this->getStatisticsQueryScope($user);

        // Department and user statistics
        $departmentStats = $this->getDepartmentStatistics($user, $year, $month);
        $userStats = $this->getUserStatistics($user, $year, $month);

        // Get task counts by status
        $taskQuery = Task::whereBetween('created_at', [$startDate, $endDate]);

        // Áp dụng phân quyền truy cập dữ liệu
        $this->applyQueryScope($taskQuery, $queryScope, $user);

        $allTasks = $taskQuery->get();
        $totalTasks = $allTasks->count();

        // Tasks by status
        $completedTasks = 0;
        $inProgressTasks = 0;
        $pendingTasks = 0;
        $overdueTasks = 0;

        foreach ($allTasks as $task) {
            $taskStatus = $this->determineTaskStatus($task);

            switch ($taskStatus) {
                case 'completed':
                    $completedTasks++;
                    break;
                case 'in-progress':
                    $inProgressTasks++;
                    break;
                case 'pending':
                    $pendingTasks++;
                    break;
                case 'overdue':
                    $overdueTasks++;
                    break;
            }
        }

        // Recent completions query with scope
        $recentCompletionsQuery = DB::table('task_user')
            ->join('tasks', 'task_user.task_id', '=', 'tasks.id')
            ->join('users', 'task_user.user_id', '=', 'users.id')
            ->select('task_user.*', 'tasks.title', 'users.name')
            ->where('task_user.status', 'completed')
            ->whereNotNull('task_user.approved_at')
            ->whereBetween('task_user.completion_date', [$startDate, $endDate]);

        // Áp dụng phân quyền cho recent completions
        if ($user->role->slug === 'staff') {
            $recentCompletionsQuery->where('task_user.user_id', $user->id);
        } elseif ($user->role->slug === 'department-head' || $user->role->slug === 'deputy-department-head') {
            $recentCompletionsQuery->whereExists(function ($query) use ($user) {
                $query->select(DB::raw(1))
                    ->from('users')
                    ->whereColumn('users.id', 'task_user.user_id')
                    ->where('users.department_id', $user->department_id);
            });
        }

        $recentCompletions = $recentCompletionsQuery
            ->orderBy('task_user.completion_date', 'desc')
            ->limit(10)
            ->get();

        // Department progress
        $departmentProgress = [];

        foreach ($departmentStats as $stat) {
            $departmentName = $stat['department'];
            $total = $stat['total'];
            $completed = $stat['completed'];
            $late = $stat['late'];
            $incomplete = $stat['incomplete'];

            $inProgress = $incomplete - $pendingForDept = 0; // Simplified for now

            // Calculate percentages safely
            $completedPercent = $total > 0 ? ($completed / $total) * 100 : 0;
            $inProgressPercent = $total > 0 ? ($inProgress / $total) * 100 : 0;
            $pendingPercent = $total > 0 ? ($pendingForDept / $total) * 100 : 0;

            $departmentProgress[] = [
                'name' => $departmentName,
                'total' => $total,
                'completed' => $completed,
                'in_progress' => $inProgress,
                'pending' => $pendingForDept,
                'completed_percent' => $completedPercent,
                'in_progress_percent' => $inProgressPercent,
                'pending_percent' => $pendingPercent
            ];
        }

        // Department chart data
        $departmentChartData = [
            'labels' => [],
            'completed' => [],
            'in_progress' => [],
            'pending' => []
        ];

        foreach ($departmentProgress as $dept) {
            $departmentChartData['labels'][] = $dept['name'];
            $departmentChartData['completed'][] = $dept['completed'];
            $departmentChartData['in_progress'][] = $dept['in_progress'];
            $departmentChartData['pending'][] = $dept['pending'];
        }

        // Recent activity timeline với scope
        $recentActivities = [];

        // Get task creations với scope
        $taskCreationsQuery = Task::with('creator')->whereBetween('created_at', [$startDate, $endDate]);
        $this->applyQueryScope($taskCreationsQuery, $queryScope, $user);
        $taskCreations = $taskCreationsQuery->orderBy('created_at', 'desc')->limit(20)->get();

        // Get task completions với scope
        $taskCompletionsQuery = DB::table('task_user')
            ->join('tasks', 'task_user.task_id', '=', 'tasks.id')
            ->join('users', 'task_user.user_id', '=', 'users.id')
            ->select('task_user.*', 'tasks.title', 'users.name')
            ->where('task_user.status', 'completed')
            ->whereNotNull('task_user.completion_date')
            ->whereBetween('task_user.completion_date', [$startDate, $endDate]);

        if ($user->role->slug === 'staff') {
            $taskCompletionsQuery->where('task_user.user_id', $user->id);
        } elseif ($user->role->slug === 'department-head' || $user->role->slug === 'deputy-department-head') {
            $taskCompletionsQuery->whereExists(function ($query) use ($user) {
                $query->select(DB::raw(1))
                    ->from('users')
                    ->whereColumn('users.id', 'task_user.user_id')
                    ->where('users.department_id', $user->department_id);
            });
        }

        $taskCompletions = $taskCompletionsQuery->orderBy('task_user.completion_date', 'desc')->limit(20)->get();

        // Get task extensions with scope
        $taskExtensionsQuery = DB::table('task_extensions')
            ->join('tasks', 'task_extensions.task_id', '=', 'tasks.id')
            ->join('users', 'task_extensions.user_id', '=', 'users.id')
            ->join('users as requesters', 'task_extensions.requested_by', '=', 'requesters.id')
            ->select('task_extensions.*', 'tasks.title', 'users.name', 'requesters.name as requester_name')
            ->whereBetween('task_extensions.requested_at', [$startDate, $endDate]);

        if ($user->role->slug === 'staff') {
            $taskExtensionsQuery->where('task_extensions.user_id', $user->id);
        } elseif ($user->role->slug === 'department-head' || $user->role->slug === 'deputy-department-head') {
            $taskExtensionsQuery->whereExists(function ($query) use ($user) {
                $query->select(DB::raw(1))
                    ->from('users')
                    ->whereColumn('users.id', 'task_extensions.user_id')
                    ->where('users.department_id', $user->department_id);
            });
        }

        $taskExtensions = $taskExtensionsQuery->orderBy('task_extensions.requested_at', 'desc')->limit(20)->get();

        // Combine and format activities by date
        $activities = [];

        foreach ($taskCreations as $creation) {
            $date = date('Y-m-d', strtotime($creation->created_at));
            $activities[$date][] = [
                'type' => 'create',
                'time' => date('H:i', strtotime($creation->created_at)),
                'user' => $creation->creator->name ?? 'N/A',
                'action' => 'đã tạo công việc mới',
                'description' => $creation->title,
                'task_id' => $creation->id,
                'icon' => 'fa-plus-circle',
                'color' => 'bg-blue'
            ];
        }

        foreach ($taskCompletions as $completion) {
            $date = date('Y-m-d', strtotime($completion->completion_date));
            $activities[$date][] = [
                'type' => 'complete',
                'time' => date('H:i', strtotime($completion->completion_date)),
                'user' => $completion->name ?? 'N/A',
                'action' => 'đã hoàn thành công việc',
                'description' => $completion->title,
                'task_id' => $completion->task_id,
                'icon' => 'fa-check-circle',
                'color' => 'bg-green'
            ];
        }

        foreach ($taskExtensions as $extension) {
            $date = date('Y-m-d', strtotime($extension->requested_at));
            $activities[$date][] = [
                'type' => 'extend',
                'time' => date('H:i', strtotime($extension->requested_at)),
                'user' => $extension->requester_name ?? 'N/A',
                'action' => 'đã yêu cầu gia hạn cho',
                'description' => $extension->title,
                'task_id' => $extension->task_id,
                'icon' => 'fa-clock',
                'color' => 'bg-yellow'
            ];
        }

        // Sort activities by date (newest first)
        krsort($activities);

        // Format for view
        $recentActivities = [];
        foreach ($activities as $date => $items) {
            $recentActivities[] = [
                'date' => date('d/m/Y', strtotime($date)),
                'items' => $items
            ];
        }

        // Get top performers (users with most completed tasks) with scope
        $topPerformers = collect($userStats)
            ->sortByDesc('completed')
            ->filter(function ($item) {
                return $item['completed'] > 0;
            })
            ->take(10)
            ->values();

        // Get available tasks for selection dropdown
        $availableTasksQuery = Task::query();
        $this->applyQueryScope($availableTasksQuery, $queryScope, $user);
        $availableTasks = $availableTasksQuery->orderBy('created_at', 'desc')->take(50)->get();

        // Thống kê chi tiết theo task
        $taskDetailStats = [];
        $selectedTaskDetails = null;

        // If a specific task is selected, only show that task's statistics
        if ($selectedTaskId) {
            $selectedTask = Task::with(['users', 'departments', 'users.department', 'creator'])
                ->where('id', $selectedTaskId);
            
            // Apply permission check
            if ($queryScope === 'department') {
                $departmentId = $user->department_id;
                $selectedTask->where(function ($q) use ($departmentId, $user) {
                    $q->whereHas('departments', function ($q1) use ($departmentId) {
                        $q1->where('departments.id', $departmentId);
                    })
                    ->orWhereHas('users', function ($q1) use ($departmentId) {
                        $q1->where('users.department_id', $departmentId);
                    })
                    ->orWhere('created_by', $user->id);
                });
            } elseif ($queryScope === 'self') {
                $selectedTask->where(function ($q) use ($user) {
                    $q->whereHas('users', function ($q1) use ($user) {
                        $q1->where('users.id', $user->id);
                    })
                    ->orWhere('created_by', $user->id);
                });
            }
            
            $selectedTask = $selectedTask->first();
            
            if ($selectedTask) {
                $taskDetail = [
                    'id' => $selectedTask->id,
                    'title' => $selectedTask->title,
                    'deadline' => $selectedTask->deadline->format('d/m/Y H:i'),
                    'created_by' => $selectedTask->creator->name ?? 'N/A',
                    'for_departments' => $selectedTask->for_departments,
                    'departments' => [],
                    'users' => [],
                    'completion_rate' => 0
                ];

                // Nếu task được giao cho phòng ban
                if ($selectedTask->for_departments) {
                    foreach ($selectedTask->departments as $department) {
                        $departmentStats = $this->getTaskDepartmentStats($selectedTask, $department);
                        $taskDetail['departments'][] = $departmentStats;
                    }

                    // Sort departments by completion rate
                    usort($taskDetail['departments'], function ($a, $b) {
                        return $b['completion_rate'] <=> $a['completion_rate'];
                    });

                    // Calculate overall completion rate
                    $totalAssignees = count($taskDetail['departments']);
                    $totalCompletionRate = 0;

                    if ($totalAssignees > 0) {
                        foreach ($taskDetail['departments'] as $dept) {
                            $totalCompletionRate += $dept['completion_rate'];
                        }
                        $taskDetail['completion_rate'] = $totalCompletionRate / $totalAssignees;
                    }
                }
                // Nếu task được giao cho cá nhân
                else {
                    foreach ($selectedTask->users as $taskUser) {
                        $userStats = $this->getTaskUserStats($selectedTask, $taskUser);
                        $taskDetail['users'][] = $userStats;
                    }

                    // Sort users by completion rate
                    usort($taskDetail['users'], function ($a, $b) {
                        return $b['completion_rate'] <=> $a['completion_rate'];
                    });

                    // Calculate overall completion rate
                    $totalAssignees = count($taskDetail['users']);
                    $totalCompletionRate = 0;

                    if ($totalAssignees > 0) {
                        foreach ($taskDetail['users'] as $userStat) {
                            $totalCompletionRate += $userStat['completion_rate'];
                        }
                        $taskDetail['completion_rate'] = $totalCompletionRate / $totalAssignees;
                    }
                }

                $selectedTaskDetails = $taskDetail;
            }
        } else {
            // Get tasks assigned in the selected month/year
            $detailedTasksQuery = Task::with(['users', 'departments', 'users.department', 'creator'])
                ->whereBetween('created_at', [$startDate, $endDate]);

            // Áp dụng phân quyền 
            $this->applyQueryScope($detailedTasksQuery, $queryScope, $user);

            $detailedTasks = $detailedTasksQuery->get();

            foreach ($detailedTasks as $task) {
                $taskDetail = [
                    'id' => $task->id,
                    'title' => $task->title,
                    'deadline' => $task->deadline->format('d/m/Y H:i'),
                    'created_by' => $task->creator->name ?? 'N/A',
                    'for_departments' => $task->for_departments,
                    'departments' => [],
                    'users' => [],
                    'completion_rate' => 0
                ];

                // Nếu task được giao cho phòng ban
                if ($task->for_departments) {
                    foreach ($task->departments as $department) {
                        $departmentStats = $this->getTaskDepartmentStats($task, $department);
                        $taskDetail['departments'][] = $departmentStats;
                    }

                    // Sort departments by completion rate
                    usort($taskDetail['departments'], function ($a, $b) {
                        return $b['completion_rate'] <=> $a['completion_rate'];
                    });

                    // Calculate overall completion rate
                    $totalAssignees = count($taskDetail['departments']);
                    $totalCompletionRate = 0;

                    if ($totalAssignees > 0) {
                        foreach ($taskDetail['departments'] as $dept) {
                            $totalCompletionRate += $dept['completion_rate'];
                        }
                        $taskDetail['completion_rate'] = $totalCompletionRate / $totalAssignees;
                    }
                }
                // Nếu task được giao cho cá nhân
                else {
                    foreach ($task->users as $taskUser) {
                        $userStats = $this->getTaskUserStats($task, $taskUser);
                        $taskDetail['users'][] = $userStats;
                    }

                    // Sort users by completion rate
                    usort($taskDetail['users'], function ($a, $b) {
                        return $b['completion_rate'] <=> $a['completion_rate'];
                    });

                    // Calculate overall completion rate
                    $totalAssignees = count($taskDetail['users']);
                    $totalCompletionRate = 0;

                    if ($totalAssignees > 0) {
                        foreach ($taskDetail['users'] as $userStat) {
                            $totalCompletionRate += $userStat['completion_rate'];
                        }
                        $taskDetail['completion_rate'] = $totalCompletionRate / $totalAssignees;
                    }
                }

                $taskDetailStats[] = $taskDetail;
            }

            // Sort tasks by creation date (newest first)
            usort($taskDetailStats, function ($a, $b) {
                return $b['id'] <=> $a['id'];
            });
        }

        return view('manager_task.tasks.statistics', compact(
            'year',
            'month',
            'totalTasks',
            'completedTasks',
            'inProgressTasks',
            'pendingTasks',
            'overdueTasks',
            'departmentProgress',
            'departmentChartData',
            'recentCompletions',
            'recentActivities',
            'topPerformers',
            'taskDetailStats',
            'availableTasks',
            'selectedTaskDetails',
            'selectedTaskId'
        ));
    }

    /**
     * Define query scope based on user role
     */
    private function getStatisticsQueryScope(User $user)
    {
        // Admin, Director, and Deputy Director can see all data
        if ($user->role->slug === 'admin' || $user->role->slug === 'director' || $user->role->slug === 'deputy-director') {
            return 'all'; // See all data
        } 
        // Department Head and Deputy Department Head can see department data
        elseif ($user->role->slug === 'department-head' || $user->role->slug === 'deputy-department-head') {
            return 'department'; // See department data
        } 
        // Regular staff and other roles can only see their own data
        else {
            return 'self'; // See only own data
        }
    }

    /**
     * Apply query scope based on user role
     */
    private function applyQueryScope($query, $scope, User $user)
    {
        if ($scope === 'department') {
            $departmentId = $user->department_id;

            $query->where(function ($q) use ($departmentId, $user) {
                // Tasks assigned to the department
                $q->whereHas('departments', function ($q1) use ($departmentId) {
                    $q1->where('departments.id', $departmentId);
                });

                // OR Tasks assigned to users in the department
                $q->orWhereHas('users', function ($q1) use ($departmentId) {
                    $q1->where('users.department_id', $departmentId);
                });

                // OR Tasks created by the user
                $q->orWhere('tasks.created_by', $user->id);
            });
        } elseif ($scope === 'self') {
            $userId = $user->id;

            $query->where(function ($q) use ($userId) {
                // Tasks assigned to the user
                $q->whereHas('users', function ($q1) use ($userId) {
                    $q1->where('users.id', $userId);
                });

                // OR Tasks created by the user
                $q->orWhere('tasks.created_by', $userId);
            });
        }
        // For 'all' scope, no filtering needed
    }

    /**
     * Get department task statistics with scope
     */
    private function getDepartmentStatistics(User $user, $year, $month)
    {
        $startDate = "$year-$month-01 00:00:00";
        $endDate = date('Y-m-t 23:59:59', strtotime($startDate));
        $queryScope = $this->getStatisticsQueryScope($user);

        // Get departments based on user role
        if ($queryScope === 'all') {
            $departments = Department::all();
        } elseif ($queryScope === 'department') {
            $departments = Department::where('id', $user->department_id)->get();
        } else {
            // For staff, only show their own department for reference
            $departments = $user->department_id ? Department::where('id', $user->department_id)->get() : collect();
        }

        $stats = collect();

        foreach ($departments as $department) {
            // Base query for tasks related to this department
            $tasksQuery = Task::query();

            if ($queryScope === 'self') {
                // Staff can only see tasks assigned to them
                $tasksQuery->whereHas('users', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                });
            } else {
                // Department heads see all department tasks, admins/directors see all
                $tasksQuery->where(function ($q) use ($department) {
                    $q->whereHas('departments', function ($q1) use ($department) {
                        $q1->where('departments.id', $department->id);
                    });

                    $q->orWhereHas('users', function ($q1) use ($department) {
                        $q1->where('users.department_id', $department->id);
                    });
                });
            }

            $tasksQuery->whereBetween('created_at', [$startDate, $endDate]);
            $tasks = $tasksQuery->get();

            $total = $tasks->count();
            $completed = 0;
            $late = 0;

            foreach ($tasks as $task) {
                if ($this->determineTaskStatus($task) === 'completed') {
                    $completed++;
                } else if ($this->determineTaskStatus($task) === 'overdue') {
                    $late++;
                }
            }

            $stats->push([
                'department' => $department->name,
                'total' => $total,
                'completed' => $completed,
                'late' => $late,
                'incomplete' => $total - $completed,
                'completion_rate' => $total > 0 ? ($completed / $total) * 100 : 0
            ]);
        }

        return $stats;
    }

    /**
     * Get user task statistics with scope
     */
    private function getUserStatistics(User $user, $year, $month)
    {
        $startDate = "$year-$month-01 00:00:00";
        $endDate = date('Y-m-t 23:59:59', strtotime($startDate));
        $queryScope = $this->getStatisticsQueryScope($user);

        // Get users based on user role
        if ($queryScope === 'all') {
            $users = User::all();
        } elseif ($queryScope === 'department') {
            $users = User::where('department_id', $user->department_id)->get();
        } else {
            $users = User::where('id', $user->id)->get();
        }

        $stats = collect();

        foreach ($users as $u) {
            // Base query for tasks assigned to this user
            $tasksQuery = Task::query()
                ->whereHas('users', function ($q) use ($u) {
                    $q->where('users.id', $u->id);
                })
                ->whereBetween('created_at', [$startDate, $endDate]);

            $tasks = $tasksQuery->get();

            $total = $tasks->count();
            $completed = 0;
            $late = 0;

            foreach ($tasks as $task) {
                $taskUser = $task->users()->where('users.id', $u->id)->first();
                if ($taskUser && $taskUser->pivot->status === TaskUser::STATUS_COMPLETED) {
                    $completed++;
                } else if ($task->deadline < now() && (!$taskUser || $taskUser->pivot->status !== TaskUser::STATUS_COMPLETED)) {
                    $late++;
                }
            }

            $stats->push([
                'user' => $u->name,
                'department' => $u->department ? $u->department->name : 'N/A',
                'total' => $total,
                'completed' => $completed,
                'late' => $late,
                'incomplete' => $total - $completed,
                'completion_rate' => $total > 0 ? ($completed / $total) * 100 : 0
            ]);
        }

        return $stats;
    }

    /**
     * Get statistics for a specific task department
     */
    private function getTaskDepartmentStats(Task $task, Department $department)
    {
        // Get all users in department assigned to this task
        $departmentUsers = $task->users()
            ->where('users.department_id', $department->id)
            ->get();

        $totalUsers = $departmentUsers->count();
        $completedUsers = 0;
        $overdueUsers = 0;

        foreach ($departmentUsers as $user) {
            $status = $user->pivot->status;

            if ($status === TaskUser::STATUS_COMPLETED) {
                $completedUsers++;
            } else if ($task->deadline < now() && $status !== TaskUser::STATUS_COMPLETED) {
                $overdueUsers++;
            }
        }

        return [
            'department_id' => $department->id,
            'department_name' => $department->name,
            'total_users' => $totalUsers,
            'completed_users' => $completedUsers,
            'overdue_users' => $overdueUsers,
            'completion_rate' => $totalUsers > 0 ? ($completedUsers / $totalUsers) * 100 : 0
        ];
    }

    /**
     * Get statistics for a specific task user
     */
    private function getTaskUserStats(Task $task, User $assignee)
    {
        $taskUser = $task->users()
            ->where('users.id', $assignee->id)
            ->first();

        $status = $taskUser ? $taskUser->pivot->status : 'unknown';
        $viewed = $taskUser && $taskUser->pivot->viewed_at;
        $completionRate = 0;

        // Calculate completion rate based on status
        switch ($status) {
            case TaskUser::STATUS_COMPLETED:
            case TaskUser::STATUS_APPROVED:
                $completionRate = 100;
                break;
            case TaskUser::STATUS_IN_PROGRESS:
                $completionRate = 50;
                break;
            case TaskUser::STATUS_VIEWED:
                $completionRate = 25;
                break;
            case TaskUser::STATUS_SENDING:
                $completionRate = $viewed ? 10 : 0;
                break;
            default:
                $completionRate = 0;
        }

        return [
            'user_id' => $assignee->id,
            'user_name' => $assignee->name,
            'department' => $assignee->department ? $assignee->department->name : 'N/A',
            'status' => $status,
            'completion_rate' => $completionRate,
            'is_overdue' => $task->deadline < now() && $status !== TaskUser::STATUS_COMPLETED
        ];
    }
    /**
     * Determine task status based on assignment data
     */
    private function determineTaskStatus(Task $task)
    {
        // Check if task has any assignments
        if ($task->users->isEmpty() && $task->departments->isEmpty()) {
            return 'pending';
        }

        // For individual assignments
        $completedCount = 0;
        $inProgressCount = 0;
        $pendingCount = 0;
        $overdueCount = 0;

        foreach ($task->users as $user) {
            if ($user->pivot->status === 'completed' && $user->pivot->approved_at) {
                if ($user->pivot->completion_date <= $task->deadline) {
                    $completedCount++;
                } else {
                    // Completed but late
                    $overdueCount++;
                }
            } elseif ($user->pivot->status === 'in-progress') {
                $inProgressCount++;
            } elseif (now()->gt($task->deadline)) {
                $overdueCount++;
            } else {
                $pendingCount++;
            }
        }

        // Determine overall status
        if ($completedCount > 0 && $completedCount == $task->users->count()) {
            return 'completed';
        } elseif ($overdueCount > 0) {
            return 'overdue';
        } elseif ($inProgressCount > 0) {
            return 'in-progress';
        } else {
            return 'pending';
        }
    }

    /**
     * Download a task attachment
     */
    public function downloadAttachment($id)
    {
        $attachment = \App\Models\TaskAttachment::findOrFail($id);

        // Check if file exists
        if (!file_exists(public_path($attachment->file_path))) {
            return redirect()->back()->with('error', 'Tệp đính kèm không tồn tại!');
        }

        // Return the file download response
        return response()->download(public_path($attachment->file_path), $attachment->original_filename);
    }
}
