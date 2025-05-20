@extends('manager_task.layouts.partials.sidebar')

@section('title', 'Danh sách công việc')

@section('main-content')
    <div class="category-container">
        <!-- Breadcrumb -->
        <div class="content-breadcrumb">
            <ol class="breadcrumb-list">
                <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item current">Công việc</li>
            </ol>
        </div>

        <div class="content-card">
            <div class="card-top">
                <div class="card-title">
                    <i class="fas fa-tasks icon-title"></i>
                    <h5>Danh sách công việc</h5>
                </div>
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
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <form action="{{ route('tasks.index.admin') }}" method="GET" class="filter-form">
                    <div class="filter-group">
                        <div class="filter-item">
                            <label for="title_filter">Tiêu đề</label>
                            <input type="text" id="title_filter" name="title" class="filter-input"
                                placeholder="Tìm theo tiêu đề" value="{{ request('title') }}">
                        </div>
                        <div class="filter-item">
                            <label for="department_id">Phòng ban</label>
                            <select id="department_id" name="department_id" class="filter-input">
                                <option value="">Tất cả phòng ban</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}"
                                        {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-item">
                            <label for="creator_role">Vai trò người tạo</label>
                            <select id="creator_role" name="creator_role" class="filter-input">
                                <option value="">Tất cả vai trò</option>
                                @foreach ($roles as $role)
                                    @if ($role->slug !== 'admin' && $role->slug !== 'staff')
                                        <option value="{{ $role->id }}"
                                            {{ request('creator_role') == $role->id ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endif
                                @endforeach
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

                @if (request('title') || request('department_id') || request('creator_role') || request('overdue'))
                    <div class="active-filters">
                        <span class="active-filters-title">Đang lọc: </span>

                        @if (request('title'))
                            <span class="filter-tag">
                                <span>Tiêu đề: {{ request('title') }}</span>
                                <a href="{{ request()->url() }}?{{ http_build_query(request()->except('title')) }}"
                                    class="remove-filter">×</a>
                            </span>
                        @endif

                        @if (request('department_id'))
                            @php
                                $dept = $departments->firstWhere('id', request('department_id'));
                            @endphp
                            <span class="filter-tag">
                                <span>Phòng ban: {{ $dept ? $dept->name : 'Không xác định' }}</span>
                                <a href="{{ request()->url() }}?{{ http_build_query(request()->except('department_id')) }}"
                                    class="remove-filter">×</a>
                            </span>
                        @endif

                        @if (request('creator_role'))
                            @php
                                $role = $roles->firstWhere('id', request('creator_role'));
                            @endphp
                            <span class="filter-tag">
                                <span>Vai trò người tạo: {{ $role ? $role->name : 'Không xác định' }}</span>
                                <a href="{{ request()->url() }}?{{ http_build_query(request()->except('creator_role')) }}"
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
                                    <th class="column-medium">Thời hạn</th>
                                    <th class="column-small text-center">File</th>
                                    <th class="column-small text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tasks as $index => $task)
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
                                                @foreach ($task->departments as $department)
                                                    <span class="department-badge">- {{ $department->name }}</span>
                                                @endforeach
                                            </div>
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

        @if (Auth::user()->isAdmin() ||
                Auth::user()->isDirector() ||
                Auth::user()->isDeputyDirector() ||
                Auth::user()->isDepartmentHead() ||
                Auth::user()->isDeputyDepartmentHead())
            <div class="text-end mt-4">
                <a href="{{ route('tasks.statistics') }}" class="action-button">
                    <i class="fas fa-chart-pie me-1"></i> Xem thống kê công việc
                </a>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        // Khi thay đổi bộ lọc, tự động submit form
        document.getElementById('department_id').addEventListener('change', function() {
            document.querySelector('.filter-form').submit();
        });

        document.getElementById('creator_role').addEventListener('change', function() {
            document.querySelector('.filter-form').submit();
        });

        document.getElementById('overdue_filter').addEventListener('change', function() {
            document.querySelector('.filter-form').submit();
        });

        // Confirm delete function with enhanced alert
        function confirmDelete(event, form) {
            event.preventDefault();
            event.stopPropagation();

            // Use SweetAlert if available, otherwise fallback to confirm
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Bạn có chắc chắn?',
                    text: "Bạn không thể hoàn tác hành động này!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Xóa',
                    cancelButtonText: 'Hủy'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            } else {
                if (confirm('Bạn có chắc chắn muốn xóa công việc này?')) {
                    form.submit();
                }
            }
        }
    </script>
@endpush
