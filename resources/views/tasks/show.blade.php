@extends('layouts.partials.sidebar')

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
                    <div class="card-top d-flex justify-content-between align-items-center">
                        <div class="card-title">
                            <i class="fas fa-clipboard-check icon-title"></i>
                            <h5>{{ $task->title }}</h5>
                        </div>
                        <div class="task-deadline">
                            @if(now() > $task->deadline && !($task->users->where('id', Auth::id())->first()?->pivot->status === 'completed'))
                                <span class="badge bg-danger pulse-badge">
                                    <i class="far fa-clock me-1"></i>Quá hạn {{ now()->diffInDays($task->deadline) }} ngày
                                </span>
                            @elseif(now()->diffInDays($task->deadline) <= 3)
                                <span class="badge bg-warning text-dark">
                                    <i class="far fa-clock me-1"></i>Còn {{ now()->diffInDays($task->deadline) }} ngày
                                </span>
                            @else
                                <span class="badge bg-info">
                                    <i class="far fa-clock me-1"></i>Còn {{ now()->diffInDays($task->deadline) }} ngày
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="card-main">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        
                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="task-meta mb-4">
                            <div class="task-meta-item">
                                <div class="task-meta-label"><i class="fas fa-user me-1"></i>Người tạo</div>
                                <div class="task-meta-value">{{ $task->creator->name ?? 'N/A' }}</div>
                            </div>
                            <div class="task-meta-item">
                                <div class="task-meta-label"><i class="fas fa-calendar me-1"></i>Ngày tạo</div>
                                <div class="task-meta-value">{{ $task->created_at->format('d/m/Y H:i') }}</div>
                            </div>
                            <div class="task-meta-item">
                                <div class="task-meta-label"><i class="fas fa-hourglass-end me-1"></i>Thời hạn</div>
                                <div class="task-meta-value">{{ $task->deadline->format('d/m/Y H:i') }}</div>
                            </div>
                            <div class="task-meta-item">
                                <div class="task-meta-label"><i class="fas fa-tag me-1"></i>Loại nhiệm vụ</div>
                                <div class="task-meta-value">{{ $task->for_departments ? 'Nhiệm vụ phòng ban' : 'Nhiệm vụ cá nhân' }}</div>
                            </div>
                        </div>

                        <div class="form-section">
                            <div class="form-section-title"><i class="fas fa-align-left me-2"></i>Mô tả nhiệm vụ</div>
                            <div class="task-description">
                                {!! nl2br(e($task->description)) !!}
                            </div>
                        </div>

                        @if($canUpdateStatus)
                            <div class="form-section">
                                <div class="form-section-title"><i class="fas fa-sync-alt me-2"></i>Cập nhật trạng thái</div>
                                <form action="{{ route('tasks.update-status', $task) }}" method="POST" class="task-update-form">
                                    @csrf
                                    @method('PATCH')
                                    <div class="row align-items-center">
                                        <div class="col-md-8 mb-2 mb-md-0">
                                            <select name="status" class="form-select">
                                                <option value="pending" {{ $task->users->where('id', Auth::id())->first()->pivot->status === 'pending' ? 'selected' : '' }}>Chưa thực hiện</option>
                                                <option value="in_progress" {{ $task->users->where('id', Auth::id())->first()->pivot->status === 'in_progress' ? 'selected' : '' }}>Đang thực hiện</option>
                                                <option value="completed" {{ $task->users->where('id', Auth::id())->first()->pivot->status === 'completed' ? 'selected' : '' }}>Đã hoàn thành</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <button type="submit" class="form-button">
                                                <i class="fas fa-save me-1"></i>Cập nhật
                                            </button>
                                        </div>
                                    </div>
                                </form>
                                
                                <div class="mt-3">
                                    <a href="{{ route('task-extensions.request', $task) }}" class="btn-extension">
                                        <i class="fas fa-clock me-1"></i> Xin gia hạn
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="card-bottom d-flex justify-content-between">
                        <a href="{{ route('tasks.index') }}" class="card-bottom-button back">
                            <i class="fas fa-arrow-left me-1"></i>Quay lại
                        </a>
                        
                        @if($canEdit)
                            <div class="task-actions">
                                <a href="{{ route('tasks.edit', $task) }}" class="card-bottom-button edit">
                                    <i class="fas fa-edit me-1"></i>Chỉnh sửa
                                </a>
                                <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="d-inline ms-2">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="card-bottom-button delete" onclick="confirmDelete(event, this.form)">
                                        <i class="fas fa-trash me-1"></i>Xóa
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                @if($task->departments->count() > 0)
                    <div class="content-side-card">
                        <div class="side-card-top">
                            <div class="side-card-title">
                                <i class="fas fa-building icon-title"></i>
                                <h5>Phòng ban được giao</h5>
                            </div>
                        </div>
                        <div class="side-card-main">
                            <ul class="list-group list-group-flush department-list">
                                @foreach($task->departments as $index => $department)
                                    <li class="list-group-item d-flex justify-content-between align-items-center department-item" style="animation-delay: {{ 0.1 * $index }}s">
                                        <div>
                                            <i class="fas fa-sitemap text-primary me-2"></i>
                                            {{ $department->name }}
                                        </div>
                                        <span class="badge rounded-pill {{ $department->pivot->status === 'pending' ? 'bg-secondary' : ($department->pivot->status === 'in_progress' ? 'bg-primary' : 'bg-success') }}">
                                            {{ $department->pivot->status === 'pending' ? 'Chưa thực hiện' : ($department->pivot->status === 'in_progress' ? 'Đang thực hiện' : 'Đã hoàn thành') }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <div class="content-side-card">
                    <div class="side-card-top">
                        <div class="side-card-title">
                            <i class="fas fa-users icon-title"></i>
                            <h5>Người được giao</h5>
                        </div>
                    </div>
                    <div class="side-card-main">
                        <ul class="list-group list-group-flush assignee-list">
                            @forelse($task->users as $index => $user)
                                <li class="list-group-item assignee-item" style="animation-delay: {{ 0.1 * $index }}s">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="user-name">{{ $user->name }}</span>
                                            <small class="d-block text-muted">
                                                <i class="fas fa-sitemap me-1"></i>{{ $user->department->name ?? 'N/A' }}
                                            </small>
                                        </div>
                                        <span class="badge rounded-pill {{ $user->pivot->status === 'pending' ? 'bg-secondary' : ($user->pivot->status === 'in_progress' ? 'bg-primary' : 'bg-success') }}">
                                            {{ $user->pivot->status === 'pending' ? 'Chưa thực hiện' : ($user->pivot->status === 'in_progress' ? 'Đang thực hiện' : 'Đã hoàn thành') }}
                                        </span>
                                    </div>
                                    
                                    @if($user->pivot->status === 'completed')
                                        <div class="completion-info mt-2">
                                            <small class="text-muted">
                                                <i class="fas fa-check-circle me-1"></i>Hoàn thành lúc: {{ $user->pivot->completion_date ? \Carbon\Carbon::parse($user->pivot->completion_date)->format('d/m/Y H:i') : 'N/A' }}
                                            </small>
                                            
                                            @if($user->pivot->approved_at)
                                                <div class="badge bg-success mt-1 approval-badge">
                                                    <i class="fas fa-check me-1"></i>Đã xác nhận bởi {{ App\Models\User::find($user->pivot->approved_by)->name ?? 'N/A' }}
                                                </div>
                                            @else
                                                @if(Auth::user()->isAdmin() || Auth::user()->id === $task->created_by || 
                                                   (Auth::user()->isDepartmentHead() && $user->department_id === Auth::user()->department_id) ||
                                                   (Auth::user()->isDeputyDepartmentHead() && $user->department_id === Auth::user()->department_id && $user->isStaff()))
                                                    <form action="{{ route('tasks.approve-completion', ['task' => $task->id, 'assignee' => $user->id]) }}" method="POST" class="mt-1">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-sm btn-success">
                                                            <i class="fas fa-check me-1"></i>Xác nhận hoàn thành
                                                        </button>
                                                    </form>
                                                @else
                                                    <div class="badge bg-warning text-dark mt-1">
                                                        <i class="fas fa-clock me-1"></i>Chờ xác nhận
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                    @endif
                                    
                                    @if($user->pivot->viewed_at)
                                        <small class="text-muted d-block mt-1 viewed-status">
                                            <i class="fas fa-eye me-1"></i>Đã xem lúc {{ \Carbon\Carbon::parse($user->pivot->viewed_at)->format('d/m/Y H:i') }}
                                        </small>
                                    @endif
                                </li>
                            @empty
                                <li class="list-group-item text-center">Không có người được giao</li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                <div class="content-side-card">
                    <div class="side-card-top">
                        <div class="side-card-title">
                            <i class="fas fa-clock icon-title"></i>
                            <h5>Yêu cầu gia hạn</h5>
                        </div>
                    </div>
                    <div class="side-card-main">
                        <ul class="list-group list-group-flush extension-list">
                            @forelse($task->extensions as $index => $extension)
                                <li class="list-group-item extension-item" style="animation-delay: {{ 0.1 * $index }}s">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="fw-bold">
                                            <i class="fas fa-user me-1"></i>{{ $extension->user->name }}
                                        </div>
                                        <span class="badge {{ $extension->status === 'pending' ? 'bg-warning text-dark' : ($extension->status === 'approved' ? 'bg-success' : 'bg-danger') }}">
                                            {{ $extension->status === 'pending' ? 'Đang chờ' : ($extension->status === 'approved' ? 'Đã duyệt' : 'Từ chối') }}
                                        </span>
                                    </div>
                                    <div class="extension-details mt-2">
                                        <div class="extension-info">
                                            <i class="fas fa-calendar-alt text-primary me-1"></i>
                                            <small>Thời hạn mới: <strong>{{ \Carbon\Carbon::parse($extension->new_deadline)->format('d/m/Y H:i') }}</strong></small>
                                        </div>
                                        <div class="extension-reason mt-1">
                                            <i class="fas fa-comment text-info me-1"></i>
                                            <small>Lý do: {{ $extension->reason }}</small>
                                        </div>
                                        <div class="extension-time mt-1 text-muted">
                                            <i class="fas fa-history me-1"></i>
                                            <small>Yêu cầu lúc: {{ $extension->requested_at->format('d/m/Y H:i') }}</small>
                                        </div>
                                        
                                        @if($extension->status !== 'pending')
                                            <div class="extension-approval mt-1">
                                                <i class="fas {{ $extension->status === 'approved' ? 'fa-check-circle text-success' : 'fa-times-circle text-danger' }} me-1"></i>
                                                <small>{{ $extension->status === 'approved' ? 'Duyệt' : 'Từ chối' }} bởi: <strong>{{ $extension->approver->name ?? 'N/A' }}</strong></small>
                                            </div>
                                            
                                            @if($extension->status === 'rejected' && $extension->rejection_reason)
                                                <div class="extension-rejection mt-1 text-danger">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    <small>Lý do từ chối: {{ $extension->rejection_reason }}</small>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </li>
                            @empty
                                <li class="list-group-item text-center">Không có yêu cầu gia hạn</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@push('scripts')
    <script>
        $(document).ready(function() {
            // Bootstrap tooltip initialization
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
              return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Set timeout for alerts
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
        });
        
        // Confirm delete function with enhanced alert
        function confirmDelete(event, form) {
            event.preventDefault();
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Bạn có chắc chắn?',
                    text: "Bạn không thể hoàn tác hành động này!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Xác nhận xóa',
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