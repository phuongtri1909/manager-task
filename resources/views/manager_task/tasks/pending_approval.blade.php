<!-- filepath: d:\manager-task\resources\views\manager_task\tasks\pending_approval.blade.php -->
@extends('manager_task.layouts.partials.sidebar')

@section('title', 'Công việc chờ duyệt')

@section('main-content')
    <div class="category-container">
        <!-- Breadcrumb -->
        <div class="content-breadcrumb">
            <ol class="breadcrumb-list">
                <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item current">Công việc chờ duyệt</li>
            </ol>
        </div>

        <div class="content-card">
            <div class="card-top">
                <div class="card-title">
                    <i class="fas fa-clipboard-check icon-title"></i>
                    <h5>Công việc hoàn thành đang chờ duyệt</h5>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <form action="{{ route('tasks.pending-approval') }}" method="GET" class="filter-form">
                    <div class="filter-group">
                        <div class="filter-item">
                            <label for="search">Tìm kiếm</label>
                            <input type="text" id="search" name="search" class="filter-input"
                                placeholder="Tìm theo tiêu đề hoặc tên người thực hiện" value="{{ request('search') }}">
                        </div>

                        @if (isset($departments) &&
                                $departments->count() > 0 &&
                                (Auth::user()->isDirector() || Auth::user()->isDeputyDirector()))
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
                        @endif

                        <div class="filter-item">
                            <label for="date_from">Từ ngày</label>
                            <input type="date" id="date_from" name="date_from" class="filter-input"
                                value="{{ request('date_from') }}">
                        </div>

                        <div class="filter-item">
                            <label for="date_to">Đến ngày</label>
                            <input type="date" id="date_to" name="date_to" class="filter-input"
                                value="{{ request('date_to') }}">
                        </div>
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="filter-btn">
                            <i class="fas fa-filter"></i> Lọc
                        </button>
                        <a href="{{ route('tasks.pending-approval') }}" class="filter-clear-btn">
                            <i class="fas fa-times"></i> Xóa bộ lọc
                        </a>
                    </div>
                </form>
            </div>

            <div class="card-content">
                @if (request('search') || request('department_id') || request('date_from') || request('date_to'))
                    <div class="active-filters">
                        <span class="active-filters-title">Đang lọc: </span>
                        @if (request('search'))
                            <span class="filter-tag">
                                <span>Tìm kiếm: {{ request('search') }}</span>
                                <a href="{{ request()->url() }}?{{ http_build_query(request()->except('search')) }}"
                                    class="remove-filter">×</a>
                            </span>
                        @endif
                        @if (request('department_id') && isset($departments) && (Auth::user()->isDirector() || Auth::user()->isDeputyDirector()))
                            @php
                                $dept = $departments->firstWhere('id', request('department_id'));
                            @endphp
                            <span class="filter-tag">
                                <span>Phòng ban: {{ $dept ? $dept->name : 'Không xác định' }}</span>
                                <a href="{{ request()->url() }}?{{ http_build_query(request()->except('department_id')) }}"
                                    class="remove-filter">×</a>
                            </span>
                        @endif
                        @if (request('date_from'))
                            <span class="filter-tag">
                                <span>Từ ngày: {{ request('date_from') }}</span>
                                <a href="{{ request()->url() }}?{{ http_build_query(request()->except('date_from')) }}"
                                    class="remove-filter">×</a>
                            </span>
                        @endif
                        @if (request('date_to'))
                            <span class="filter-tag">
                                <span>Đến ngày: {{ request('date_to') }}</span>
                                <a href="{{ request()->url() }}?{{ http_build_query(request()->except('date_to')) }}"
                                    class="remove-filter">×</a>
                            </span>
                        @endif
                    </div>
                @endif

                @if ($pendingTasks->isEmpty())
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        @if (request('search') || request('department_id') || request('date_from') || request('date_to'))
                            <h4>Không tìm thấy công việc nào</h4>
                            <p>Không có công việc nào phù hợp với bộ lọc hiện tại.</p>
                            <a href="{{ route('tasks.pending-approval') }}" class="action-button">
                                <i class="fas fa-times"></i> Xóa bộ lọc
                            </a>
                        @else
                            <h4>Không có công việc nào chờ duyệt</h4>
                            <p>Tất cả kết quả công việc đã được phê duyệt.</p>
                        @endif
                    </div>
                @else
                    <div class="data-table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th class="column-small">STT</th>
                                    <th class="column-medium">Tiêu đề</th>
                                    <th class="column-medium">Người thực hiện</th>
                                    <th class="column-small">Phòng ban</th>
                                    <th class="column-small">Ngày hoàn thành</th>
                                    <th class="column-small">Số lần từ chối</th>
                                    <th class="column-medium">File đính kèm</th>
                                    <th class="column-small text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pendingTasks as $index => $task)
                                    <tr class="fs-7">
                                        <td class="text-center">{{ $pendingTasks->firstItem() + $index }}</td>
                                        <td class="item-title">
                                            <a href="{{ route('tasks.show', $task->task_id) }}"
                                                class="text-decoration-none">
                                                {{ $task->title }}
                                            </a>
                                        </td>
                                        <td>{{ $task->user_name }}</td>
                                        <td>{{ $task->department_name }}</td>
                                        <td class="fs-7">
                                            {{ \Carbon\Carbon::parse($task->completion_date)->format('H:i d/m/Y') }}

                                            @php
                                                $completionDate = \Carbon\Carbon::parse($task->completion_date);
                                                $hoursAgo = $completionDate->diffInHours(now());
                                            @endphp

                                            @if ($hoursAgo < 24)
                                                <span class="filter-tag fs-8"
                                                    style="background-color: #e3fcef; color: #00875a;">Mới</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($task->approved_rejected > 0)
                                                <span class="badge bg-danger">{{ $task->approved_rejected }}</span>
                                            @else
                                                <span class="text-muted">0</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if (isset($taskUserAttachments[$task->task_user_id]) && count($taskUserAttachments[$task->task_user_id]) > 0)
                                                <div class="attachments-wrapper">
                                                    @foreach ($taskUserAttachments[$task->task_user_id] as $file)
                                                        @php
                                                            $fileType = strtolower($file->file_type);
                                                            $iconClass = match (true) {
                                                                in_array($fileType, ['pdf'])
                                                                    => 'fas fa-file-pdf text-danger',
                                                                in_array($fileType, ['doc', 'docx'])
                                                                    => 'fas fa-file-word text-primary',
                                                                in_array($fileType, ['xls', 'xlsx', 'csv'])
                                                                    => 'fas fa-file-excel text-success',
                                                                in_array($fileType, ['ppt', 'pptx'])
                                                                    => 'fas fa-file-powerpoint text-warning',
                                                                in_array($fileType, ['jpg', 'jpeg', 'png', 'gif'])
                                                                    => 'fas fa-file-image text-info',
                                                                in_array($fileType, ['zip', 'rar', '7z'])
                                                                    => 'fas fa-file-archive text-secondary',
                                                                default => 'fas fa-file text-muted',
                                                            };
                                                        @endphp

                                                        <div class="attachment-item">
                                                            <a href="{{ route('tasks.user-attachments.download', $file->id) }}"
                                                                class="attachment-link"
                                                                title="{{ $file->original_filename }}">
                                                                <i class="{{ $iconClass }} me-1"></i>
                                                                <span
                                                                    class="attachment-name">{{ $file->original_filename }}</span>
                                                            </a>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-muted fst-italic">Không có file</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="action-buttons-wrapper">
                                                <a href="{{ route('tasks.show', $task->task_id) }}"
                                                    class="action-icon view-icon text-decoration-none"
                                                    title="Xem chi tiết">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button type="button" class="action-icon approve-btn btn btn-success" "
                                                                data-task-id="{{ $task->task_id }}"
                                                                data-user-id="{{ $task->user_id }}"
                                                                data-task-title="{{ $task->title }}"
                                                                data-user-name="{{ $task->user_name }}" title="Phê duyệt">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                            <button type="button" class="action-icon reject-btn btn btn-danger"
                                                                data-task-id="{{ $task->task_id }}"
                                                                data-user-id="{{ $task->user_id }}"
                                                                data-task-title="{{ $task->title }}"
                                                                data-user-name="{{ $task->user_name }}" title="Từ chối kết quả">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
     @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if (method_exists($pendingTasks, 'links'))
                        <div class="pagination-wrapper">
                            <div class="pagination-info">
                                Hiển thị {{ $pendingTasks->firstItem() ?? 0 }} đến {{ $pendingTasks->lastItem() ?? 0 }}
                                của
                                {{ $pendingTasks->total() }} công việc
                            </div>
                            <div class="pagination-controls">
                                {{ $pendingTasks->appends(request()->query())->links('manager_task.components.paginate') }}
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <!-- Modal Phê duyệt -->
    <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="approveModalLabel">Phê duyệt kết quả công việc</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn phê duyệt kết quả công việc "<span id="approveTaskTitle"></span>" của <span
                            id="approveUserName"></span>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-success" id="confirmApprove">Xác nhận phê duyệt</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Từ chối -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectModalLabel">Từ chối kết quả công việc</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="rejectForm">
                    <div class="modal-body">
                        <!-- Hiển thị lịch sử từ chối (nếu có) -->
                        <div id="rejectionHistorySection" class="mb-3 d-none">
                            <label class="form-label">Lịch sử từ chối</label>
                            <div id="rejectionHistoryContent" class="border rounded p-3 bg-light">
                                <!-- Lịch sử từ chối sẽ được thêm ở đây bằng JavaScript -->
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="rejectOptions" class="form-label">Chọn hành động</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status"
                                    id="optionApprovalRejected" value="approval_rejected" checked>
                                <label class="form-check-label" for="optionApprovalRejected">
                                    <span class="text-danger">Từ chối kết quả</span> - Yêu cầu người thực hiện làm lại
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status" id="optionRejected"
                                    value="rejected">
                                <label class="form-check-label" for="optionRejected">
                                    <span class="text-danger">Từ chối công việc</span> - Hủy bỏ hoàn toàn công việc này
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="rejection_reason" class="form-label">Lý do từ chối <span
                                    class="text-danger">*</span></label>
                            <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="4" required></textarea>
                            <div class="invalid-feedback" id="rejection-reason-error"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-danger" id="confirmReject">
                            <span class="spinner-border spinner-border-sm d-none" role="status"
                                aria-hidden="true"></span>
                            Xác nhận từ chối
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto submit when changing filters
            const dateFromFilter = document.getElementById('date_from');
            const dateToFilter = document.getElementById('date_to');
            const filterForm = document.querySelector('.filter-form');
            const departmentFilter = document.getElementById('department_id');

            @if (isset($departments) &&
                    $departments->count() > 0 &&
                    (Auth::user()->isDirector() || Auth::user()->isDeputyDirector()))
                departmentFilter.addEventListener('change', function() {
                    document.querySelector('.filter-form').submit();
                });
            @endif

            // Biến lưu thông tin task và user hiện tại
            let currentTaskId = null;
            let currentUserId = null;
            let currentTaskUserId = null;

            // Modal phê duyệt
            const approveModal = new bootstrap.Modal(document.getElementById('approveModal'));
            const approveButtons = document.querySelectorAll('.approve-btn');

            approveButtons.forEach(button => {
                button.addEventListener('click', function() {
                    currentTaskId = this.getAttribute('data-task-id');
                    currentUserId = this.getAttribute('data-user-id');
                    const taskTitle = this.getAttribute('data-task-title');
                    const userName = this.getAttribute('data-user-name');

                    document.getElementById('approveTaskTitle').textContent = taskTitle;
                    document.getElementById('approveUserName').textContent = userName;

                    approveModal.show();
                });
            });

            // Xử lý khi nhấn nút xác nhận phê duyệt
            document.getElementById('confirmApprove').addEventListener('click', function() {
                if (!currentTaskId || !currentUserId) return;

                const url = `/tasks/${currentTaskId}/approve-status/${currentUserId}`;
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch(url, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            status: 'approved'
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Đã xảy ra lỗi khi phê duyệt');
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Đóng modal
                        approveModal.hide();

                        // Hiển thị thông báo thành công
                        Swal.fire({
                            icon: 'success',
                            title: 'Thành công!',
                            text: 'Phê duyệt kết quả công việc thành công',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            // Tải lại trang
                            window.location.reload();
                        });
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Lỗi!',
                            text: error.message || 'Đã xảy ra lỗi khi phê duyệt công việc',
                        });
                    });
            });

            // Modal từ chối
            const rejectModal = new bootstrap.Modal(document.getElementById('rejectModal'));
            const rejectButtons = document.querySelectorAll('.reject-btn');
            const rejectForm = document.getElementById('rejectForm');

            rejectButtons.forEach(button => {
                button.addEventListener('click', function() {
                    currentTaskId = this.getAttribute('data-task-id');
                    currentUserId = this.getAttribute('data-user-id');

                    // Reset form và validation
                    rejectForm.reset();
                    document.getElementById('rejection_reason').classList.remove('is-invalid');
                    document.getElementById('rejection-reason-error').textContent = '';

                    // Lấy thông tin lịch sử từ chối nếu có
                    const url = `/tasks/${currentTaskId}/rejection-history/${currentUserId}`;
                    fetch(url, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').getAttribute('content'),
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            const historySection = document.getElementById(
                                'rejectionHistorySection');
                            const historyContent = document.getElementById(
                                'rejectionHistoryContent');

                            // Nếu có lịch sử từ chối
                            if (data.history && data.history.length > 0) {
                                historyContent.innerHTML = ''; // Xóa nội dung cũ

                                // Hiển thị từng mục trong lịch sử
                                data.history.forEach((item, index) => {
                                    const historyItem = document.createElement('div');
                                    historyItem.className = 'mb-2 pb-2' + (index < data
                                        .history.length - 1 ? ' border-bottom' : '');

                                    historyItem.innerHTML = `
                                    <div><strong>Lần ${index + 1}</strong> - <small class="text-muted">${item.rejected_at}</small></div>
                                    <div><small>${item.message}</small></div>
                                    <div class="text-end"><small class="text-muted">Bởi: ${item.rejected_by}</small></div>
                                `;

                                    historyContent.appendChild(historyItem);
                                });

                                historySection.classList.remove('d-none');
                            } else {
                                historySection.classList.add('d-none');
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching rejection history:', error);
                            document.getElementById('rejectionHistorySection').classList.add(
                                'd-none');
                        });

                    rejectModal.show();
                });
            });

            // Xử lý khi submit form từ chối
            rejectForm.addEventListener('submit', function(e) {
                e.preventDefault();

                if (!currentTaskId || !currentUserId) return;

                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const status = document.querySelector('input[name="status"]:checked').value;
                const reason = document.getElementById('rejection_reason').value.trim();

                // Validate lý do từ chối
                if (!reason) {
                    document.getElementById('rejection_reason').classList.add('is-invalid');
                    document.getElementById('rejection-reason-error').textContent =
                        'Vui lòng nhập lý do từ chối';
                    return;
                }

                // Hiển thị spinner
                const spinner = document.querySelector('#confirmReject .spinner-border');
                const submitBtn = document.getElementById('confirmReject');
                spinner.classList.remove('d-none');
                submitBtn.disabled = true;

                // Gọi API từ chối
                const url = `/tasks/${currentTaskId}/approve-status/${currentUserId}`;

                fetch(url, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            status: status,
                            rejection_reason: reason
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(data => {
                                throw new Error(data.message || 'Đã xảy ra lỗi khi từ chối');
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Đóng modal
                        rejectModal.hide();

                        // Hiển thị thông báo thành công
                        let message = '';
                        if (status === 'approval_rejected') {
                            message = 'Đã từ chối kết quả và yêu cầu thực hiện lại công việc';
                        } else {
                            message = 'Đã từ chối và hủy bỏ công việc';
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Thành công!',
                            text: message,
                            confirmButtonText: 'OK'
                        }).then(() => {
                            // Tải lại trang
                            window.location.reload();
                        });
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Lỗi!',
                            text: error.message || 'Đã xảy ra lỗi khi từ chối công việc',
                        });
                    })
                    .finally(() => {
                        // Ẩn spinner
                        spinner.classList.add('d-none');
                        submitBtn.disabled = false;
                    });
            });

            // Confirm delete function
            function confirmDelete(event, form) {
                event.preventDefault();
                if (confirm('Bạn có chắc chắn muốn xóa công việc này?')) {
                    form.submit();
                }
            }
        });
    </script>
@endpush

@push('styles')
    <style>
        /* Styles hiện tại */
        .action-buttons-wrapper {
            display: flex;
            gap: 5px;
            justify-content: center;
        }

        .action-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 4px;
            color: white;
            cursor: pointer;
            transition: opacity 0.2s;
        }

        .action-icon:hover {
            opacity: 0.85;
        }

        /* Thêm styles mới cho phần hiển thị file đính kèm */
        .attachments-wrapper {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .attachment-item {
            display: inline-block;
            max-width: 100%;
        }


        .attachment-link {
            display: inline-flex;
            align-items: center;
            font-size: 0.85rem;
            background-color: #f0f0f0;
            padding: 4px 8px;
            border-radius: 4px;
            color: #333;
            text-decoration: none;
            transition: background-color 0.2s;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 100%;
        }

        .attachment-link:hover {
            background-color: #e0e0e0;
            text-decoration: none;
            color: #000;
        }

        .attachment-name {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 220px;
            display: inline-block;
        }

        .attachment-more {
            display: inline-flex;
            align-items: center;
        }

        /* Thêm style cho hiển thị badge rejection count */
        .badge.bg-danger {
            font-weight: 600;
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        /* Modal styles */
        .modal-body .form-check {
            margin-bottom: 10px;
        }

        /* Thêm tooltip cho hiển thị lý do từ chối trước đó nếu có */
        .rejection-history-wrapper {
            position: relative;
        }

        .rejection-history-tooltip {
            display: none;
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background-color: #333;
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 0.8rem;
            width: 250px;
            z-index: 100;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .rejection-history-wrapper:hover .rejection-history-tooltip {
            display: block;
        }

        .rejection-history-item {
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding: 6px 0;
        }

        .rejection-history-item:last-child {
            border-bottom: none;
        }
    </style>
@endpush
