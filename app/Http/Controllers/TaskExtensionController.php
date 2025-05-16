<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskExtension;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        
        // Determine who should approve the extension
        $this->notifyApprovers($extension);
        
        return redirect()->route('tasks.show', $task)->with('success', 'Yêu cầu gia hạn đã được gửi thành công!');
    }
    
    /**
     * Show all extension requests the user can manage
     */
    public function index()
    {
        $user = Auth::user();
        
        // Different views based on user role
        if ($user->isAdmin()) {
            $extensions = TaskExtension::with('task', 'user', 'requester')->latest()->get();
        } elseif ($user->isDirector() || $user->isDeputyDirector()) {
            // Get all extension requests for tasks they created or from department heads
            $extensions = TaskExtension::whereHas('task', function($query) use ($user) {
                $query->where('created_by', $user->id);
            })
            ->orWhereHas('user', function($query) {
                $query->whereHas('role', function($q) {
                    $q->whereIn('slug', ['department-head', 'deputy-department-head']);
                });
            })
            ->with('task', 'user', 'requester')
            ->latest()
            ->get();
        } elseif ($user->isDepartmentHead()) {
            // Department heads see extensions from their department
            $departmentId = $user->department_id;
            
            $extensions = TaskExtension::whereHas('user', function($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            })
            ->with('task', 'user', 'requester')
            ->latest()
            ->get();
        } elseif ($user->isDeputyDepartmentHead()) {
            // Deputy heads see extensions from staff in their department
            $departmentId = $user->department_id;
            
            $extensions = TaskExtension::whereHas('user', function($query) use ($departmentId) {
                $query->where('department_id', $departmentId)
                    ->whereHas('role', function($q) {
                        $q->where('slug', 'staff');
                    });
            })
            ->with('task', 'user', 'requester')
            ->latest()
            ->get();
        } else {
            // Regular staff see only their own extensions
            $extensions = TaskExtension::where('user_id', $user->id)
                ->with('task', 'requester', 'approver')
                ->latest()
                ->get();
        }
        
        return view('manager_task.task_extensions.index', compact('extensions'));
    }
    
    /**
     * Approve or reject extension request
     */
    public function respond(Request $request, TaskExtension $extension)
    {
        $user = Auth::user();
        
        // Check if user can approve/reject this extension
        $canRespond = $this->canRespondToExtension($user, $extension);
        
        if (!$canRespond) {
            return redirect()->route('task-extensions.index')->with('error', 'Bạn không có quyền phê duyệt yêu cầu gia hạn này!');
        }
        
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'rejection_reason' => 'required_if:status,rejected|nullable|string',
        ]);
        
        DB::beginTransaction();
        
        try {
            $extension->update([
                'status' => $validated['status'],
                'approved_by' => $user->id,
                'approved_at' => now(),
                'rejection_reason' => $validated['rejection_reason'],
            ]);
            
            // If approved, update the task deadline for this user
            if ($validated['status'] === 'approved') {
                $extension->task->users()->updateExistingPivot($extension->user_id, [
                    'deadline' => $extension->new_deadline,
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('task-extensions.index')->with('success', 'Yêu cầu gia hạn đã được ' . 
                ($validated['status'] === 'approved' ? 'phê duyệt' : 'từ chối') . ' thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Đã xảy ra lỗi: ' . $e->getMessage());
        }
    }
    
    /**
     * Check if user can respond to the extension request
     */
    private function canRespondToExtension(User $user, TaskExtension $extension)
    {
        // Admin can respond to all extensions
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
        
        // Deputy head can respond to extensions from staff in their department
        if ($user->isDeputyDepartmentHead() && 
            $extension->user->department_id === $user->department_id && 
            $extension->user->isStaff()) {
            // But not for tasks created by director/deputy director or department head
            $taskCreator = User::find($extension->task->created_by);
            if ($taskCreator && 
                ($taskCreator->isDirector() || $taskCreator->isDeputyDirector() || $taskCreator->isDepartmentHead())) {
                return false;
            }
            return true;
        }
        
        return false;
    }
    
    /**
     * Notify the appropriate users about the extension request
     */
    private function notifyApprovers(TaskExtension $extension)
    {
        // In a real application, this would send notifications to the appropriate users
        // based on the hierarchical approval flow described in the requirements
        
        // For now, we'll just implement the logic to determine who should approve
        
        $taskCreator = User::find($extension->task->created_by);
        $requester = User::find($extension->requested_by);
        
        // If the task was created by director or deputy director, they approve
        if ($taskCreator->isDirector() || $taskCreator->isDeputyDirector()) {
            // Send notification to task creator
            // Notification::send($taskCreator, new ExtensionRequestNotification($extension));
        } 
        // If task was created by department head, they approve unless...
        elseif ($taskCreator->isDepartmentHead()) {
            // If the request is from a deputy head/staff, department head approves
            if ($requester->isDeputyDepartmentHead() || $requester->isStaff()) {
                // Send notification to department head
                // Notification::send($taskCreator, new ExtensionRequestNotification($extension));
            }
        }
        // If task was created by deputy department head
        elseif ($taskCreator->isDeputyDepartmentHead()) {
            // If requester is staff, deputy head approves
            if ($requester->isStaff()) {
                // Send notification to deputy head
                // Notification::send($taskCreator, new ExtensionRequestNotification($extension));
            }
        }
        
        // For tasks initially assigned to departments
        if ($extension->task->for_departments) {
            $departmentHead = User::whereHas('role', function($query) {
                $query->where('slug', 'department-head');
            })
            ->where('department_id', $requester->department_id)
            ->first();
            
            if ($departmentHead) {
                // Send notification to department head
                // Notification::send($departmentHead, new ExtensionRequestNotification($extension));
            }
        }
    }
}
