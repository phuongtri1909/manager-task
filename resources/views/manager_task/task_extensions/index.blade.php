@extends('manager_task.layouts.partials.sidebar')

@section('title', 'Danh sách yêu cầu gia hạn')

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
                    <h5>Quản lý yêu cầu gia hạn</h5>
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
                
                <ul class="nav nav-tabs custom-tabs mb-4" id="extensionsTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="all-tab" data-bs-toggle="tab" href="#all" role="tab" aria-controls="all" aria-selected="true">
                            <i class="fas fa-list me-1"></i> Tất cả <span class="badge bg-primary ms-1">{{ $extensions->count() }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="pending-tab" data-bs-toggle="tab" href="#pending" role="tab" aria-controls="pending" aria-selected="false">
                            <i class="fas fa-hourglass-start me-1"></i> Đang chờ <span class="badge bg-warning ms-1">{{ $extensions->where('status', 'pending')->count() }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="approved-tab" data-bs-toggle="tab" href="#approved" role="tab" aria-controls="approved" aria-selected="false">
                            <i class="fas fa-check-circle me-1"></i> Đã duyệt <span class="badge bg-success ms-1">{{ $extensions->where('status', 'approved')->count() }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="rejected-tab" data-bs-toggle="tab" href="#rejected" role="tab" aria-controls="rejected" aria-selected="false">
                            <i class="fas fa-times-circle me-1"></i> Từ chối <span class="badge bg-danger ms-1">{{ $extensions->where('status', 'rejected')->count() }}</span>
                        </a>
                    </li>
                </ul>
                
                <div class="tab-content" id="extensionsTabsContent">
                    <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">
                        @include('manager_task.task_extensions.partials.extension_table', ['extensionList' => $extensions])
                    </div>
                    <div class="tab-pane fade" id="pending" role="tabpanel" aria-labelledby="pending-tab">
                        @include('manager_task.task_extensions.partials.extension_table', ['extensionList' => $extensions->where('status', 'pending')])
                    </div>
                    <div class="tab-pane fade" id="approved" role="tabpanel" aria-labelledby="approved-tab">
                        @include('manager_task.task_extensions.partials.extension_table', ['extensionList' => $extensions->where('status', 'approved')])
                    </div>
                    <div class="tab-pane fade" id="rejected" role="tabpanel" aria-labelledby="rejected-tab">
                        @include('manager_task.task_extensions.partials.extension_table', ['extensionList' => $extensions->where('status', 'rejected')])
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap4.min.css">
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {
            // DataTables initialization
            $('.extension-table').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.4/i18n/vi.json'
                },
                order: [[0, 'desc']], // Sort by newest first
                pageLength: 10,
                responsive: true,
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                     '<"row"<"col-sm-12"tr>>' +
                     '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            });
            
            // Save active tab
            $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                localStorage.setItem('activeExtensionTab', $(e.target).attr('href'));
            });
            
            // Restore active tab
            var activeTab = localStorage.getItem('activeExtensionTab');
            if(activeTab){
                $('#extensionsTabs a[href="' + activeTab + '"]').tab('show');
            }
            
            // Modal for extension approval/rejection
            $(document).on('click', '.approve-btn, .reject-btn', function(e) {
                e.preventDefault();
                const action = $(this).hasClass('approve-btn') ? 'approve' : 'reject';
                const extensionId = $(this).data('id');
                const taskTitle = $(this).data('task');
                const userName = $(this).data('user');
                const form = action === 'approve' ? 
                    $('#approveForm' + extensionId) : 
                    $('#rejectForm' + extensionId);
                
                if (action === 'approve') {
                    $('#approveModal').find('.task-title').text(taskTitle);
                    $('#approveModal').find('.user-name').text(userName);
                    $('#approveModal').find('form').attr('action', form.attr('action'));
                    $('#approveModal').modal('show');
                } else {
                    $('#rejectModal').find('.task-title').text(taskTitle);
                    $('#rejectModal').find('.user-name').text(userName);
                    $('#rejectModal').find('form').attr('action', form.attr('action'));
                    $('#rejectModal').modal('show');
                }
            });
        });
    </script>
    
    <!-- Approval Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1" role="dialog" aria-labelledby="approveModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="approveModalLabel"><i class="fas fa-check-circle me-2"></i>Xác nhận duyệt yêu cầu</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn duyệt yêu cầu gia hạn của <strong class="user-name"></strong> cho công việc <strong class="task-title"></strong>?</p>
                    <p>Khi được duyệt, thời hạn của công việc sẽ được cập nhật theo yêu cầu.</p>
                    
                    <form action="" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="approved">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="modal-button secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i>Hủy</button>
                    <button type="button" class="modal-button primary" onclick="$(this).closest('.modal').find('form').submit();"><i class="fas fa-check me-1"></i>Xác nhận duyệt</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="rejectModalLabel"><i class="fas fa-times-circle me-2"></i>Xác nhận từ chối yêu cầu</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn từ chối yêu cầu gia hạn của <strong class="user-name"></strong> cho công việc <strong class="task-title"></strong>?</p>
                    
                    <form action="" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="rejected">
                        
                        <div class="form-group">
                            <label for="rejection_reason" class="form-label-custom">Lý do từ chối:</label>
                            <textarea class="custom-input" id="rejection_reason" name="rejection_reason" rows="3" required></textarea>
                            <div class="form-hint">Vui lòng cung cấp lý do từ chối để người yêu cầu hiểu rõ.</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="modal-button secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i>Hủy</button>
                    <button type="button" class="modal-button danger" onclick="$(this).closest('.modal').find('form').submit();"><i class="fas fa-ban me-1"></i>Xác nhận từ chối</button>
                </div>
            </div>
        </div>
    </div>
@endpush 