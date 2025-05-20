<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskExtension;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TaskExtensionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /**
     * Request task extension
     */
    public function request(Request $request, Task $task)
    {
        $user = Auth::user();
        
        // Check if user is assigned to the task
        if (!$task->users()->where('users.id', $user->id)->exists()) {
            return redirect()->route('tasks.show', $task)->with('error', 'Bạn không được phân công công việc này!');
        }
        
        return view('manager_task.task_extensions.request', compact('task'));
    }
    
    /**
     * Store extension request
     */
    public function store(Request $request, Task $task)
    {
        $user = Auth::user();
        
        // Check if user is assigned to the task
        if (!$task->users()->where('users.id', $user->id)->exists()) {
            return redirect()->route('tasks.show', $task)->with('error', 'Bạn không được phân công công việc này!');
        }
        
        $validated = $request->validate([
            'new_deadline' => 'required|date|after:now',
            'reason' => 'required|string|min:10',
        ], [
            'new_deadline.required' => 'Thời hạn mới là bắt buộc',
            'new_deadline.date' => 'Thời hạn mới phải là định dạng ngày tháng hợp lệ',
            'new_deadline.after' => 'Thời hạn mới phải sau thời gian hiện tại',
            'reason.required' => 'Lý do gia hạn là bắt buộc',
            'reason.min' => 'Lý do gia hạn phải có ít nhất 10 ký tự',
        ]);
        
        $extension = TaskExtension::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'requested_by' => $user->id,
            'requested_at' => now(),
            'new_deadline' => $validated['new_deadline'],
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);
        
        return redirect()->route('tasks.show', $task)->with('success', 'Yêu cầu gia hạn đã được gửi thành công!');
    }
    
    /**
     * Show all extension requests the user can manage
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Kiểm tra quyền xem yêu cầu gia hạn
        if (!($user->isAdmin() || $user->isDirector() || $user->isDeputyDirector() || 
              $user->isDepartmentHead() || $user->isDeputyDepartmentHead() || 
              $user->can_assign_task)) {
            return redirect()->route('tasks.index')->with('error', 'Bạn không có quyền xem mục này!');
        }
        
        // Xây dựng query
        $query = TaskExtension::query()->with(['task', 'user', 'user.department', 'approver']);
        
        // Phân quyền truy cập dữ liệu
        if ($user->isAdmin()) {
            // Admin sees all extensions
        } 
        elseif ($user->isDirector() || $user->isDeputyDirector()) {
            // Directors see tasks they created or from their direct reports
            $query->where(function($q) use ($user) {
                $q->whereHas('task', function($q1) use ($user) {
                    $q1->where('created_by', $user->id);
                })
                ->orWhereHas('user', function($q1) use ($user) {
                    $q1->where('department_id', $user->department_id);
                });
            });
        } 
        elseif ($user->isDepartmentHead()) {
            // Department heads see extensions from their department
            $query->where(function($q) use ($user) {
                $q->whereHas('task', function($q1) use ($user) {
                    $q1->where('created_by', $user->id);
                })
                ->orWhereHas('user', function($q1) use ($user) {
                    $q1->where('department_id', $user->department_id);
                });
            });
        } 
        elseif ($user->isDeputyDepartmentHead()) {
            // Deputy heads see extensions from staff in their department
            $query->where(function($q) use ($user) {
                $q->whereHas('task', function($q1) use ($user) {
                    $q1->where('created_by', $user->id);
                })
                ->orWhereHas('user', function($q1) use ($user) {
                    $q1->where('department_id', $user->department_id)
                       ->whereHas('role', function($q2) {
                           $q2->where('slug', 'staff');
                       });
                });
            });
        } 
        else {
            // Regular staff with assign_task permission see their own created tasks
            $query->whereHas('task', function($q) use ($user) {
                $q->where('created_by', $user->id);
            });
        }
        
        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('task', function($q1) use ($search) {
                    $q1->where('title', 'like', "%{$search}%");
                })
                ->orWhereHas('user', function($q1) use ($search) {
                    $q1->where('name', 'like', "%{$search}%");
                });
            });
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('department_id')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('requested_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('requested_at', '<=', $request->date_to);
        }
        
        // Sort and paginate
        $extensions = $query->orderBy('requested_at', 'desc')->paginate(10);
        
        // Get departments for filter
        if ($user->isDirector() || $user->isDeputyDirector()) {
            $departments = \App\Models\Department::orderBy('name')->get();
        } else if ($user->isDepartmentHead() || $user->isDeputyDepartmentHead()) {
            $departments = \App\Models\Department::where('id', $user->department_id)->get();
        } else {
            $departments = collect();
        }
        
        return view('manager_task.task_extensions.index', compact('extensions', 'departments'));
    }
    
    /**
     * Approve or reject extension request
     */
    public function respond(Request $request, TaskExtension $extension)
    {
        $user = Auth::user();
        
        // Check if user can approve/reject this extension
        if (!$this->canRespondToExtension($user, $extension)) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền phê duyệt yêu cầu gia hạn này!'
                ], 403);
            }
            
            return redirect()->route('task-extensions.index')
                ->with('error', 'Bạn không có quyền phê duyệt yêu cầu gia hạn này!');
        }
        
        // Validate the request
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,rejected',
            'rejection_reason' => 'required_if:status,rejected|nullable|string|min:10',
        ], [
            'status.required' => 'Trạng thái là bắt buộc',
            'status.in' => 'Trạng thái không hợp lệ',
            'rejection_reason.required_if' => 'Lý do từ chối là bắt buộc khi từ chối yêu cầu',
            'rejection_reason.min' => 'Lý do từ chối phải có ít nhất 10 ký tự',
        ]);
        
        if ($validator->fails()) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        $validated = $validator->validated();
        
        DB::beginTransaction();
        
        try {
            $extension->update([
                'status' => $validated['status'],
                'approved_by' => $user->id,
                'approved_at' => now(),
                'rejection_reason' => $validated['status'] === 'rejected' ? $validated['rejection_reason'] : null,
                'rejected_at' => $validated['status'] === 'rejected' ? now() : null,
            ]);
            
            // If approved, update the task deadline for this user
            if ($validated['status'] === 'approved') {
                Task::findOrFail($extension->task_id)
                    ->update(['deadline' => $extension->new_deadline]);
            }
            
            DB::commit();
            
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Yêu cầu gia hạn đã được ' . 
                        ($validated['status'] === 'approved' ? 'phê duyệt' : 'từ chối') . ' thành công!',
                    'status' => $validated['status'],
                ]);
            }
            
            return redirect()->route('task-extensions.index')->with(
                'success', 
                'Yêu cầu gia hạn đã được ' . ($validated['status'] === 'approved' ? 'phê duyệt' : 'từ chối') . ' thành công!'
            );
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Đã xảy ra lỗi: ' . $e->getMessage());
        }
    }
    
    /**
     * Check if user can respond to the extension request
     */
    private function canRespondToExtension(User $user, TaskExtension $extension)
    {
        // Admin has full rights
        if ($user->isAdmin()) {
            return true;
        }
        
        // Task creator can respond
        if ($extension->task->created_by === $user->id) {
            return true;
        }
        
        // Department head can respond to extensions from their department
        if ($user->isDepartmentHead() && $extension->user->department_id === $user->department_id) {
            // But not for tasks created by director/deputy director
            $taskCreator = User::find($extension->task->created_by);
            if ($taskCreator && ($taskCreator->isDirector() || $taskCreator->isDeputyDirector())) {
                return false;
            }
            return true;
        }
        
        // Deputy department head can only respond to staff extensions for tasks they created
        if ($user->isDeputyDepartmentHead()) {
            if ($extension->user->department_id === $user->department_id && 
                $extension->user->isStaff() && 
                $extension->task->created_by === $user->id) {
                return true;
            }
            return false;
        }
        
        return false;
    }
}