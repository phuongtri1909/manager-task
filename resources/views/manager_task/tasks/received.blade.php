@extends('manager_task.layouts.partials.sidebar')

@section('title', 'Công việc được nhận')

@section('main-content')
    <div class="category-container">
        <!-- Breadcrumb -->
        <div class="content-breadcrumb">
            <ol class="breadcrumb-list">
                <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item current">Công việc được nhận</li>
            </ol>
        </div>

        <div class="content-card">
            <div class="card-top">
                <div class="card-title">
                    <i class="fas fa-inbox icon-title"></i>
                    <h5>Công việc được giao cho tôi</h5>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <form action="{{ route('tasks.received') }}" method="GET" class="filter-form">
                    <div class="filter-group">
                        <div class="filter-item">
                            <label for="title_filter">Tiêu đề</label>
                            <input type="text" id="title_filter" name="title" class="filter-input"
                                placeholder="Tìm theo tiêu đề" value="{{ request('title') }}">
                        </div>
                        <div class="filter-item">
                            <label for="status_filter">Trạng thái</label>
                            <select id="status_filter" name="status" class="filter-input">
                                <option value="">Tất cả trạng thái</option>
                                @foreach ($statusOptions as $value => $label)
                                    <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-item">
                            <label for="task_type">Loại công việc</label>
                            <select id="task_type" name="task_type" class="filter-input">
                                <option value="">Tất cả</option>
                                <option value="department" {{ request('task_type') == 'department' ? 'selected' : '' }}>
                                    Phòng ban</option>
                                <option value="individual" {{ request('task_type') == 'individual' ? 'selected' : '' }}>Cá
                                    nhân</option>
                            </select>
                        </div>
                        <div class="filter-item">
                            <label for="overdue_filter">Thời hạn</label>
                            <select id="overdue_filter" name="overdue" class="filter-input">
                                <option value="">Tất cả</option>
                                <option value="1" {{ request('overdue') == '1' ? 'selected' : '' }}>Quá hạn</option>
                                <option value="0" {{ request('overdue') == '0' ? 'selected' : '' }}>Trong hạn</option>
                            </select>
                        </div>
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="filter-btn">
                            <i class="fas fa-filter"></i> Lọc
                        </button>
                        <a href="{{ route('tasks.index') }}" class="filter-clear-btn">
                            <i class="fas fa-times"></i> Xóa bộ lọc
                        </a>
                    </div>
                </form>
            </div>

            <!-- filepath: d:\manager-task\resources\views\manager_task\tasks\received.blade.php -->

            <div class="task-status-badges mt-3 mb-3">
                @php
                    // Đếm số lượng công việc theo từng trạng thái bằng cách duyệt qua collection
                    $sendingCount = 0;
                    $viewedCount = 0;
                    $inProgressCount = 0;
                    $completedCount = 0;
                    $approvedCount = 0;
                    $rejectedCount = 0;

                    // Duyệt qua từng task và kiểm tra trạng thái trong pivot
                    foreach ($tasks as $task) {
                        $taskUser = $task->users->where('id', Auth::id())->first();
                        if ($taskUser) {
                            $status = $taskUser->pivot->status;

                            if ($status === \App\Models\TaskUser::STATUS_SENDING) {
                                $sendingCount++;
                            } elseif ($status === \App\Models\TaskUser::STATUS_VIEWED) {
                                $viewedCount++;
                            } elseif ($status === \App\Models\TaskUser::STATUS_IN_PROGRESS) {
                                $inProgressCount++;
                            } elseif ($status === \App\Models\TaskUser::STATUS_COMPLETED) {
                                $completedCount++;
                            } elseif ($status === \App\Models\TaskUser::STATUS_APPROVED) {
                                $approvedCount++;
                            } elseif (
                                in_array($status, [
                                    \App\Models\TaskUser::STATUS_APPROVAL_REJECTED,
                                    \App\Models\TaskUser::STATUS_REJECTED,
                                ])
                            ) {
                                $rejectedCount++;
                            }
                        }
                    }
                @endphp

                <div class="task-status-badge {{ $sendingCount > 0 ? 'active' : '' }}">
                    <div class="status-badge-count">{{ $sendingCount }}</div>
                    <div class="status-badge-label">Chưa xem</div>
                </div>

                <div class="task-status-badge {{ $viewedCount > 0 ? 'active' : '' }}">
                    <div class="status-badge-count">{{ $viewedCount }}</div>
                    <div class="status-badge-label">Đã xem</div>
                </div>

                <div class="task-status-badge in-progress {{ $inProgressCount > 0 ? 'active' : '' }}">
                    <div class="status-badge-count">{{ $inProgressCount }}</div>
                    <div class="status-badge-label">Đang thực hiện</div>
                </div>

                <div class="task-status-badge completed {{ $completedCount > 0 ? 'active' : '' }}">
                    <div class="status-badge-count">{{ $completedCount }}</div>
                    <div class="status-badge-label">Hoàn thành</div>
                </div>

                <div class="task-status-badge approved {{ $approvedCount > 0 ? 'active' : '' }}">
                    <div class="status-badge-count">{{ $approvedCount }}</div>
                    <div class="status-badge-label">Đã duyệt</div>
                </div>

                <div class="task-status-badge rejected {{ $rejectedCount > 0 ? 'active' : '' }}">
                    <div class="status-badge-count">{{ $rejectedCount }}</div>
                    <div class="status-badge-label">Từ chối</div>
                </div>
            </div>

            <div class="card-content">

                @if (request('title') || request('status') || request('task_type') || request('overdue'))
                    <div class="active-filters">
                        <span class="active-filters-title">Đang lọc: </span>

                        @if (request('title'))
                            <span class="filter-tag">
                                <span>Tiêu đề: {{ request('title') }}</span>
                                <a href="{{ request()->url() }}?{{ http_build_query(request()->except('title')) }}"
                                    class="remove-filter">×</a>
                            </span>
                        @endif

                        @if (request('status'))
                            <span class="filter-tag">
                                <span>Trạng thái: {{ $statusOptions[request('status')] ?? request('status') }}</span>
                                <a href="{{ request()->url() }}?{{ http_build_query(request()->except('status')) }}"
                                    class="remove-filter">×</a>
                            </span>
                        @endif

                        @if (request('task_type'))
                            <span class="filter-tag">
                                <span>Loại công việc:
                                    {{ request('task_type') == 'department' ? 'Phòng ban' : 'Cá nhân' }}</span>
                                <a href="{{ request()->url() }}?{{ http_build_query(request()->except('task_type')) }}"
                                    class="remove-filter">×</a>
                            </span>
                        @endif

                        @if (request('overdue') !== null && request('overdue') !== '')
                            <span class="filter-tag">
                                <span>Thời hạn: {{ request('overdue') == '1' ? 'Quá hạn' : 'Trong hạn' }}</span>
                                <a href="{{ request()->url() }}?{{ http_build_query(request()->except('overdue')) }}"
                                    class="remove-filter">×</a>
                            </span>
                        @endif
                    </div>
                @endif

                @if ($tasks->isEmpty())
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-tasks"></i>
                        </div>
                        @if (request('title') || request('status') || request('overdue'))
                            <h4>Không tìm thấy công việc nào</h4>
                            <p>Không có công việc nào phù hợp với bộ lọc hiện tại.</p>
                            <a href="{{ route('tasks.index') }}" class="action-button">
                                <i class="fas fa-times"></i> Xóa bộ lọc
                            </a>
                        @else
                            <h4>Chưa có công việc nào</h4>
                            <p>Bắt đầu bằng cách thêm công việc đầu tiên.</p>
                            @if (
                                !Auth::user()->isAdmin() &&
                                    Auth::user()->can_assign_task &&
                                    (Auth::user()->isDirector() ||
                                        Auth::user()->isDeputyDirector() ||
                                        Auth::user()->isDepartmentHead() ||
                                        Auth::user()->isDeputyDepartmentHead()))
                                <a href="{{ route('tasks.create') }}" class="action-button">
                                    <i class="fas fa-plus-circle mb-0"></i> Tạo công việc mới
                                </a>
                            @endif
                        @endif
                    </div>
                @else
                    <div class="data-table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th class="column-small">ID</th>
                                    <th class="column-large">Tiêu đề</th>
                                    <th class="column-medium">Người tạo</th>
                                    <th class="column-medium">Loại công việc</th>
                                    <th class="column-medium">Trạng thái</th>
                                    <th class="column-medium">Thời hạn</th>
                                    <th class="column-small text-center">File</th>
                                    <th class="column-small text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tasks as $index => $task)
                                    @php
                                        $taskUser = $task->users->where('id', Auth::id())->first();
                                        $status = $taskUser ? $taskUser->pivot->status : 'undefined';

                                        $statusClass = match ($status) {
                                            \App\Models\TaskUser::STATUS_SENDING => 'status-sending',
                                            \App\Models\TaskUser::STATUS_VIEWED => 'status-viewed',
                                            \App\Models\TaskUser::STATUS_IN_PROGRESS => 'status-in-progress',
                                            default => '',
                                        };
                                    @endphp
                                    <tr class="fs-7">
                                        <td class="text-center">{{ $task->id }}</td>
                                        <td class="item-title">
                                            {{ $task->title }}
                                            @if (now()->diffInHours($task->created_at) < 24)
                                                <span class="filter-tag"
                                                    style="background-color: #e3fcef; color: #00875a;">Mới</span>
                                            @endif
                                        </td>
                                        <td>{{ $task->creator->name ?? 'N/A' }}</td>
                                        <td>
                                            <div class="departments-list d-flex flex-column">
                                                @if ($task->for_departments)
                                                    Phòng ban
                                                @else
                                                    Cá nhân
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $taskUser = $task->users->where('id', Auth::id())->first();
                                                $status = $taskUser ? $taskUser->pivot->status : 'undefined';
                                            @endphp

                                            @switch($status)
                                                @case('sending')
                                                    <span class="badge bg-warning">Chưa xem</span>
                                                @break

                                                @case('viewed')
                                                    <span class="badge bg-secondary">Đã xem</span>
                                                @break

                                                @case('in_progress')
                                                    <span class="badge bg-info">Đang thực hiện</span>
                                                @break

                                                @case('completed')
                                                    <span class="badge bg-success">Hoàn thành</span>
                                                @break

                                                @case('approval_rejected')
                                                    <span class="badge bg-danger">Từ chối kết quả</span>
                                                @break

                                                @case('approved')
                                                    <span class="badge bg-primary">Đã phê duyệt</span>
                                                @break

                                                @case('rejected')
                                                    <span class="badge bg-dark">Đã hủy</span>
                                                @break

                                                @default
                                                    <span class="badge bg-light text-dark">Không xác định</span>
                                            @endswitch
                                        </td>
                                        <td class="fs-7">
                                            {{ $task->deadline->format('d/m/Y H:i') }}
                                            @if (now() > $task->deadline && $task->status !== 'completed')
                                                <span class="filter-tag fs-8"
                                                    style="background-color: #fef0f0; color: #e53935;">Quá hạn</span>
                                            @elseif($task->deadline->diffInDays(now()) <= 3)
                                                <span class="filter-tag fs-8"
                                                    style="background-color: #fff8e1; color: #ff8f00;">Sắp hết hạn</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($task->attachments->count() > 0)
                                                @foreach ($task->attachments as $attachment)
                                                    <a href="{{ asset($attachment->file_path) }}"
                                                        class="attachment-icon text-decoration-none p-1" target="_blank"
                                                        title="{{ $attachment->filename }}">
                                                        <i class="fas fa-file-download fa-xl"></i>
                                                    </a>
                                                @endforeach
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="action-buttons-wrapper">
                                                <a href="{{ route('tasks.show', $task) }}"
                                                    class="action-icon view-icon text-decoration-none"
                                                    title="Xem chi tiết">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if (Auth::user()->isAdmin())
                                                    <a href="{{ route('tasks.edit', $task) }}"
                                                        class="action-icon edit-icon" title="Chỉnh sửa">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('tasks.destroy', $task) }}" method="POST"
                                                        class="delete-action-container d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="action-icon bg-danger"
                                                            style="border: none" onclick="confirmDelete(event, this.form)"
                                                            title="Xóa">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if (method_exists($tasks, 'links'))
                        <div class="pagination-wrapper">
                            <div class="pagination-info">
                                Hiển thị {{ $tasks->firstItem() ?? 0 }} đến {{ $tasks->lastItem() ?? 0 }} của
                                {{ $tasks->total() }} công việc
                            </div>
                            <div class="pagination-controls">
                                {{ $tasks->appends(request()->query())->links('manager_task.components.paginate') }}
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- Copy scripts từ index.blade.php -->
@endpush
