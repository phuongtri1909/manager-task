@extends('manager_task.layouts.partials.sidebar')

@section('title', 'Chi tiết nhiệm vụ')

@section('main-content')
<div class="category-container">
    <!-- Breadcrumb -->
    <div class="content-breadcrumb">
        <ol class="breadcrumb-list">
            <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}">Nhiệm vụ</a></li>
            <li class="breadcrumb-item current">Chi tiết</li>
        </ol>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="content-card">
                <div class="card-top">
                    <div class="card-title">
                        <i class="fas fa-clipboard-check icon-title"></i>
                        <h5>{{ $task->title }}</h5>
                    </div>
                    <div class="task-status-indicator">
                        @if(now() > $task->deadline && !($task->users->where('id', Auth::id())->first()?->pivot->status === 'completed'))
                            <span class="task-badge task-badge-overdue">
                                <i class="far fa-clock me-1"></i>Quá hạn {{ now()->diffInDays($task->deadline) }} ngày
                            </span>
                        @elseif(now()->diffInDays($task->deadline) <= 3)
                            <span class="task-badge task-badge-warning">
                                <i class="far fa-clock me-1"></i>Còn {{ now()->diffInDays($task->deadline) }} ngày
                            </span>
                        @else
                            <span class="task-badge">
                                <i class="far fa-clock me-1"></i>Còn {{ now()->diffInDays($task->deadline) }} ngày
                            </span>
                        @endif
                    </div>
                </div>
                
                <div class="card-content">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                            <button type="button" class="alert-close" aria-label="Close">&times;</button>
                        </div>
                    @endif
                    
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                            <button type="button" class="alert-close" aria-label="Close">&times;</button>
                        </div>
                    @endif

                    <div class="task-meta-grid">
                        <div class="task-meta-item animate-slide-up" style="--delay: 0.1s">
                            <div class="task-meta-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="task-meta-content">
                                <div class="task-meta-label">Người tạo</div>
                                <div class="task-meta-value">{{ $task->creator->name ?? 'N/A' }}</div>
                            </div>
                        </div>
                        
                        <div class="task-meta-item animate-slide-up" style="--delay: 0.2s">
                            <div class="task-meta-icon">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <div class="task-meta-content">
                                <div class="task-meta-label">Ngày tạo</div>
                                <div class="task-meta-value">{{ $task->created_at->format('d/m/Y H:i') }}</div>
                            </div>
                        </div>
                        
                        <div class="task-meta-item animate-slide-up" style="--delay: 0.3s">
                            <div class="task-meta-icon">
                                <i class="fas fa-hourglass-end"></i>
                            </div>
                            <div class="task-meta-content">
                                <div class="task-meta-label">Thời hạn</div>
                                <div class="task-meta-value">{{ $task->deadline->format('d/m/Y H:i') }}</div>
                            </div>
                        </div>
                        
                        <div class="task-meta-item animate-slide-up" style="--delay: 0.4s">
                            <div class="task-meta-icon">
                                <i class="fas fa-tag"></i>
                            </div>
                            <div class="task-meta-content">
                                <div class="task-meta-label">Loại nhiệm vụ</div>
                                <div class="task-meta-value">{{ $task->for_departments ? 'Nhiệm vụ phòng ban' : 'Nhiệm vụ cá nhân' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="task-section animate-slide-up" style="--delay: 0.5s">
                        <div class="task-section-header">
                            <i class="fas fa-align-left"></i>
                            <h6>Mô tả nhiệm vụ</h6>
                        </div>
                        <div class="task-section-content">
                            <div class="task-description">
                                {!! nl2br(e($task->description)) !!}
                            </div>
                        </div>
                    </div>

                    @if($canUpdateStatus)
                        <div class="task-section animate-slide-up" style="--delay: 0.6s">
                            <div class="task-section-header">
                                <i class="fas fa-sync-alt"></i>
                                <h6>Cập nhật trạng thái</h6>
                            </div>
                            <div class="task-section-content">
                                <form action="{{ route('tasks.update-status', $task) }}" method="POST" class="status-update-form">
                                    @csrf
                                    @method('PATCH')
                                    <div class="form-flex">
                                        <div class="form-flex-main">
                                            <select name="status" class="form-select">
                                                <option value="pending" {{ $task->users->where('id', Auth::id())->first()->pivot->status === 'pending' ? 'selected' : '' }}>Chưa thực hiện</option>
                                                <option value="in_progress" {{ $task->users->where('id', Auth::id())->first()->pivot->status === 'in_progress' ? 'selected' : '' }}>Đang thực hiện</option>
                                                <option value="completed" {{ $task->users->where('id', Auth::id())->first()->pivot->status === 'completed' ? 'selected' : '' }}>Đã hoàn thành</option>
                                            </select>
                                        </div>
                                        <div>
                                            <button type="submit" class="btn-submit">
                                                <i class="fas fa-save"></i> Cập nhật
                                            </button>
                                        </div>
                                    </div>
                                </form>
                                
                                <div class="task-extension-action">
                                    <a href="{{ route('task-extensions.request', $task) }}" class="btn-extension">
                                        <i class="fas fa-clock"></i> Xin gia hạn
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                
                <div class="card-footer">
                    <a href="{{ route('tasks.index') }}" class="btn-back">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                    
                    @if($canEdit)
                        <div class="task-actions">
                            <a href="{{ route('tasks.edit', $task) }}" class="btn-edit">
                                <i class="fas fa-edit"></i> Chỉnh sửa
                            </a>
                            <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn-delete" onclick="confirmDelete(event, this.form)">
                                    <i class="fas fa-trash"></i> Xóa
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            @if($task->departments->count() > 0)
                <div class="content-card side-card animate-slide-up" style="--delay: 0.7s">
                    <div class="card-top">
                        <div class="card-title">
                            <i class="fas fa-building icon-title"></i>
                            <h5>Phòng ban được giao</h5>
                        </div>
                    </div>
                    <div class="card-content">
                        <ul class="department-list">
                            @foreach($task->departments as $index => $department)
                                <li class="department-item animate-slide-up" style="--delay: {{ 0.7 + ($index * 0.1) }}s">
                                    <div class="department-name">
                                        <i class="fas fa-sitemap"></i>
                                        <span>{{ $department->name }}</span>
                                    </div>
                                    <span class="status-badge {{ 
                                        $department->pivot->status === 'pending' ? 'status-inactive' : 
                                        ($department->pivot->status === 'in_progress' ? 'bg-info text-white' : 'status-active') 
                                    }}">
                                        {{ 
                                            $department->pivot->status === 'pending' ? 'Chưa thực hiện' : 
                                            ($department->pivot->status === 'in_progress' ? 'Đang thực hiện' : 'Hoàn thành')
                                        }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <div class="content-card side-card animate-slide-up" style="--delay: 0.8s">
                <div class="card-top">
                    <div class="card-title">
                        <i class="fas fa-users icon-title"></i>
                        <h5>Người thực hiện</h5>
                    </div>
                </div>
                <div class="card-content">
                    <ul class="assignee-list">
                        @forelse($task->users as $index => $user)
                            <li class="assignee-item animate-slide-up" style="--delay: {{ 0.8 + ($index * 0.1) }}s">
                                <div class="assignee-main">
                                    <div class="assignee-info">
                                        <div class="assignee-avatar">
                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=random" alt="{{ $user->name }}">
                                        </div>
                                        <div class="assignee-details">
                                            <div class="assignee-name">{{ $user->name }}</div>
                                            <div class="assignee-dept">
                                                <i class="fas fa-sitemap"></i> {{ $user->department->name ?? 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                    <span class="status-badge {{ 
                                        $user->pivot->status === 'pending' ? 'status-inactive' : 
                                        ($user->pivot->status === 'in_progress' ? 'bg-info text-white' : 'status-active') 
                                    }}">
                                        {{ 
                                            $user->pivot->status === 'pending' ? 'Chưa thực hiện' : 
                                            ($user->pivot->status === 'in_progress' ? 'Đang thực hiện' : 'Hoàn thành')
                                        }}
                                    </span>
                                </div>
                                
                                @if($user->pivot->status === 'completed')
                                    <div class="completion-info">
                                        <div class="completion-time">
                                            <i class="fas fa-check-circle"></i>
                                            <span>Hoàn thành lúc: {{ $user->pivot->completion_date ? \Carbon\Carbon::parse($user->pivot->completion_date)->format('d/m/Y H:i') : 'N/A' }}</span>
                                        </div>
                                        
                                        @if($user->pivot->approved_at)
                                            <div class="approval-info approved">
                                                <i class="fas fa-check"></i>
                                                <span>Đã xác nhận bởi {{ App\Models\User::find($user->pivot->approved_by)->name ?? 'N/A' }}</span>
                                            </div>
                                        @else
                                            @if(Auth::user()->isAdmin() || Auth::user()->id === $task->created_by || 
                                               (Auth::user()->isDepartmentHead() && $user->department_id === Auth::user()->department_id) ||
                                               (Auth::user()->isDeputyDepartmentHead() && $user->department_id === Auth::user()->department_id && $user->isStaff()))
                                                <form action="{{ route('tasks.approve-completion', ['task' => $task->id, 'assignee' => $user->id]) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn-approve">
                                                        <i class="fas fa-check"></i> Xác nhận hoàn thành
                                                    </button>
                                                </form>
                                            @else
                                                <div class="approval-info pending">
                                                    <i class="fas fa-clock"></i>
                                                    <span>Chờ xác nhận</span>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                @endif
                                
                                @if($user->pivot->viewed_at)
                                    <div class="viewed-info">
                                        <i class="fas fa-eye"></i>
                                        <span>Đã xem lúc {{ \Carbon\Carbon::parse($user->pivot->viewed_at)->format('d/m/Y H:i') }}</span>
                                    </div>
                                @endif
                            </li>
                        @empty
                            <li class="empty-item">Không có người được giao</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <div class="content-card side-card animate-slide-up" style="--delay: 0.9s">
                <div class="card-top">
                    <div class="card-title">
                        <i class="fas fa-clock icon-title"></i>
                        <h5>Yêu cầu gia hạn</h5>
                    </div>
                </div>
                <div class="card-content">
                    <ul class="extension-list">
                        @forelse($task->extensions as $index => $extension)
                            <li class="extension-item animate-slide-up" style="--delay: {{ 0.9 + ($index * 0.1) }}s">
                                <div class="extension-header">
                                    <div class="extension-user">
                                        <i class="fas fa-user"></i>
                                        <span>{{ $extension->user->name }}</span>
                                    </div>
                                    <span class="extension-status {{ 
                                        $extension->status === 'pending' ? 'status-pending' : 
                                        ($extension->status === 'approved' ? 'status-approved' : 'status-rejected') 
                                    }}">
                                        {{ 
                                            $extension->status === 'pending' ? 'Đang chờ' : 
                                            ($extension->status === 'approved' ? 'Đã duyệt' : 'Từ chối')
                                        }}
                                    </span>
                                </div>
                                
                                <div class="extension-details">
                                    <div class="extension-deadline">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span>Thời hạn mới: <strong>{{ \Carbon\Carbon::parse($extension->new_deadline)->format('d/m/Y H:i') }}</strong></span>
                                    </div>
                                    
                                    <div class="extension-reason">
                                        <i class="fas fa-comment"></i>
                                        <span>Lý do: {{ $extension->reason }}</span>
                                    </div>
                                    
                                    <div class="extension-time">
                                        <i class="fas fa-history"></i>
                                        <span>Yêu cầu lúc: {{ $extension->requested_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                    
                                    @if($extension->status !== 'pending')
                                        <div class="extension-approver {{ $extension->status === 'approved' ? 'approved' : 'rejected' }}">
                                            <i class="fas {{ $extension->status === 'approved' ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                                            <span>{{ $extension->status === 'approved' ? 'Duyệt' : 'Từ chối' }} bởi: <strong>{{ $extension->approver->name ?? 'N/A' }}</strong></span>
                                        </div>
                                        
                                        @if($extension->status === 'rejected' && $extension->rejection_reason)
                                            <div class="extension-rejection">
                                                <i class="fas fa-info-circle"></i>
                                                <span>Lý do từ chối: {{ $extension->rejection_reason }}</span>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </li>
                        @empty
                            <li class="empty-item">Không có yêu cầu gia hạn</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@push('styles')
<style>
    /* Task Detail Styles */
    .task-status-indicator {
        display: flex;
        align-items: center;
    }
    
    .task-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.4rem 0.8rem;
        border-radius: 30px;
        font-size: 0.8rem;
        font-weight: 600;
        background-color: var(--info);
        color: white;
    }
    
    .task-badge i {
        margin-right: 0.3rem;
    }
    
    .task-badge-overdue {
        background-color: var(--danger);
        animation: pulse 2s infinite;
    }
    
    .task-badge-warning {
        background-color: var(--warning);
        color: var(--dark);
    }
    
    /* Task Metadata Grid */
    .task-meta-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }
    
    .task-meta-item {
        display: flex;
        align-items: flex-start;
        background-color: #f8f9fa;
        border-radius: 0.5rem;
        padding: 1rem;
        transition: all 0.3s ease;
    }
    
    .task-meta-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    .task-meta-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--primary);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        margin-right: 1rem;
    }
    
    .task-meta-content {
        flex: 1;
    }
    
    .task-meta-label {
        font-size: 0.85rem;
        color: #6c757d;
        margin-bottom: 0.3rem;
    }
    
    .task-meta-value {
        font-weight: 600;
        font-size: 1rem;
    }
    
    /* Task Sections */
    .task-section {
        margin-bottom: 2rem;
        border: 1px solid rgba(0, 0, 0, 0.05);
        border-radius: 0.5rem;
        overflow: hidden;
    }
    
    .task-section-header {
        background-color: #f8f9fa;
        padding: 0.8rem 1rem;
        display: flex;
        align-items: center;
        font-weight: 600;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .task-section-header i {
        margin-right: 0.5rem;
        color: var(--primary);
    }
    
    .task-section-header h6 {
        margin: 0;
        font-size: 1rem;
    }
    
    .task-section-content {
        padding: 1rem;
    }
    
    .task-description {
        line-height: 1.6;
        color: #333;
    }
    
    /* Form Elements */
    .status-update-form {
        margin-bottom: 1rem;
    }
    
    .form-flex {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .form-flex-main {
        flex: 1;
    }
    
    .form-select {
        width: 100%;
        padding: 0.5rem;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        background-color: white;
        font-size: 1rem;
        transition: border-color 0.3s ease;
    }
    
    .form-select:focus {
        border-color: var(--primary);
        outline: 0;
        box-shadow: 0 0 0 0.25rem rgba(52, 144, 220, 0.25);
    }
    
    .btn-submit {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 1rem;
        background-color: var(--primary);
        color: white;
        border: none;
        border-radius: 0.25rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .btn-submit:hover {
        background-color: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }
    
    .btn-submit i {
        margin-right: 0.5rem;
    }
    
    .task-extension-action {
        margin-top: 1rem;
    }
    
    .btn-extension {
        display: inline-flex;
        align-items: center;
        background-color: transparent;
        color: var(--primary);
        border: 1px solid var(--primary);
        padding: 0.5rem 1rem;
        border-radius: 0.25rem;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    
    .btn-extension:hover {
        background-color: var(--primary);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }
    
    .btn-extension i {
        margin-right: 0.5rem;
    }
    
    /* Card Footer */
    .card-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem;
        background-color: #f8f9fa;
        border-top: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .btn-back {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 1rem;
        background-color: #e9ecef;
        color: #495057;
        border: none;
        border-radius: 0.25rem;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    
    .btn-back:hover {
        background-color: #dee2e6;
        color: #212529;
        transform: translateY(-2px);
    }
    
    .btn-back i {
        margin-right: 0.5rem;
    }
    
    .task-actions {
        display: flex;
        gap: 0.5rem;
    }
    
    .btn-edit {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 1rem;
        background-color: var(--primary);
        color: white;
        border: none;
        border-radius: 0.25rem;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    
    .btn-edit:hover {
        background-color: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }
    
    .btn-edit i {
        margin-right: 0.5rem;
    }
    
    .btn-delete {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 1rem;
        background-color: var(--danger);
        color: white;
        border: none;
        border-radius: 0.25rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .btn-delete:hover {
        background-color: #e53935;
        transform: translateY(-2px);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }
    
    .btn-delete i {
        margin-right: 0.5rem;
    }
    
    /* Side Cards */
    .side-card {
        margin-bottom: 1.5rem;
    }
    
    /* Department List */
    .department-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .department-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.8rem 0;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }
    
    .department-item:last-child {
        border-bottom: none;
    }
    
    .department-item:hover {
        transform: translateX(5px);
        background-color: rgba(0, 0, 0, 0.01);
    }
    
    .department-name {
        display: flex;
        align-items: center;
    }
    
    .department-name i {
        color: var(--primary);
        margin-right: 0.5rem;
    }
    
    /* Assignee List */
    .assignee-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .assignee-item {
        padding: 1rem 0;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }
    
    .assignee-item:last-child {
        border-bottom: none;
    }
    
    .assignee-item:hover {
        background-color: rgba(0, 0, 0, 0.01);
    }
    
    .assignee-main {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.8rem;
    }
    
    .assignee-info {
        display: flex;
        align-items: center;
    }
    
    .assignee-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        overflow: hidden;
        margin-right: 0.8rem;
        border: 2px solid rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }
    
    .assignee-item:hover .assignee-avatar {
        border-color: var(--primary);
        transform: scale(1.05);
    }
    
    .assignee-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .assignee-details {
        display: flex;
        flex-direction: column;
    }
    
    .assignee-name {
        font-weight: 600;
        margin-bottom: 0.2rem;
    }
    
    .assignee-dept {
        font-size: 0.8rem;
        color: #6c757d;
        display: flex;
        align-items: center;
    }
    
    .assignee-dept i {
        margin-right: 0.3rem;
        font-size: 0.7rem;
    }
    
    .completion-info {
        padding: 0.8rem;
        background-color: #f8f9fa;
        border-radius: 0.5rem;
        margin-top: 0.5rem;
        font-size: 0.85rem;
        animation: fadeIn 0.5s ease;
    }
    
    .completion-time {
        display: flex;
        align-items: center;
        color: #6c757d;
        margin-bottom: 0.5rem;
    }
    
    .completion-time i {
        color: var(--success);
        margin-right: 0.5rem;
    }
    
    .approval-info {
        display: flex;
        align-items: center;
        padding: 0.4rem 0.8rem;
        border-radius: 30px;
        font-size: 0.8rem;
    }
    
    .approval-info.approved {
        background-color: rgba(16, 185, 129, 0.1);
        color: var(--success);
    }
    
    .approval-info.pending {
        background-color: rgba(245, 158, 11, 0.1);
        color: var(--warning);
    }
    
    .approval-info i {
        margin-right: 0.5rem;
    }
    
    .btn-approve {
        display: inline-flex;
        align-items: center;
        padding: 0.4rem 0.8rem;
        background-color: var(--success);
        color: white;
        border: none;
        border-radius: 0.25rem;
        font-size: 0.8rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .btn-approve:hover {
        background-color: #059669;
        transform: translateY(-2px);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }
    
    .btn-approve i {
        margin-right: 0.5rem;
    }
    
    .viewed-info {
        display: flex;
        align-items: center;
        font-size: 0.8rem;
        color: #6c757d;
        margin-top: 0.5rem;
    }
    
    .viewed-info i {
        margin-right: 0.5rem;
        color: var(--info);
    }
    
    /* Extension List */
    .extension-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .extension-item {
        padding: 1rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }
    
    .extension-item:last-child {
        border-bottom: none;
    }
    
    .extension-item:hover {
        background-color: rgba(0, 0, 0, 0.01);
        transform: translateY(-3px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
    }
    
    .extension-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.8rem;
    }
    
    .extension-user {
        display: flex;
        align-items: center;
        font-weight: 600;
    }
    
    .extension-user i {
        margin-right: 0.5rem;
        color: var(--primary);
    }
    
    .extension-status {
        padding: 0.3rem 0.6rem;
        border-radius: 30px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .extension-status.status-pending {
        background-color: rgba(245, 158, 11, 0.1);
        color: var(--warning);
    }
    
    .extension-status.status-approved {
        background-color: rgba(16, 185, 129, 0.1);
        color: var(--success);
    }
    
    .extension-status.status-rejected {
        background-color: rgba(239, 68, 68, 0.1);
        color: var(--danger);
    }
    
    .extension-details {
        background-color: #f8f9fa;
        padding: 0.8rem;
        border-radius: 0.5rem;
        font-size: 0.85rem;
    }
    
    .extension-details > div {
        margin-bottom: 0.5rem;
        display: flex;
        align-items: flex-start;
    }
    
    .extension-details > div:last-child {
        margin-bottom: 0;
    }
    
    .extension-details i {
        margin-right: 0.5rem;
        margin-top: 0.2rem;
    }
    
    .extension-deadline i {
        color: var(--primary);
    }
    
    .extension-reason i {
        color: var(--info);
    }
    
    .extension-time i {
        color: var(--secondary);
    }
    
    .extension-approver {
        color: #6c757d;
    }
    
    .extension-approver.approved i {
        color: var(--success);
    }
    
    .extension-approver.rejected i {
        color: var(--danger);
    }
    
    .extension-rejection {
        color: var(--danger);
    }
    
    .empty-item {
        padding: 1.5rem;
        text-align: center;
        color: #6c757d;
        font-style: italic;
    }
    
    /* Animation */
    .animate-slide-up {
        opacity: 0;
        transform: translateY(20px);
        animation: slideUp 0.5s ease forwards;
        animation-delay: var(--delay, 0s);
    }
    
    @keyframes slideUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4);
        }
        70% {
            box-shadow: 0 0 0 10px rgba(239, 68, 68, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
        }
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Responsive Adjustments */
    @media (max-width: 767.98px) {
        .task-meta-grid {
            grid-template-columns: 1fr;
        }
        
        .form-flex {
            flex-direction: column;
            align-items: stretch;
        }
        
        .card-footer, .task-actions {
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .btn-back, .btn-edit, .btn-delete {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Alert close functionality
        const closeButtons = document.querySelectorAll('.alert-close');
        closeButtons.forEach(button => {
            button.addEventListener('click', function() {
                const alert = this.closest('.alert');
                alert.classList.remove('show');
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 150);
            });
        });
        
        // Auto hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.classList.remove('show');
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 150);
            });
        }, 5000);
    });
    
    // Confirm delete function with enhanced alert
    function confirmDelete(event, form) {
        event.preventDefault();
        
        // Use SweetAlert if available, otherwise fallback to confirm
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Bạn có chắc chắn?',
                text: "Bạn không thể hoàn tác hành động này!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Xóa',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        } else {
            if (confirm('Bạn có chắc chắn muốn xóa nhiệm vụ này?')) {
                form.submit();
            }
        }
    }
</script>
@endpush