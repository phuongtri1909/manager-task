@extends('manager_task.layouts.partials.sidebar')

@section('title', 'Quản lý yêu cầu gia hạn')

@section('main-content')
    <div class="category-container">
        <!-- Breadcrumb -->
        <div class="content-breadcrumb">
            <ol class="breadcrumb-list">
                <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item current">Yêu cầu gia hạn</li>
            </ol>
        </div>

        <div class="content-card">
            <div class="card-top">
                <div class="card-title">
                    <i class="fas fa-clock icon-title"></i>
                    <h5>Danh sách yêu cầu gia hạn</h5>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <form action="{{ route('task-extensions.index') }}" method="GET" class="filter-form">
                    <div class="filter-group">
                        <div class="filter-item">
                            <label for="search">Tìm kiếm</label>
                            <input type="text" id="search" name="search" class="filter-input"
                                placeholder="Tên công việc, người yêu cầu..." value="{{ request('search') }}">
                        </div>

                        <div class="filter-item">
                            <label for="status">Trạng thái</label>
                            <select id="status" name="status" class="filter-input">
                                <option value="">Tất cả</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Đang chờ duyệt</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Đã từ chối</option>
                            </select>
                        </div>

                        @if (isset($departments) && $departments->count() > 0 && (Auth::user()->isDirector() || Auth::user()->isDeputyDirector() || Auth::user()->isAdmin()))
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
                        <a href="{{ route('task-extensions.index') }}" class="filter-clear-btn">
                            <i class="fas fa-times"></i> Xóa bộ lọc
                        </a>
                    </div>
                </form>
            </div>

            <div class="card-content">
                <!-- Active filters -->
                @if (request('search') || request('status') || request('department_id') || request('date_from') || request('date_to'))
                    <div class="active-filters">
                        <span class="active-filters-title">Đang lọc: </span>
                        @if (request('search'))
                            <span class="filter-tag">
                                <span>Tìm kiếm: {{ request('search') }}</span>
                                <a href="{{ request()->url() }}?{{ http_build_query(request()->except('search')) }}"
                                    class="remove-filter">×</a>
                            </span>
                        @endif
                        @if (request('status'))
                            <span class="filter-tag">
                                <span>Trạng thái: 
                                    @if(request('status') == 'pending') Đang chờ duyệt
                                    @elseif(request('status') == 'approved') Đã duyệt
                                    @elseif(request('status') == 'rejected') Đã từ chối
                                    @endif
                                </span>
                                <a href="{{ request()->url() }}?{{ http_build_query(request()->except('status')) }}"
                                    class="remove-filter">×</a>
                            </span>
                        @endif
                        @if (request('department_id') && isset($departments))
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

                @if ($extensions->isEmpty())
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        @if (request('search') || request('status') || request('department_id') || request('date_from') || request('date_to'))
                            <h4>Không tìm thấy yêu cầu nào</h4>
                            <p>Không có yêu cầu gia hạn nào phù hợp với bộ lọc hiện tại.</p>
                            <a href="{{ route('task-extensions.index') }}" class="action-button">
                                <i class="fas fa-times"></i> Xóa bộ lọc
                            </a>
                        @else
                            <h4>Chưa có yêu cầu gia hạn nào</h4>
                            <p>Hiện tại không có yêu cầu gia hạn nào cần xử lý.</p>
                        @endif
                    </div>
                @else
                    <div class="data-table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th class="column-small">ID</th>
                                    <th class="column-large">Công việc</th>
                                    <th class="column-medium">Người yêu cầu</th>
                                    <th class="column-medium">Thời hạn hiện tại</th>
                                    <th class="column-medium">Thời hạn mới</th>
                                    <th class="column-small text-center">Ngày yêu cầu</th>
                                    <th class="column-small text-center">Trạng thái</th>
                                    <th class="column-small text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($extensions as $extension)
                                    <tr class="fs-7">
                                        <td class="text-center">{{ $extension->id }}</td>
                                        <td class="item-title">
                                            <a href="{{ route('tasks.show', $extension->task_id) }}" class="task-link text-decoration-none">
                                                {{ Str::limit($extension->task->title ?? 'N/A', 40) }}
                                            </a>
                                            @if (now()->diffInHours($extension->requested_at) < 24)
                                                <span class="filter-tag"
                                                    style="background-color: #e3fcef; color: #00875a;">Mới</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                {{ $extension->user->name ?? 'N/A' }}
                                                <span class="ms-1 text-muted">({{ $extension->user->department->name ?? 'N/A' }})</span>
                                            </div>
                                        </td>
                                        <td class="fs-7">
                                            {{ optional($extension->task->deadline)->format('d/m/Y H:i') ?? 'N/A' }}
                                            @if ($extension->task && now() > $extension->task->deadline)
                                                <span class="filter-tag fs-8"
                                                    style="background-color: #fef0f0; color: #e53935;">Quá hạn</span>
                                            @endif
                                        </td>
                                        <td class="fs-7">
                                            {{ $extension->new_deadline->format('d/m/Y H:i') }}
                                            <span class="filter-tag fs-8" style="background-color: #edf7ff; color: #0077c5;">
                                                <i class="fas fa-clock me-1"></i>
                                                +{{ $extension->new_deadline->diffInDays($extension->task->deadline ?? now()) }} ngày
                                            </span>
                                        </td>
                                        <td class="text-center fs-7">{{ $extension->requested_at->format('d/m/Y') }}</td>
                                        <td class="text-center">
                                            @if ($extension->status === 'pending')
                                                <span class="status-badge status-warning">
                                                    <i class="fas fa-hourglass-half"></i> Đang chờ
                                                </span>
                                            @elseif ($extension->status === 'approved')
                                                <span class="status-badge status-active">
                                                    <i class="fas fa-check-circle"></i> Đã duyệt
                                                </span>
                                            @elseif ($extension->status === 'rejected')
                                                <span class="status-badge status-inactive">
                                                    <i class="fas fa-times-circle"></i> Từ chối
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="action-buttons-wrapper">
                                                <button type="button" class="action-icon view-icon" 
                                                        onclick="showExtensionDetail({{ $extension->id }})"
                                                        title="Xem chi tiết">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                
                                                @if ($extension->status === 'pending')
                                                    <button type="button" class="action-icon approve-btn btn-success btn" 
                                                            onclick="approveExtension({{ $extension->id }})"
                                                            title="Phê duyệt">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    
                                                    <button type="button" class="action-icon reject-btn btn bg-danger"
                                                            onclick="showRejectModal({{ $extension->id }})"
                                                            title="Từ chối">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if (method_exists($extensions, 'links'))
                        <div class="pagination-wrapper">
                            <div class="pagination-info">
                                Hiển thị {{ $extensions->firstItem() ?? 0 }} đến {{ $extensions->lastItem() ?? 0 }} của
                                {{ $extensions->total() }} yêu cầu
                            </div>
                            <div class="pagination-controls">
                                {{ $extensions->appends(request()->query())->links('manager_task.components.paginate') }}
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <!-- Extension Detail Modal -->
    <div class="modal fade" id="extensionDetailModal" tabindex="-1" aria-labelledby="extensionDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" id="extensionDetailModalHeader">
                    <h5 class="modal-title" id="extensionDetailModalLabel">Chi tiết yêu cầu gia hạn</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="extensionDetailModalBody">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Đang tải...</span>
                        </div>
                        <p class="mt-2">Đang tải thông tin...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <div id="extensionDetailModalActions"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rejection Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="rejectModalLabel">Từ chối yêu cầu gia hạn</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="rejectForm">
                        <input type="hidden" id="extension_id" name="extension_id" value="">
                        <input type="hidden" name="status" value="rejected">
                        
                        <div class="mb-3">
                            <label for="rejection_reason" class="form-label required">Lý do từ chối</label>
                            <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="4" required 
                                      placeholder="Nhập lý do từ chối yêu cầu gia hạn..."></textarea>
                            <div class="invalid-feedback" id="rejection_reason_error"></div>
                            <div class="form-text">Vui lòng cung cấp lý do từ chối yêu cầu gia hạn này.</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="button" class="btn btn-danger" id="submitRejectBtn">
                        <i class="fas fa-times me-1"></i> Xác nhận từ chối
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Auto submit when changing filters
    document.addEventListener('DOMContentLoaded', function() {
        const statusFilter = document.getElementById('status');
        const departmentFilter = document.getElementById('department_id');
        const filterForm = document.querySelector('.filter-form');
        
        if (statusFilter) {
            statusFilter.addEventListener('change', function() {
                filterForm.submit();
            });
        }
        
        if (departmentFilter) {
            departmentFilter.addEventListener('change', function() {
                filterForm.submit();
            });
        }
    });
    
    // Show extension detail
    function showExtensionDetail(extensionId) {
        const modal = new bootstrap.Modal(document.getElementById('extensionDetailModal'));
        const modalHeader = document.getElementById('extensionDetailModalHeader');
        const modalBody = document.getElementById('extensionDetailModalBody');
        const modalActions = document.getElementById('extensionDetailModalActions');
        
        // Reset modal content
        modalBody.innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Đang tải...</span>
                </div>
                <p class="mt-2">Đang tải thông tin...</p>
            </div>
        `;
        
        modal.show();
        
        // Find extension in the page
        const extensionRow = document.querySelector(`tr[data-extension-id="${extensionId}"]`);
        if (extensionRow) {
            const taskTitle = extensionRow.querySelector('.item-title a').textContent.trim();
            const requesterName = extensionRow.querySelector('td:nth-child(3)').textContent.trim();
            const currentDeadline = extensionRow.querySelector('td:nth-child(4)').textContent.trim().split('Quá hạn')[0].trim();
            const newDeadline = extensionRow.querySelector('td:nth-child(5)').textContent.trim().split('+')[0].trim();
            const extensionDays = extensionRow.querySelector('.filter-tag').textContent.trim();
            const requestDate = extensionRow.querySelector('td:nth-child(6)').textContent.trim();
            const status = extensionRow.querySelector('td:nth-child(7) .status-badge').textContent.trim();
            
            let statusClass = '';
            if (status.includes('Đang chờ')) statusClass = 'bg-warning';
            else if (status.includes('Đã duyệt')) statusClass = 'bg-success text-white';
            else if (status.includes('Từ chối')) statusClass = 'bg-danger text-white';
            
            // Update modal header
            modalHeader.className = `modal-header ${statusClass}`;
            if (statusClass.includes('text-white')) {
                document.querySelector('#extensionDetailModal .btn-close').classList.add('btn-close-white');
            } else {
                document.querySelector('#extensionDetailModal .btn-close').classList.remove('btn-close-white');
            }
            
            // Create and populate content
            // ...create detailed content...
            
            // Add actions if pending
            if (status.includes('Đang chờ')) {
                modalActions.innerHTML = `
                    <button type="button" class="btn btn-success" onclick="approveExtension(${extensionId})">
                        <i class="fas fa-check me-1"></i> Phê duyệt
                    </button>
                    <button type="button" class="btn btn-danger" onclick="showRejectModal(${extensionId})">
                        <i class="fas fa-times me-1"></i> Từ chối
                    </button>
                `;
            } else {
                modalActions.innerHTML = '';
            }
        } else {
            // Handle missing data
            modalBody.innerHTML = '<div class="alert alert-danger">Không thể tải thông tin chi tiết.</div>';
        }
    }
    
    // Approve extension
    function approveExtension(extensionId) {
        if (confirm('Bạn có chắc chắn muốn phê duyệt yêu cầu gia hạn này?')) {
            // Send AJAX request to approve
            fetch(`/task-extensions/${extensionId}/respond`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    status: 'approved'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    alert(data.message);
                    // Reload page to reflect changes
                    window.location.reload();
                } else {
                    alert(data.message || 'Đã xảy ra lỗi khi phê duyệt.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Đã xảy ra lỗi khi phê duyệt yêu cầu.');
            });
        }
    }
    
    // Show reject modal
    function showRejectModal(extensionId) {
        document.getElementById('extension_id').value = extensionId;
        document.getElementById('rejection_reason').value = '';
        document.getElementById('rejection_reason_error').textContent = '';
        
        const rejectModal = new bootstrap.Modal(document.getElementById('rejectModal'));
        rejectModal.show();
    }
    
    // Handle reject submission
    document.getElementById('submitRejectBtn').addEventListener('click', function() {
        const extensionId = document.getElementById('extension_id').value;
        const rejectionReason = document.getElementById('rejection_reason').value;
        
        if (!rejectionReason.trim()) {
            document.getElementById('rejection_reason_error').textContent = 'Vui lòng nhập lý do từ chối.';
            document.getElementById('rejection_reason').classList.add('is-invalid');
            return;
        }
        
        if (rejectionReason.trim().length < 10) {
            document.getElementById('rejection_reason_error').textContent = 'Lý do từ chối phải có ít nhất 10 ký tự.';
            document.getElementById('rejection_reason').classList.add('is-invalid');
            return;
        }
        
        // Send AJAX request to reject
        fetch(`/task-extensions/${extensionId}/respond`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                status: 'rejected',
                rejection_reason: rejectionReason
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close modal
                bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
                
                // Show success message
                alert(data.message);
                
                // Reload page to reflect changes
                window.location.reload();
            } else if (data.errors && data.errors.rejection_reason) {
                document.getElementById('rejection_reason_error').textContent = data.errors.rejection_reason[0];
                document.getElementById('rejection_reason').classList.add('is-invalid');
            } else {
                alert(data.message || 'Đã xảy ra lỗi khi từ chối yêu cầu.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Đã xảy ra lỗi khi từ chối yêu cầu.');
        });
    });
    
    // Clear validation when typing
    document.getElementById('rejection_reason').addEventListener('input', function() {
        this.classList.remove('is-invalid');
        document.getElementById('rejection_reason_error').textContent = '';
    });
</script>
@endpush