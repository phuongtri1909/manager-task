@extends('manager_task.layouts.partials.sidebar')

@section('title', 'Công việc đã giao')

@section('main-content')
    <div class="category-container">
        <!-- Breadcrumb -->
        <div class="content-breadcrumb">
            <ol class="breadcrumb-list">
                <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item current">Công việc đã giao</li>
            </ol>
        </div>

        <div class="content-card">
            <div class="card-top">
                <div class="card-title">
                    <i class="fas fa-paper-plane icon-title"></i>
                    <h5>Công việc tôi đã giao</h5>
                </div>
                <a href="{{ route('tasks.create') }}" class="action-button">
                    <i class="fas fa-plus-circle"></i> Tạo công việc mới
                </a>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <form action="{{ route('tasks.assigned') }}" method="GET" class="filter-form">
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
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chưa thực
                                    hiện</option>
                                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>Đang
                                    thực hiện</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Hoàn
                                    thành</option>
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

            <div class="card-content">
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

                @if (request('title') || request('status') || request('overdue'))
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
                                <span>Trạng thái:
                                    {{ request('status') == 'pending'
                                        ? 'Chưa thực hiện'
                                        : (request('status') == 'in_progress'
                                            ? 'Đang thực hiện'
                                            : (request('status') == 'completed'
                                                ? 'Hoàn thành'
                                                : request('status'))) }}
                                </span>
                                <a href="{{ request()->url() }}?{{ http_build_query(request()->except('status')) }}"
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
                                    <i class="fas fa-plus-circle"></i> Tạo công việc mới
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
                                    <th class="column-medium">Phòng ban</th>
                                    <th class="column-medium">Người thực hiện</th>
                                    <th class="column-medium">Thời hạn</th>
                                    <th class="column-small text-center">Trạng thái</th>
                                    <th class="column-small text-center">File</th>
                                    <th class="column-small text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tasks as $index => $task)
                                    <tr>
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
                                            <div class="departments-list">
                                                @foreach ($task->departments as $department)
                                                    <span class="department-badge">{{ $department->name }}</span>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td>
                                            <div class="users-list">
                                                @foreach ($task->users as $user)
                                                    <div class="user-badge" title="{{ $user->name }}">
                                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&size=24"
                                                            alt="{{ $user->name }}">
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td>
                                            @if ($task->attachments->count() > 0)
                                                <span class="attachment-count"
                                                    title="{{ $task->attachments->count() }} tệp đính kèm">
                                                    <i class="fas fa-paperclip"></i> {{ $task->attachments->count() }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $task->deadline->format('d/m/Y H:i') }}
                                            @if (now() > $task->deadline && $task->status !== 'completed')
                                                <span class="filter-tag"
                                                    style="background-color: #fef0f0; color: #e53935;">Quá hạn</span>
                                            @elseif($task->deadline->diffInDays(now()) <= 3)
                                                <span class="filter-tag"
                                                    style="background-color: #fff8e1; color: #ff8f00;">Sắp hết hạn</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span
                                                class="status-badge {{ $task->status === 'pending'
                                                    ? 'status-inactive'
                                                    : ($task->status === 'in_progress'
                                                        ? 'bg-info text-white'
                                                        : ($task->status === 'completed'
                                                            ? 'status-active'
                                                            : 'bg-info text-white')) }}">
                                                {{ $task->status === 'pending'
                                                    ? 'Chưa thực hiện'
                                                    : ($task->status === 'in_progress'
                                                        ? 'Đang thực hiện'
                                                        : ($task->status === 'completed'
                                                            ? 'Hoàn thành'
                                                            : $task->status)) }}
                                            </span>
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
                                {{ $tasks->appends(request()->query())->links() }}
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
