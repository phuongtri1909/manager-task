<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Different task views based on user role
        if ($user->isAdmin()) {
            $tasks = Task::with('creator', 'departments', 'users');
        } elseif ($user->isDirector() || $user->isDeputyDirector()) {
            // Directors see tasks from all departments and higher-level management
            $managerRoles = ['director', 'deputy-director', 'department-head', 'deputy-department-head'];
            $managers = User::whereHas('role', function($query) use ($managerRoles) {
                $query->whereIn('slug', $managerRoles);
            })->pluck('id');
            
            $tasks = Task::where(function($query) use ($user, $managers) {
                $query->whereIn('created_by', $managers)
                    ->orWhereHas('users', function($q) use ($managers) {
                        $q->whereIn('users.id', $managers);
                    });
            })
            ->with('creator', 'departments', 'users');
        } elseif ($user->isDepartmentHead() || $user->isDeputyDepartmentHead()) {
            // Department heads see tasks for their department
            $departmentId = $user->department_id;
            
            $tasks = Task::where(function($query) use ($user, $departmentId) {
                $query->where('created_by', $user->id)
                    ->orWhereHas('departments', function($q) use ($departmentId) {
                        $q->where('departments.id', $departmentId);
                    })
                    ->orWhereHas('users', function($q) use ($user) {
                        $q->where('users.id', $user->id);
                    });
            })
            ->with('creator', 'departments', 'users');
        } else {
            // Regular staff see only their assigned tasks
            $tasks = $user->tasks()->with('creator');
        }
        
        // Apply filters
        if ($request->filled('title')) {
            $tasks->where('title', 'like', '%' . $request->title . '%');
        }
        
        if ($request->filled('status')) {
            $tasks->where('status', $request->status);
        }
        
        if ($request->filled('overdue')) {
            $now = now();
            if ($request->overdue == '1') {
                // Overdue tasks (deadline < now and not completed)
                $tasks->where('deadline', '<', $now)
                    ->where(function($query) {
                        $query->where('status', '!=', 'completed')
                            ->orWhereNull('status');
                    });
            } else {
                // Not overdue tasks (deadline >= now OR completed)
                $tasks->where(function($query) use ($now) {
                    $query->where('deadline', '>=', $now)
                        ->orWhere('status', 'completed');
                });
            }
        }
        
        // Get paginated results
        $tasks = $tasks->latest()->paginate(10);
        
        return view('manager_task.tasks.index', compact('tasks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        
        if (!$user->canAssignTasks()) {
            return redirect()->route('tasks.index')->with('error', 'Bạn không có quyền tạo công việc!');
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
        
        if (!$user->canAssignTasks()) {
            return redirect()->route('tasks.index')->with('error', 'Bạn không có quyền tạo công việc!');
        }
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'required|date|after:now',
            'departments' => 'required_without:users|array',
            'users' => 'required_without:departments|array',
            'include_department_heads' => 'boolean',
            'for_departments' => 'boolean',
        ],[
            'title.required' => 'Tiêu đề là bắt buộc',
            'title.string' => 'Tiêu đề phải là chuỗi',
            'title.max' => 'Tiêu đề không được vượt quá 255 ký tự',
            'deadline.required' => 'Ngày hạn là bắt buộc',
            'deadline.date' => 'Ngày hạn phải là ngày',
            'deadline.after' => 'Ngày hạn phải sau ngày hiện tại',
            'departments.required_without' => 'Phải chọn ít nhất một phòng ban',
            'users.required_without' => 'Phải chọn ít nhất một người',
            'include_department_heads.boolean' => 'Trường bao gồm trưởng/phó phòng phải là boolean',
            'for_departments.boolean' => 'Cột phòng là bắt buộc',      
        ]);
        
        DB::beginTransaction();
        
        try {
            $task = Task::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'deadline' => $validated['deadline'],
                'created_by' => $user->id,
                'updated_by' => $user->id,
                'for_departments' => $request->has('for_departments'),
                'include_department_heads' => $request->has('include_department_heads'),
            ]);
            
            // Assign to departments if selected
            if ($request->has('departments')) {
                $task->departments()->attach($request->departments, ['status' => 'pending']);
                
                // Auto-assign to department heads if include_department_heads is true
                if ($request->has('include_department_heads')) {
                    $departmentHeads = User::whereIn('department_id', $request->departments)
                        ->whereHas('role', function($query) {
                            $query->whereIn('slug', ['department-head', 'deputy-department-head']);
                        })
                        ->pluck('id');
                    
                    if ($departmentHeads->count() > 0) {
                        $task->users()->attach($departmentHeads, ['status' => 'pending']);
                    }
                }
            }
            
            // Assign to individual users if selected
            if ($request->has('users')) {
                $task->users()->attach($request->users, ['status' => 'pending']);
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
        
        // Mark as viewed if the current user is assigned to this task
        if ($task->users()->where('users.id', $user->id)->exists() && 
            is_null($task->users()->where('users.id', $user->id)->first()->pivot->viewed_at)) {
            $task->users()->updateExistingPivot($user->id, [
                'viewed_at' => now()
            ]);
        }
        
        $canEdit = $user->isAdmin() || $task->created_by === $user->id;
        $canUpdateStatus = $task->users()->where('users.id', $user->id)->exists();
        
        return view('manager_task.tasks.show', compact('task', 'canEdit', 'canUpdateStatus'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        $user = Auth::user();
        
        if (!($user->isAdmin() || $task->created_by === $user->id)) {
            return redirect()->route('tasks.index')->with('error', 'Bạn không có quyền chỉnh sửa công việc này!');
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
        
        if (!($user->isAdmin() || $task->created_by === $user->id)) {
            return redirect()->route('tasks.index')->with('error', 'Bạn không có quyền chỉnh sửa công việc này!');
        }
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'required|date',
            'departments' => 'required_without:users|array',
            'users' => 'required_without:departments|array',
            'include_department_heads' => 'boolean',
            'for_departments' => 'boolean',
        ],[
            'title.required' => 'Tiêu đề là bắt buộc',
            'title.string' => 'Tiêu đề phải là chuỗi',
            'title.max' => 'Tiêu đề không được vượt quá 255 ký tự',
            'deadline.required' => 'Ngày hạn là bắt buộc',
            'deadline.date' => 'Ngày hạn phải là ngày',
            'departments.required_without' => 'Phải chọn ít nhất một phòng ban',
            'users.required_without' => 'Phải chọn ít nhất một người',
            'include_department_heads.boolean' => 'Trường bao gồm trưởng/phó phòng phải là boolean',
            'for_departments.boolean' => 'Cột phòng là bắt buộc',
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
                        ->whereHas('role', function($query) {
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
        
        if (!($user->isAdmin() || $task->created_by === $user->id)) {
            return redirect()->route('tasks.index')->with('error', 'Bạn không có quyền xóa công việc này!');
        }
        
        $task->delete();
        
        return redirect()->route('tasks.index')->with('success', 'Công việc đã được xóa thành công!');
    }
    
    /**
     * Update task status by assignee
     */
    public function updateStatus(Request $request, Task $task)
    {
        $user = Auth::user();
        
        if (!$task->users()->where('users.id', $user->id)->exists()) {
            return redirect()->route('tasks.show', $task)->with('error', 'Bạn không được phân công công việc này!');
        }
        
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed',
        ]);
        
        $task->users()->updateExistingPivot($user->id, [
            'status' => $validated['status'],
            'completion_date' => $validated['status'] === 'completed' ? now() : null,
        ]);
        
        return redirect()->route('tasks.show', $task)->with('success', 'Trạng thái công việc đã được cập nhật thành công!');
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
    
    /**
     * Get departments available to the current user
     */
    private function getAvailableDepartments(User $user)
    {
        if ($user->isAdmin() || $user->isDirector() || $user->isDeputyDirector()) {
            return Department::all();
        } elseif ($user->isDepartmentHead() || $user->isDeputyDepartmentHead()) {
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
            return User::where('id', '!=', $user->id)->get();
        } elseif ($user->isDirector() || $user->isDeputyDirector()) {
            return User::where('id', '!=', $user->id)->get();
        } elseif ($user->isDepartmentHead()) {
            return User::where('department_id', $user->department_id)
                ->where('id', '!=', $user->id)
                ->get();
        } elseif ($user->isDeputyDepartmentHead()) {
            return User::where('department_id', $user->department_id)
                ->whereHas('role', function($query) {
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
        
        if (!($user->isAdmin() || $user->isDirector() || $user->isDeputyDirector() || $user->isDepartmentHead() || $user->isDeputyDepartmentHead())) {
            return redirect()->route('tasks.index')->with('error', 'Bạn không có quyền xem thống kê!');
        }
        
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', date('m'));
        
        // Get start and end dates for queries
        $startDate = "$year-$month-01 00:00:00";
        $endDate = date('Y-m-t 23:59:59', strtotime($startDate));
        
        // Department and user statistics
        $departmentStats = $this->getDepartmentStatistics($user, $year, $month);
        $userStats = $this->getUserStatistics($user, $year, $month);
        
        // Get task counts by status
        $taskQuery = Task::whereBetween('created_at', [$startDate, $endDate]);
        
        // Filter by user's access level
        if (!$user->isAdmin() && !$user->isDirector() && !$user->isDeputyDirector()) {
            if ($user->isDepartmentHead() || $user->isDeputyDepartmentHead()) {
                $departmentId = $user->department_id;
                $taskQuery->whereHas('departments', function($query) use ($departmentId) {
                    $query->where('departments.id', $departmentId);
                });
            } else {
                $taskQuery->whereHas('users', function($query) use ($user) {
                    $query->where('users.id', $user->id);
                });
            }
        }
        
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
        
        // Recent completions
        $recentCompletions = DB::table('task_user')
            ->join('tasks', 'task_user.task_id', '=', 'tasks.id')
            ->join('users', 'task_user.user_id', '=', 'users.id')
            ->select('task_user.*', 'tasks.title', 'users.name')
            ->where('task_user.status', 'completed')
            ->whereNotNull('task_user.approved_at')
            ->whereBetween('task_user.completion_date', [$startDate, $endDate])
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
        
        // Recent activity timeline
        $recentActivities = [];
        
        // Get task creations
        $taskCreations = Task::with('creator')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
        
        // Get task completions
        $taskCompletions = DB::table('task_user')
            ->join('tasks', 'task_user.task_id', '=', 'tasks.id')
            ->join('users', 'task_user.user_id', '=', 'users.id')
            ->select('task_user.*', 'tasks.title', 'users.name')
            ->where('task_user.status', 'completed')
            ->whereNotNull('task_user.completion_date')
            ->whereBetween('task_user.completion_date', [$startDate, $endDate])
            ->orderBy('task_user.completion_date', 'desc')
            ->limit(20)
            ->get();
        
        // Get task extensions
        $taskExtensions = DB::table('task_extensions')
            ->join('tasks', 'task_extensions.task_id', '=', 'tasks.id')
            ->join('users', 'task_extensions.user_id', '=', 'users.id')
            ->join('users as requesters', 'task_extensions.requested_by', '=', 'requesters.id')
            ->select('task_extensions.*', 'tasks.title', 'users.name', 'requesters.name as requester_name')
            ->whereBetween('task_extensions.requested_at', [$startDate, $endDate])
            ->orderBy('task_extensions.requested_at', 'desc')
            ->limit(20)
            ->get();
        
        // Combine and format activities by date
        $activities = [];
        
        foreach ($taskCreations as $creation) {
            $date = date('Y-m-d', strtotime($creation->created_at));
            $activities[$date][] = [
                'time' => date('H:i', strtotime($creation->created_at)),
                'user' => $creation->creator->name,
                'action' => 'đã tạo công việc',
                'description' => $creation->title,
                'task_id' => $creation->id,
                'icon' => 'fa-plus',
                'color' => 'bg-blue'
            ];
        }
        
        foreach ($taskCompletions as $completion) {
            $date = date('Y-m-d', strtotime($completion->completion_date));
            $activities[$date][] = [
                'time' => date('H:i', strtotime($completion->completion_date)),
                'user' => $completion->name,
                'action' => 'đã hoàn thành công việc',
                'description' => $completion->title,
                'task_id' => $completion->task_id,
                'icon' => 'fa-check',
                'color' => 'bg-green'
            ];
        }
        
        foreach ($taskExtensions as $extension) {
            $date = date('Y-m-d', strtotime($extension->requested_at));
            $activities[$date][] = [
                'time' => date('H:i', strtotime($extension->requested_at)),
                'user' => $extension->requester_name,
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
        foreach ($activities as $date => $items) {
            $recentActivities[] = [
                'date' => date('d/m/Y', strtotime($date)),
                'items' => $items
            ];
        }
        
        // Get top performers (users with most completed tasks)
        $topPerformers = collect($userStats)
            ->sortByDesc('completed')
            ->take(5)
            ->values()
            ->all();
        
        return view('manager_task.tasks.statistics', compact(
            'departmentStats', 
            'userStats', 
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
            'topPerformers'
        ));
    }
    
    /**
     * Get department task statistics 
     */
    private function getDepartmentStatistics(User $user, $year, $month)
    {
        $startDate = "$year-$month-01 00:00:00";
        $endDate = date('Y-m-t 23:59:59', strtotime($startDate));
        
        if ($user->isAdmin() || $user->isDirector() || $user->isDeputyDirector()) {
            $departments = Department::all();
        } else {
            $departments = Department::where('id', $user->department_id)->get();
        }
        
        $stats = collect();
        
        foreach ($departments as $department) {
            $departmentTasks = Task::whereHas('departments', function($query) use ($department) {
                $query->where('departments.id', $department->id);
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
            
            $completed = 0;
            $late = 0;
            $incomplete = 0;
            
            foreach ($departmentTasks as $task) {
                $taskUser = $task->users()
                    ->whereHas('department', function($query) use ($department) {
                        $query->where('departments.id', $department->id);
                    })
                    ->first();
                
                if ($taskUser && $taskUser->pivot->status === 'completed' && $taskUser->pivot->approved_at) {
                    if ($taskUser->pivot->completion_date <= $task->deadline) {
                        $completed++;
                    } else {
                        $late++;
                    }
                } else {
                    $incomplete++;
                }
            }
            
            $stats->push([
                'department' => $department->name,
                'completed' => $completed,
                'late' => $late,
                'incomplete' => $incomplete,
                'total' => $departmentTasks->count()
            ]);
        }
        
        return $stats;
    }
    
    /**
     * Get user task statistics
     */
    private function getUserStatistics(User $user, $year, $month)
    {
        $startDate = "$year-$month-01 00:00:00";
        $endDate = date('Y-m-t 23:59:59', strtotime($startDate));
        
        if ($user->isAdmin() || $user->isDirector() || $user->isDeputyDirector()) {
            $users = User::all();
        } elseif ($user->isDepartmentHead() || $user->isDeputyDepartmentHead()) {
            $users = User::where('department_id', $user->department_id)->get();
        } else {
            $users = collect([$user]);
        }
        
        $stats = collect();
        
        foreach ($users as $u) {
            $userTasks = $u->tasks()
                ->whereBetween('tasks.created_at', [$startDate, $endDate])
                ->get();
            
            $completed = $userTasks->filter(function($task) use ($u) {
                return $task->pivot->status === 'completed' && 
                       $task->pivot->approved_at && 
                       $task->pivot->completion_date <= $task->deadline;
            })->count();
            
            $late = $userTasks->filter(function($task) use ($u) {
                return $task->pivot->status === 'completed' && 
                       $task->pivot->approved_at && 
                       $task->pivot->completion_date > $task->deadline;
            })->count();
            
            $incomplete = $userTasks->filter(function($task) use ($u) {
                return $task->pivot->status !== 'completed' || !$task->pivot->approved_at;
            })->count();
            
            $stats->push([
                'user' => $u->name,
                'department' => $u->department->name ?? 'N/A',
                'completed' => $completed,
                'late' => $late,
                'incomplete' => $incomplete,
                'total' => $userTasks->count()
            ]);
        }
        
        return $stats;
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
}
