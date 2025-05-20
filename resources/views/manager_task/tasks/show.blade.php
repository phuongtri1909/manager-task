@extends('manager_task.layouts.partials.sidebar')

@section('title', 'Chi tiết công việc')

@section('main-content')
    <div class="category-container">
        <!-- Breadcrumb -->
        <div class="content-breadcrumb">
            <ol class="breadcrumb-list">
                <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}">Công việc</a></li>
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
                            @php
                                $deadline = $task->deadline;
                                $now = now();
                                $diffInSeconds = $now->diffInSeconds($deadline, false);
                                $isPast = $diffInSeconds < 0;
                                $diffInSeconds = abs($diffInSeconds);

                                // Tính toán chi tiết
                                $diffInMinutes = floor($diffInSeconds / 60) % 60;
                                $diffInHours = floor($diffInSeconds / 3600) % 24;
                                $diffInDays = floor($diffInSeconds / 86400) % 30;
                                $diffInMonths = floor($diffInSeconds / 2592000) % 12;
                                $diffInYears = floor($diffInSeconds / 31536000);

                                // Xây dựng chuỗi hiển thị thời gian
                                $timeString = '';
                                if ($diffInYears > 0) {
                                    $timeString .= $diffInYears . ' năm ';
                                }
                                if ($diffInMonths > 0) {
                                    $timeString .= $diffInMonths . ' tháng ';
                                }
                                if ($diffInDays > 0) {
                                    $timeString .= $diffInDays . ' ngày ';
                                }
                                if ($diffInYears == 0 && $diffInMonths == 0) {
                                    if ($diffInHours > 0) {
                                        $timeString .= $diffInHours . ' giờ ';
                                    }
                                    if (
                                        $diffInMinutes > 0 ||
                                        ($diffInYears == 0 &&
                                            $diffInMonths == 0 &&
                                            $diffInDays == 0 &&
                                            $diffInHours == 0)
                                    ) {
                                        $timeString .= $diffInMinutes . ' phút';
                                    }
                                }
                                $timeString = trim($timeString);
                            @endphp

                            @if ($isPast)
                                <span class="task-badge task-badge-overdue">
                                    <i class="far fa-clock me-1"></i>Quá hạn {{ $timeString }}
                                </span>
                            @elseif($diffInDays < 3 && $diffInMonths == 0 && $diffInYears == 0)
                                <span class="task-badge task-badge-warning">
                                    <i class="far fa-clock me-1"></i>Còn {{ $timeString }}
                                </span>
                            @else
                                <span class="task-badge">
                                    <i class="far fa-clock me-1"></i>Còn {{ $timeString }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="card-content">
                        <div class="task-meta-grid">
                            <div class="task-meta-item animate-slide-up" style="--delay: 0.1s">
                                <div class="task-meta-icon">
                                    <i class="fas fa-user text-dark"></i>
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
                                    <div class="task-meta-label">Loại công việc</div>
                                    <div class="task-meta-value">
                                        {{ $task->for_departments ? 'Công việc phòng ban' : 'Công việc cá nhân' }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="task-section animate-slide-up" style="--delay: 0.5s">
                            <div class="task-section-header">
                                <i class="fas fa-align-left"></i>
                                <h6>Mô tả công việc</h6>
                            </div>
                            <div class="task-section-content">
                                <div class="task-description">
                                    {!! nl2br(e($task->description)) !!}
                                </div>
                            </div>
                        </div>

                        @if ($task->attachments->count() > 0)
                            <div class="task-section animate-slide-up" style="--delay: 0.7s">
                                <div class="task-section-header">
                                    <i class="fas fa-paperclip"></i>
                                    <h6>Tệp đính kèm</h6>
                                </div>
                                <div class="task-section-content">
                                    <ul class="attachment-list">
                                        @foreach ($task->attachments as $attachment)
                                            <li class="attachment-item">
                                                <div class="attachment-icon">
                                                    @if ($attachment->isDocument())
                                                        <i class="fas fa-file-alt"></i>
                                                    @elseif($attachment->isSpreadsheet())
                                                        <i class="fas fa-file-excel"></i>
                                                    @elseif($attachment->isVideo())
                                                        <i class="fas fa-file-video"></i>
                                                    @elseif($attachment->isImage())
                                                        <i class="fas fa-file-image"></i>
                                                    @else
                                                        <i class="fas fa-file"></i>
                                                    @endif
                                                </div>
                                                <div class="attachment-info">
                                                    <div class="attachment-name">{{ $attachment->original_filename }}
                                                    </div>
                                                    <div class="attachment-meta">
                                                        <span
                                                            class="attachment-size">{{ number_format($attachment->file_size / 1024, 2) }}
                                                            KB</span>
                                                        <span class="attachment-uploader">Uploaded by:
                                                            {{ $attachment->uploader->name }}</span>
                                                    </div>
                                                </div>
                                                <div class="attachment-action">
                                                    <a href="{{ route('tasks.attachments.download', $attachment) }}"
                                                        class="btn-download">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif

                        @if ($canUpdateStatus)
                            <div class="task-section animate-slide-up" style="--delay: 0.6s">
                                <div class="task-section-header">
                                    <i class="fas fa-sync-alt"></i>
                                    <h6>Cập nhật trạng thái</h6>
                                </div>
                                <div class="task-section-content">
                                    @php
                                        $userTaskPivot = $task->users->where('id', Auth::id())->first()->pivot;

                                        $currentAttempt =
                                            DB::table('task_user')
                                                ->where('task_id', $userTaskPivot->task_id)
                                                ->where('user_id', Auth::id())
                                                ->value('completion_attempt') ?? 0;
                                        $currentStatus = $userTaskPivot->status;
                                        $isApproved = $currentStatus === 'approved';
                                        $isRejected = $currentStatus === 'rejected';
                                        $isApprovalRejected = $currentStatus === 'approval_rejected';
                                        $canUpdateStatus = !$isApproved && !$isRejected;
                                    @endphp

                                    @if ($isApprovalRejected)
                                        <div class="rejection-alert">
                                            <div class="rejection-header">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                <span>Kết quả công việc không đạt yêu cầu</span>
                                            </div>
                                            <div class="rejection-message">
                                                @php
                                                    $rejectionReason = json_decode(
                                                        $userTaskPivot->approved_rejected_reason,
                                                        true,
                                                    );
                                                    $rejectionMessage =
                                                        $rejectionReason['message'] ?? 'Không có lý do cụ thể';
                                                @endphp
                                                <p><strong>Trạng thái hiện tại:</strong> <span class="badge bg-danger">Từ
                                                        chối kết quả</span></p>
                                                <p><strong>Lý do:</strong> {{ $rejectionMessage }}</p>
                                                <p><strong>Từ chối bởi:</strong>
                                                    {{ App\Models\User::find($userTaskPivot->approved_by)->name ?? 'Không xác định' }}
                                                </p>
                                                <p><strong>Thời gian:</strong>

                                                    {{ $userTaskPivot->approved_at ? \Carbon\Carbon::parse($userTaskPivot->approved_at)->format('d/m/Y H:i') : 'N/A' }}
                                                </p>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($canUpdateStatus)
                                        @if (
                                            $userTaskPivot->status == 'sending' ||
                                                $userTaskPivot->status == 'viewed' ||
                                                $userTaskPivot->status == 'approval_rejected' ||
                                                $userTaskPivot->status == 'in_progress')
                                            <form id="statusUpdateForm" action="{{ route('tasks.update-status', $task) }}"
                                                method="POST" class="status-update-form">
                                                @csrf
                                                @method('PATCH')
                                                <div class="form-flex">
                                                    <div class="form-flex-main">
                                                        <select name="status" id="taskStatus" class="form-select-sm">
                                                            <option value="in_progress"
                                                                {{ $currentStatus === 'in_progress' || $currentStatus === 'sending' || $currentStatus === 'viewed' ? 'selected' : '' }}>
                                                                Đang thực hiện</option>
                                                            <option value="completed"
                                                                {{ $currentStatus === 'completed' || $currentStatus === 'approval_rejected' ? 'selected' : '' }}>
                                                                Đã hoàn thành</option>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <button type="button" id="updateStatusBtn"
                                                            class="btn-submit btn-sm btn">
                                                            <i class="fas fa-save"></i> Cập nhật
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        @elseif ($currentStatus == 'completed')
                                            <div class="status-completed">
                                                <i class="fas fa-check-circle"></i>
                                                <span>Đã hoàn thành lúc:
                                                    {{ $userTaskPivot->completion_date ? \Carbon\Carbon::parse($userTaskPivot->completion_date)->format('d/m/Y H:i') : 'N/A' }}</span>
                                            </div>
                                        @elseif ($currentStatus == 'approved')
                                            <div class="status-approved">
                                                <i class="fas fa-check"></i>
                                                <span>Đã phê duyệt lúc:
                                                    {{ $userTaskPivot->approved_at ? \Carbon\Carbon::parse($userTaskPivot->approved_at)->format('d/m/Y H:i') : 'N/A' }}</span>
                                            </div>
                                            <div class="status-approved-by">
                                                <i class="fas fa-user-check"></i>
                                                <span>Được phê duyệt bởi:
                                                    {{ App\Models\User::find($userTaskPivot->approved_by)->name ?? 'N/A' }}</span>
                                            </div>
                                        @elseif ($currentStatus == 'rejected')
                                            <div class="status-rejected">
                                                <i class="fas fa-times-circle"></i>
                                                <span>Đã hủy task lúc:
                                                    {{ $userTaskPivot->approved_at ? \Carbon\Carbon::parse($userTaskPivot->approved_at)->format('d/m/Y H:i') : 'N/A' }}</span>
                                            </div>
                                        @endif



                                        @if ($currentStatus == 'completed' || $currentStatus == 'approval_rejected' || $currentStatus == 'approved')
                                            @php
                                                $taskUserId = $userTaskPivot->task_id;
                                                $hasAttachments =
                                                    isset($taskUserAttachments[$taskUserId]) &&
                                                    count($taskUserAttachments[$taskUserId]) > 0;
                                            @endphp


                                            <div class="task-files-section mt-4">
                                                <h6 class="d-flex align-items-center mb-3">
                                                    <i class="fas fa-paperclip me-2"></i>
                                                    File báo cáo kết quả
                                                    @if ($currentStatus == 'completed')
                                                        <span class="badge bg-primary ms-2">Chờ phê duyệt</span>
                                                    @elseif($currentStatus == 'approval_rejected')
                                                        <span class="badge bg-danger ms-2">Đã từ chối</span>
                                                    @elseif($currentStatus == 'approved')
                                                        <span class="badge bg-success ms-2">Đã phê duyệt</span>
                                                    @endif
                                                </h6>


                                                @if ($hasAttachments)
                                                    <div class="list-group">
                                                        @foreach ($taskUserAttachments[$taskUserId] as $file)
                                                            @if ($file->completion_attempt == $currentAttempt)
                                                                <div
                                                                    class="list-group-item list-group-item-action d-flex align-items-center">
                                                                    <div class="me-3 d-flex align-items-center justify-content-center bg-light rounded"
                                                                        style="width: 42px; height: 42px;">
                                                                        @php
                                                                            $fileType = strtolower($file->file_type);
                                                                            $iconClass = match (true) {
                                                                                in_array($fileType, ['pdf'])
                                                                                    => 'fas fa-file-pdf text-danger',
                                                                                in_array($fileType, ['doc', 'docx'])
                                                                                    => 'fas fa-file-word text-primary',
                                                                                in_array($fileType, [
                                                                                    'xls',
                                                                                    'xlsx',
                                                                                    'csv',
                                                                                ])
                                                                                    => 'fas fa-file-excel text-success',
                                                                                in_array($fileType, ['ppt', 'pptx'])
                                                                                    => 'fas fa-file-powerpoint text-warning',
                                                                                in_array($fileType, [
                                                                                    'jpg',
                                                                                    'jpeg',
                                                                                    'png',
                                                                                    'gif',
                                                                                    'bmp',
                                                                                ])
                                                                                    => 'fas fa-file-image text-info',
                                                                                in_array($fileType, [
                                                                                    'mp4',
                                                                                    'avi',
                                                                                    'mov',
                                                                                    'wmv',
                                                                                ])
                                                                                    => 'fas fa-file-video text-purple',
                                                                                in_array($fileType, [
                                                                                    'zip',
                                                                                    'rar',
                                                                                    '7z',
                                                                                ])
                                                                                    => 'fas fa-file-archive text-secondary',
                                                                                default => 'fas fa-file text-muted',
                                                                            };
                                                                        @endphp
                                                                        <i class="{{ $iconClass }}"></i>
                                                                    </div>

                                                                    <div class="flex-grow-1">
                                                                        <div class="fw-medium">
                                                                            {{ $file->original_filename }}</div>
                                                                        <div
                                                                            class="d-flex align-items-center text-muted small flex-wrap">
                                                                            <div class="me-3">
                                                                                <i class="fas fa-weight me-1"></i>
                                                                                {{ number_format($file->file_size / 1024, 2) }}
                                                                                KB
                                                                            </div>
                                                                            <div>
                                                                                <i class="fas fa-clock me-1"></i>
                                                                                {{ \Carbon\Carbon::parse($file->created_at)->format('H:i:s d/m/Y') }}
                                                                            </div>
                                                                        </div>
                                                                        @if ($file->description)
                                                                            <div class="mt-2">
                                                                                <div
                                                                                    class="text-muted small bg-light p-2 rounded border">
                                                                                    <i class="fas fa-comment-alt me-1"></i>
                                                                                    {{ $file->description }}
                                                                                </div>
                                                                            </div>
                                                                        @endif
                                                                    </div>

                                                                    <div>
                                                                        <a href="{{ route('tasks.user-attachments.download', $file->id) }}"
                                                                            class="btn btn-sm btn-outline-primary"
                                                                            title="Tải xuống">
                                                                            <i class="fas fa-download"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="alert alert-info">
                                                        <i class="fas fa-info-circle me-2"></i>
                                                        Chưa có file báo cáo kết quả nào được đính kèm.
                                                    </div>
                                                @endif
                                            </div>
                                        @endif

                                        @php
                                            // Tính thời gian còn lại đến deadline
                                            $deadline = $task->deadline;
                                            $now = now();
                                            $hoursRemaining = $deadline->diffInHours($now, true);

                                        @endphp

                                        @if ($hoursRemaining <= 12 && $hoursRemaining > -24 && !in_array($currentStatus, ['approved', 'rejected']))
                                            <div class="task-extension-action mt-3">
                                                <a href="{{ route('task-extensions.request', $task) }}"
                                                    class="btn-extension">
                                                    <i class="fas fa-clock"></i> Xin gia hạn
                                                    @if ($hoursRemaining <= 0)
                                                        <span class="badge bg-danger ms-1">Đã quá hạn</span>
                                                    @else
                                                        <span class="badge bg-warning ms-1">Còn {{ $hoursRemaining }}
                                                            giờ</span>
                                                    @endif
                                                </a>
                                            </div>
                                        @endif
                                    @else
                                        <div class="status-locked">
                                            <i class="fas fa-lock"></i>
                                            <span>{{ $isApproved ? 'Công việc đã được phê duyệt, không thể thay đổi trạng thái.' : 'Công việc đã bị từ chối, không thể thay đổi trạng thái.' }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                        <div class="modal fade" id="uploadFileModal" tabindex="-1"
                            aria-labelledby="uploadFileModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="uploadFileModalLabel">Đính kèm file
                                            báo cáo kết quả</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Vui lòng đính kèm ít nhất một file báo cáo kết quả để hoàn thành
                                            công việc
                                        </div>

                                        <div id="fileUploadErrors" class="alert alert-danger d-none">
                                            <ul id="errorList"></ul>
                                        </div>

                                        <form id="fileUploadForm" enctype="multipart/form-data">
                                            @csrf
                                            <input type="hidden" name="status" id="hiddenStatus" value="completed">

                                            <div class="mb-3">
                                                <label for="completion_files" class="form-label">File báo
                                                    cáo kết quả <span class="text-danger">*</span></label>
                                                <input type="file" class="form-control" id="completion_files"
                                                    name="completion_files[]" multiple>
                                                <div class="form-text">Định dạng cho phép: PDF, Word,
                                                    Excel, PowerPoint, ảnh, ZIP, RAR (tối đa 10MB/file)
                                                </div>
                                            </div>

                                            {{-- <div class="mb-3">
                                                <label for="file_description" class="form-label">Mô tả kết
                                                    quả (tùy chọn)</label>
                                                <textarea class="form-control" id="file_description" name="file_description" rows="3"
                                                    placeholder="Mô tả kết quả công việc hoặc ghi chú về file báo cáo"></textarea>
                                            </div> --}}

                                            <div class="selected-files mt-3 d-none">
                                                <h6 class="mb-2">File đã chọn:</h6>
                                                <ul id="selectedFilesList" class="list-group"></ul>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Hủy</button>
                                        <button type="button" class="btn btn-primary" id="submitTaskCompletion">
                                            <span class="spinner-border spinner-border-sm d-none" role="status"
                                                aria-hidden="true"></span>
                                            <span>Cập nhật & Hoàn thành</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>



                        @if (isset($userTaskPivot) && $userTaskPivot->approved_rejected > 0)
                            <div class="rejection-history mt-4">
                                <h6>Lịch sử từ chối kết quả ({{ $userTaskPivot->approved_rejected }} lần)</h6>

                                @php
                                    $rejectionData = json_decode($userTaskPivot->approved_rejected_reason, true);
                                    $rejectionHistory = $rejectionData['history'] ?? [];
                                @endphp

                                @foreach ($rejectionHistory as $index => $rejection)
                                    <div class="rejection-item mt-3 p-3 bg-light border rounded">
                                        <div class="rejection-count border-bottom pb-1 mb-2 fw-bold">Lần từ chối
                                            #{{ $index + 1 }}</div>
                                        <div class="rejection-details">
                                            <p class="mb-1"><strong>Lý do:</strong> {{ $rejection['message'] }}</p>
                                            <p class="mb-1"><strong>Thời gian:</strong>
                                                {{ \Carbon\Carbon::parse($rejection['rejected_at'])->format('d/m/Y H:i') }}
                                            </p>
                                            <p class="mb-0"><strong>Người từ chối:</strong>
                                                {{ $rejection['rejected_by'] }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="card-footer">
                        <a href="{{ route('tasks.index') }}" class="btn-back">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>

                        @if (Auth::user()->isAdmin())
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
                @php
                    // Define which users can see details about departments and users
                    $canSeeAllDetails =
                        Auth::user()->isAdmin() || Auth::user()->isDirector() || Auth::user()->isDeputyDirector();
                    $userDepartmentId = Auth::user()->department_id;
                    $isManager = Auth::user()->isDepartmentHead() || Auth::user()->isDeputyDepartmentHead();
                    $isStaff = Auth::user()->isStaff();
                @endphp

                @if ($task->departments->count() > 0 && ($canSeeAllDetails || $isManager))
                    <div class="content-card side-card animate-slide-up" style="--delay: 0.7s">
                        <div class="card-top">
                            <div class="card-title">
                                <i class="fas fa-building icon-title"></i>
                                <h5>Phòng ban được giao</h5>
                            </div>
                        </div>
                        <div class="card-content">
                            <ul class="department-list">
                                @foreach ($task->departments as $index => $department)
                                    {{-- Only show departments if user has access --}}
                                    @if ($canSeeAllDetails || ($isManager && $department->id == $userDepartmentId))
                                        <li class="department-item animate-slide-up"
                                            style="--delay: {{ 0.7 + $index * 0.1 }}s">
                                            <div class="department-name">
                                                <i class="fas fa-sitemap"></i>
                                                <span>{{ $department->name }}</span>
                                            </div>
                                            @php
                                                // Calculate department progress
                                                $deptUsers = $task->users->filter(function ($user) use ($department) {
                                                    return $user->department_id == $department->id;
                                                });

                                                $totalDeptUsers = $deptUsers->count();
                                                $completedDeptUsers = $deptUsers
                                                    ->filter(function ($user) {
                                                        return $user->pivot->status == 'completed' ||
                                                            $user->pivot->status == 'approved';
                                                    })
                                                    ->count();

                                                $percentComplete =
                                                    $totalDeptUsers > 0
                                                        ? ($completedDeptUsers / $totalDeptUsers) * 100
                                                        : 0;
                                            @endphp

                                            <div class="department-progress">
                                                <div class="progress-stats">
                                                    <span
                                                        class="progress-text">{{ $completedDeptUsers }}/{{ $totalDeptUsers }}
                                                        hoàn thành</span>
                                                    <span class="progress-percent">{{ round($percentComplete) }}%</span>
                                                </div>
                                                <div class="progress">
                                                    <div class="progress-bar bg-success" role="progressbar"
                                                        style="width: {{ $percentComplete }}%"
                                                        aria-valuenow="{{ $percentComplete }}" aria-valuemin="0"
                                                        aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                        </li>
                                    @endif
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
                                {{-- Filter users based on viewer's role and department --}}
                                @if (
                                    $canSeeAllDetails ||
                                        Auth::id() == $user->id ||
                                        ($isManager && $user->department_id == $userDepartmentId) ||
                                        $task->created_by == Auth::id())
                                    <li class="assignee-item animate-slide-up"
                                        style="--delay: {{ 0.8 + $index * 0.1 }}s">
                                        <div class="assignee-main">
                                            <div class="assignee-info">
                                                <div class="assignee-avatar">
                                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=random"
                                                        alt="{{ $user->name }}">
                                                </div>
                                                <div class="assignee-details">
                                                    <div class="assignee-name">{{ $user->name }}</div>
                                                    <div class="assignee-dept">
                                                        <i class="fas fa-sitemap"></i>
                                                        {{ $user->department->name ?? 'N/A' }}
                                                        @if ($user->role)
                                                            <span class="assignee-role">
                                                                <i class="fas fa-user-tag"></i> {{ $user->role->name }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            @switch($user->pivot->status)
                                                @case('sending')
                                                    <span class="status-badge status-inactive fs-9">Chưa xem</span>
                                                @break

                                                @case('viewed')
                                                    <span class="status-badge status-pending fs-9">Đã xem</span>
                                                @break

                                                @case('in_progress')
                                                    <span class="status-badge bg-info text-white fs-9">Đang thực hiện</span>
                                                @break

                                                @case('completed')
                                                    <span class="status-badge status-active fs-9">Hoàn thành</span>
                                                @break

                                                @case('approved')
                                                    <span class="status-badge bg-success fs-9">Đã phê duyệt</span>
                                                @break

                                                @case('approval_rejected')
                                                    <span class="status-badge bg-danger fs-9">Từ chối kết quả</span>
                                                @break

                                                @default
                                                    <span class="status-badge bg-secondary fs-9">{{ $user->pivot->status }}</span>
                                            @endswitch
                                        </div>

                                        @if ($user->pivot->status === 'completed' || $user->pivot->status === 'approved')
                                            <div class="completion-info">
                                                <div class="completion-time">
                                                    <i class="fas fa-check-circle"></i>
                                                    <span>Hoàn thành lúc:
                                                        {{ $user->pivot->completion_date ? \Carbon\Carbon::parse($user->pivot->completion_date)->format('d/m/Y H:i') : 'N/A' }}</span>
                                                </div>

                                                @if ($user->pivot->status === 'approved')
                                                    <div class="approval-info approved">
                                                        <i class="fas fa-check"></i>
                                                        <span>Đã xác nhận bởi
                                                            {{ App\Models\User::find($user->pivot->approved_by)->name ?? 'N/A' }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif

                                        @if ($user->pivot->viewed_at)
                                            <div class="viewed-info">
                                                <i class="fas fa-eye"></i>
                                                <span>Đã xem lúc
                                                    {{ \Carbon\Carbon::parse($user->pivot->viewed_at)->format('d/m/Y H:i') }}</span>
                                            </div>
                                        @endif
                                    </li>
                                @endif
                                @empty
                                    <li class="empty-item">Không có người được giao</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>



                    @if ($task->for_departments && ($canSeeAllDetails || $isManager))
                        <div class="content-card side-card animate-slide-up" style="--delay: 1.0s">
                            <div class="card-top">
                                <div class="card-title">
                                    <i class="fas fa-chart-pie icon-title"></i>
                                    <h5>Tiến độ công việc</h5>
                                </div>
                            </div>
                            <div class="card-content">

                                @php
                                    $taskStats = [
                                        'total' => 0,
                                        'sending' => 0,
                                        'viewed' => 0,
                                        'in_progress' => 0,
                                        'completed' => 0,
                                        'approved' => 0,
                                        'approval_rejected' => 0,
                                        'rejected' => 0,
                                    ];

                                    // Filter users by department if needed
                                    $filteredUsers = $task->users;
                                    if (!$canSeeAllDetails && $isManager) {
                                        $filteredUsers = $filteredUsers->filter(function ($user) use (
                                            $userDepartmentId,
                                        ) {
                                            return $user->department_id == $userDepartmentId;
                                        });
                                    }

                                    $taskStats['total'] = $filteredUsers->count();

                                    foreach ($filteredUsers as $user) {
                                        $taskStats[$user->pivot->status]++;
                                    }
                                @endphp

                                <div class="task-progress-summary">
                                    <div class="progress-stat-item">
                                        <div class="progress-stat-value">{{ $taskStats['total'] }}</div>
                                        <div class="progress-stat-label">Tổng số</div>
                                    </div>

                                    <div class="progress-stat-item">

                                        <div class="progress-stat-value text-danger">
                                            {{ $taskStats['sending'] + $taskStats['viewed'] }}</div>
                                        <div class="progress-stat-label">Chưa thực hiện</div>
                                    </div>

                                    <div class="progress-stat-item">
                                        <div class="progress-stat-value text-info">{{ $taskStats['in_progress'] }}</div>
                                        <div class="progress-stat-label">Đang thực hiện</div>
                                    </div>

                                    <div class="progress-stat-item">
                                        <div class="progress-stat-value text-success">
                                            {{ $taskStats['completed'] + $taskStats['approved'] }}</div>
                                        <div class="progress-stat-label">Hoàn thành</div>
                                    </div>
                                </div>

                                <div class="task-progress-chart">
                                    <canvas id="taskProgressChart"></canvas>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    @endsection

    @push('styles')
        <link rel="stylesheet" href="{{ asset('assets/css/show.css') }}">
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Khởi tạo modal
                const uploadModal = new bootstrap.Modal(document.getElementById('uploadFileModal'));
                const taskStatusSelect = document.getElementById('taskStatus');
                const updateStatusBtn = document.getElementById('updateStatusBtn');
                const fileUploadForm = document.getElementById('fileUploadForm');
                const statusForm = document.getElementById('statusUpdateForm');
                const hiddenStatus = document.getElementById('hiddenStatus');
                const fileInput = document.getElementById('completion_files');
                const submitBtn = document.getElementById('submitTaskCompletion');
                const spinner = submitBtn.querySelector('.spinner-border');
                const errorContainer = document.getElementById('fileUploadErrors');
                const errorList = document.getElementById('errorList');
                const selectedFilesContainer = document.querySelector('.selected-files');
                const selectedFilesList = document.getElementById('selectedFilesList');

                // Khi người dùng nhấn nút Cập nhật
                if (updateStatusBtn) {
                    updateStatusBtn.addEventListener('click', function(e) {
                        e.preventDefault();

                        // Lấy giá trị status hiện tại
                        const selectedStatus = taskStatusSelect.value;

                        // Nếu status là "completed", hiện modal upload file
                        if (selectedStatus === 'completed') {
                            // Cập nhật giá trị status trong form modal
                            hiddenStatus.value = selectedStatus;

                            // Hiển thị modal
                            uploadModal.show();
                        } else {
                            // Nếu không phải "completed", submit form bình thường
                            statusForm.submit();
                        }
                    });
                }

                // Hiển thị danh sách file đã chọn
                if (fileInput) {
                    fileInput.addEventListener('change', function() {
                        // Xóa danh sách file trước đó
                        selectedFilesList.innerHTML = '';

                        if (this.files.length > 0) {
                            selectedFilesContainer.classList.remove('d-none');

                            // Thêm từng file vào danh sách
                            Array.from(this.files).forEach((file, index) => {
                                const fileSize = (file.size / 1024).toFixed(2);
                                const fileExtension = file.name.split('.').pop().toLowerCase();

                                // Xác định icon dựa trên loại file
                                let iconClass = 'fas fa-file';
                                if (['pdf'].includes(fileExtension)) {
                                    iconClass = 'fas fa-file-pdf text-danger';
                                } else if (['doc', 'docx'].includes(fileExtension)) {
                                    iconClass = 'fas fa-file-word text-primary';
                                } else if (['xls', 'xlsx'].includes(fileExtension)) {
                                    iconClass = 'fas fa-file-excel text-success';
                                } else if (['ppt', 'pptx'].includes(fileExtension)) {
                                    iconClass = 'fas fa-file-powerpoint text-warning';
                                } else if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExtension)) {
                                    iconClass = 'fas fa-file-image text-info';
                                } else if (['zip', 'rar'].includes(fileExtension)) {
                                    iconClass = 'fas fa-file-archive text-secondary';
                                }

                                const listItem = document.createElement('li');
                                listItem.className = 'list-group-item d-flex align-items-center';
                                listItem.innerHTML = `
                        <i class="${iconClass} me-2"></i>
                        <div class="ms-2 flex-grow-1">
                            <div>${file.name}</div>
                            <small class="text-muted">${fileSize} KB</small>
                        </div>
                        <button type="button" class="btn-close remove-file" data-index="${index}" aria-label="Remove"></button>
                    `;
                                selectedFilesList.appendChild(listItem);
                            });

                            // Thêm event listener cho các nút xóa file
                            document.querySelectorAll('.remove-file').forEach(btn => {
                                btn.addEventListener('click', function() {
                                    // Không thể xóa trực tiếp từ FileList, phải tạo DataTransfer mới
                                    // Thay vào đó, chỉ cần xóa hiển thị và kiểm tra khi submit
                                    this.closest('li').remove();

                                    // Ẩn container nếu không còn file nào
                                    if (selectedFilesList.children.length === 0) {
                                        selectedFilesContainer.classList.add('d-none');
                                    }
                                });
                            });
                        } else {
                            selectedFilesContainer.classList.add('d-none');
                        }
                    });
                }

                // Xử lý khi nhấn nút hoàn thành trong modal
                if (submitBtn) {
                    submitBtn.addEventListener('click', function() {
                        // Kiểm tra xem có file nào được chọn không
                        if (fileInput.files.length === 0) {
                            showError(['Vui lòng đính kèm ít nhất một file báo cáo kết quả']);
                            return;
                        }

                        // Hiển thị spinner
                        spinner.classList.remove('d-none');
                        submitBtn.disabled = true;

                        // Tạo FormData từ form upload
                        const formData = new FormData(fileUploadForm);

                        // Thêm các trường từ form chính
                        formData.append('_token', document.querySelector('input[name="_token"]').value);
                        formData.append('_method', 'PATCH');
                        formData.append('status', hiddenStatus.value);

                        // Gửi request Ajax
                        fetch('{{ route('tasks.update-status', $task) }}', {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            })
                            .then(response => {
                                if (!response.ok) {
                                    return response.json().then(data => {
                                        throw new Error(JSON.stringify(data));
                                    });
                                }
                                return response.json();
                            })
                            .then(data => {
                                // Xử lý kết quả thành công
                                if (data.success) {
                                    // Đóng modal
                                    uploadModal.hide();

                                    // Hiển thị thông báo thành công
                                    Swal.fire({
                                        title: 'Thành công!',
                                        text: data.message ||
                                            'Cập nhật trạng thái công việc thành công',
                                        icon: 'success',
                                        confirmButtonText: 'OK'
                                    }).then(() => {
                                        // Tải lại trang
                                        window.location.reload();
                                    });
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);

                                // Xử lý lỗi
                                try {
                                    const errorData = JSON.parse(error.message);
                                    if (errorData.errors) {
                                        const errorMessages = [];
                                        for (const field in errorData.errors) {
                                            errorData.errors[field].forEach(message => {
                                                errorMessages.push(message);
                                            });
                                        }
                                        showError(errorMessages);
                                    } else if (errorData.message) {
                                        showError([errorData.message]);
                                    } else {
                                        showError(['Đã xảy ra lỗi khi cập nhật trạng thái']);
                                    }
                                } catch (e) {
                                    showError(['Đã xảy ra lỗi khi cập nhật trạng thái']);
                                }
                            })
                            .finally(() => {
                                // Ẩn spinner
                                spinner.classList.add('d-none');
                                submitBtn.disabled = false;
                            });
                    });
                }

                // Hàm hiển thị thông báo lỗi
                function showError(messages) {
                    errorList.innerHTML = '';
                    messages.forEach(message => {
                        const li = document.createElement('li');
                        li.textContent = message;
                        errorList.appendChild(li);
                    });
                    errorContainer.classList.remove('d-none');
                }

                // Ẩn thông báo lỗi khi modal được đóng
                document.getElementById('uploadFileModal').addEventListener('hidden.bs.modal', function() {
                    errorContainer.classList.add('d-none');
                    errorList.innerHTML = '';
                });
            });
        </script>
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

                // Initialize chart if element exists
                const chartElement = document.getElementById('taskProgressChart');
                if (chartElement) {
                    @php
                        // Get statistics for chart
                        $taskStats = [
                            'sending' => 0,
                            'viewed' => 0,
                            'in_progress' => 0,
                            'completed' => 0,
                            'approved' => 0,
                            'approval_rejected' => 0,
                            'rejected' => 0,
                        ];

                        // Filter users by department if needed
                        $filteredUsers = $task->users;
                        if (!$canSeeAllDetails && $isManager) {
                            $filteredUsers = $filteredUsers->filter(function ($user) use ($userDepartmentId) {
                                return $user->department_id == $userDepartmentId;
                            });
                        }

                        foreach ($filteredUsers as $user) {
                            $taskStats[$user->pivot->status] = ($taskStats[$user->pivot->status] ?? 0) + 1;
                        }
                    @endphp

                    // Chart data
                    const chartData = {
                        labels: [
                            'Chưa xem ({{ $taskStats['sending'] }})',
                            'Đã xem ({{ $taskStats['viewed'] }})',
                            'Đang thực hiện ({{ $taskStats['in_progress'] }})',
                            'Hoàn thành ({{ $taskStats['completed'] }})',
                            'Đã phê duyệt ({{ $taskStats['approved'] }})',
                            'Từ chối kết quả ({{ $taskStats['approval_rejected'] }})',
                            'Đã hủy ({{ $taskStats['rejected'] }})'
                        ],
                        datasets: [{
                            data: [
                                {{ $taskStats['sending'] }},
                                {{ $taskStats['viewed'] }},
                                {{ $taskStats['in_progress'] }},
                                {{ $taskStats['completed'] }},
                                {{ $taskStats['approved'] }},
                                {{ $taskStats['approval_rejected'] }},
                                {{ $taskStats['rejected'] }}
                            ],
                            backgroundColor: [
                                '#f87979',
                                '#ffcd56',
                                '#4bc0c0',
                                '#36a2eb',
                                '#9966ff',
                                '#dc3545',
                                '#6c757d'
                            ]
                        }]
                    };

                    // Chart options
                    const chartOptions = {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 12,
                                    font: {
                                        size: 11
                                    }
                                }
                            }
                        }
                    };

                    // Create the chart
                    new Chart(chartElement, {
                        type: 'doughnut',
                        data: chartData,
                        options: chartOptions
                    });
                }
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
                    if (confirm('Bạn có chắc chắn muốn xóa công việc này?')) {
                        form.submit();
                    }
                }
            }

            document.addEventListener('DOMContentLoaded', function() {
                const statusSelect = document.querySelector('select[name="status"]');
                const rejectionContainer = document.querySelector('.rejection-reason-container');

                if (statusSelect && rejectionContainer) {
                    statusSelect.addEventListener('change', function() {
                        if (this.value === 'approval_rejected') {
                            rejectionContainer.style.display = 'block';
                        } else {
                            rejectionContainer.style.display = 'none';
                        }
                    });
                }
            });
        </script>
        <script>
            // Global variables to store current form state
            let currentUserId = null;
            let currentStatus = null;

            // Function to handle approval form submission
            function handleApprovalSubmit(userId) {
                currentUserId = userId;
                const statusSelect = document.querySelector(`select.status-select[data-user-id="${userId}"]`);
                currentStatus = statusSelect.value;

                // If selected status is "approval_rejected", show the modal
                if (currentStatus === 'approval_rejected') {
                    $('#rejectionModal').modal('show');
                } else {
                    // Otherwise, just submit the form
                    document.getElementById(`approvalForm${userId}`).submit();
                }
            }

            // When the DOM is loaded
            document.addEventListener('DOMContentLoaded', function() {
                // Handle rejection confirmation
                const confirmRejectionBtn = document.getElementById('confirmRejection');
                if (confirmRejectionBtn) {
                    confirmRejectionBtn.addEventListener('click', function() {
                        const rejectionText = document.getElementById('rejectionReasonText').value.trim();

                        // Validate that rejection reason is provided
                        if (!rejectionText) {
                            document.getElementById('rejectionReasonText').classList.add('is-invalid');
                            return;
                        }

                        // Set the rejection reason in the form
                        document.getElementById(`rejectionReasonInput${currentUserId}`).value = rejectionText;

                        // Hide modal and submit form
                        $('#rejectionModal').modal('hide');
                        document.getElementById(`approvalForm${currentUserId}`).submit();
                    });
                }

                // Reset validation when typing
                const rejectionReasonText = document.getElementById('rejectionReasonText');
                if (rejectionReasonText) {
                    rejectionReasonText.addEventListener('input', function() {
                        this.classList.remove('is-invalid');
                    });
                }
            });
        </script>

        <script>
            // Thêm vào phần scripts
            document.addEventListener('DOMContentLoaded', function() {
                const extensionBtn = document.querySelector('.btn-extension');
                if (extensionBtn) {
                    // Lấy số giờ còn lại từ badge
                    const hoursBadge = extensionBtn.querySelector('.badge');
                    if (hoursBadge && hoursBadge.textContent.includes('Còn')) {
                        const hoursText = hoursBadge.textContent.replace('Còn ', '').replace(' giờ', '');
                        const hours = parseInt(hoursText);

                        // Nếu còn dưới 3 giờ, thêm hiệu ứng nhấp nháy
                        if (hours <= 3) {
                            extensionBtn.classList.add('urgent-extension');
                        }
                    }
                }
            });
        </script>
    @endpush
