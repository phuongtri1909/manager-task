<div class="data-table-container">
    <table class="data-table extension-table">
        <thead>
            <tr>
                <th class="column-small">ID</th>
                <th class="column-large">Công việc</th>
                <th class="column-medium">Người yêu cầu</th>
                <th class="column-medium">Thời hạn hiện tại</th>
                <th class="column-medium">Thời hạn mới</th>
                <th class="column-small text-center">Trạng thái</th>
                <th class="column-small text-center">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            @forelse($extensionList as $extension)
                <tr class="extension-row" data-id="{{ $extension->id }}">
                    <td class="text-center">{{ $extension->id }}</td>
                    <td class="item-title">
                        <a href="{{ route('tasks.show', $extension->task_id) }}" class="task-link">
                            {{ Str::limit($extension->task->title ?? 'N/A', 40) }}
                        </a>
                        @if(now()->diffInHours($extension->requested_at) < 24)
                            <span class="filter-tag" style="background-color: #e3fcef; color: #00875a;">Mới</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($extension->user->name ?? 'N/A') }}&background=random" 
                                 class="rounded-circle me-2" alt="User Avatar" width="24" height="24">
                            {{ $extension->user->name ?? 'N/A' }}
                        </div>
                    </td>
                    <td>
                        {{ $extension->task->deadline ? $extension->task->deadline->format('d/m/Y H:i') : 'N/A' }}
                        @if($extension->task && now() > $extension->task->deadline)
                            <span class="filter-tag" style="background-color: #fef0f0; color: #e53935;">Quá hạn</span>
                        @endif
                    </td>
                    <td>
                        {{ \Carbon\Carbon::parse($extension->new_deadline)->format('d/m/Y H:i') }}
                        <span class="filter-tag" style="background-color: #edf7ff; color: #0077c5;">
                            <i class="fas fa-clock me-1"></i>
                            +{{ \Carbon\Carbon::parse($extension->new_deadline)->diffInDays($extension->task->deadline) }} ngày
                        </span>
                    </td>
                    <td class="text-center">
                        @if($extension->status === 'pending')
                            <span class="status-badge status-warning">
                                <i class="fas fa-hourglass-half"></i> Đang chờ
                            </span>
                        @elseif($extension->status === 'approved')
                            <span class="status-badge status-active">
                                <i class="fas fa-check-circle"></i> Đã duyệt
                            </span>
                        @elseif($extension->status === 'rejected')
                            <span class="status-badge status-inactive">
                                <i class="fas fa-times-circle"></i> Từ chối
                            </span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="action-buttons-wrapper">
                            <button type="button" class="action-icon view-icon" data-bs-toggle="modal" data-bs-target="#extensionModal{{ $extension->id }}" title="Xem chi tiết">
                                <i class="fas fa-eye"></i>
                            </button>
                            
                            @if($extension->status === 'pending' && (Auth::user()->isAdmin() || Auth::user()->id === $extension->task->created_by || (Auth::user()->isDepartmentHead() && $extension->user->department_id === Auth::user()->department_id)))
                                <button type="button" class="action-icon approve-btn" 
                                        data-id="{{ $extension->id }}" 
                                        data-task="{{ $extension->task->title ?? 'Không xác định' }}"
                                        data-user="{{ $extension->user->name ?? 'Không xác định' }}"
                                        title="Phê duyệt">
                                    <i class="fas fa-check"></i>
                                </button>
                                
                                <button type="button" class="action-icon bg-danger reject-btn"
                                        data-id="{{ $extension->id }}" 
                                        data-task="{{ $extension->task->title ?? 'Không xác định' }}"
                                        data-user="{{ $extension->user->name ?? 'Không xác định' }}"
                                        title="Từ chối">
                                    <i class="fas fa-times"></i>
                                </button>
                                
                                <form id="approveForm{{ $extension->id }}" action="{{ route('task-extensions.update', $extension) }}" method="POST" class="d-none">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="approved">
                                </form>
                                
                                <form id="rejectForm{{ $extension->id }}" action="{{ route('task-extensions.update', $extension) }}" method="POST" class="d-none">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="rejected">
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                
                <!-- Modal for Extension Details -->
                <div class="modal fade" id="extensionModal{{ $extension->id }}" tabindex="-1" role="dialog" aria-labelledby="extensionModalLabel{{ $extension->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header {{ $extension->status === 'pending' ? 'bg-warning' : ($extension->status === 'approved' ? 'bg-success' : 'bg-danger') }} text-white">
                                <h5 class="modal-title" id="extensionModalLabel{{ $extension->id }}">
                                    <i class="fas {{ $extension->status === 'pending' ? 'fa-hourglass-half' : ($extension->status === 'approved' ? 'fa-check-circle' : 'fa-times-circle') }} me-2"></i>
                                    Chi tiết yêu cầu gia hạn #{{ $extension->id }}
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="text-muted">Công việc:</label>
                                            <div class="fw-bold">
                                                <a href="{{ route('tasks.show', $extension->task_id) }}">
                                                    {{ $extension->task->title ?? 'N/A' }}
                                                </a>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="text-muted">Người yêu cầu:</label>
                                            <div class="fw-bold">{{ $extension->user->name ?? 'N/A' }}</div>
                                            <small class="text-muted">{{ $extension->user->department->name ?? 'N/A' }}</small>
                                        </div>
                                        <div class="form-group">
                                            <label class="text-muted">Thời gian yêu cầu:</label>
                                            <div class="fw-bold">{{ $extension->requested_at->format('d/m/Y H:i') }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="text-muted">Thời hạn hiện tại:</label>
                                            <div class="fw-bold">{{ $extension->task->deadline ? $extension->task->deadline->format('d/m/Y H:i') : 'N/A' }}</div>
                                        </div>
                                        <div class="form-group">
                                            <label class="text-muted">Thời hạn mới:</label>
                                            <div class="fw-bold">{{ \Carbon\Carbon::parse($extension->new_deadline)->format('d/m/Y H:i') }}</div>
                                            <small class="text-info">
                                                Gia hạn thêm {{ \Carbon\Carbon::parse($extension->new_deadline)->diffInDays($extension->task->deadline) }} ngày
                                            </small>
                                        </div>
                                        <div class="form-group">
                                            <label class="text-muted">Trạng thái:</label>
                                            <div>
                                                @if($extension->status === 'pending')
                                                    <span class="badge bg-warning text-dark">Đang chờ duyệt</span>
                                                @elseif($extension->status === 'approved')
                                                    <span class="badge bg-success">Đã duyệt</span>
                                                    <div class="mt-1">
                                                        <small class="text-muted">Duyệt bởi: {{ $extension->approver->name ?? 'N/A' }}</small>
                                                        <br>
                                                        <small class="text-muted">Thời gian: {{ $extension->approved_at ? \Carbon\Carbon::parse($extension->approved_at)->format('d/m/Y H:i') : 'N/A' }}</small>
                                                    </div>
                                                @elseif($extension->status === 'rejected')
                                                    <span class="badge bg-danger">Từ chối</span>
                                                    <div class="mt-1">
                                                        <small class="text-muted">Từ chối bởi: {{ $extension->approver->name ?? 'N/A' }}</small>
                                                        <br>
                                                        <small class="text-muted">Thời gian: {{ $extension->rejected_at ? \Carbon\Carbon::parse($extension->rejected_at)->format('d/m/Y H:i') : 'N/A' }}</small>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="text-muted">Lý do yêu cầu gia hạn:</label>
                                    <div class="bg-light p-3 rounded">
                                        {!! nl2br(e($extension->reason)) !!}
                                    </div>
                                </div>
                                
                                @if($extension->status === 'rejected' && $extension->rejection_reason)
                                    <div class="form-group">
                                        <label class="text-danger">Lý do từ chối:</label>
                                        <div class="bg-light p-3 rounded border-start border-danger border-3">
                                            {!! nl2br(e($extension->rejection_reason)) !!}
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="modal-button secondary" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-1"></i>Đóng
                                </button>
                                
                                @if($extension->status === 'pending' && (Auth::user()->isAdmin() || Auth::user()->id === $extension->task->created_by || (Auth::user()->isDepartmentHead() && $extension->user->department_id === Auth::user()->department_id)))
                                    <button type="button" class="modal-button primary approve-btn" 
                                            data-id="{{ $extension->id }}" 
                                            data-task="{{ $extension->task->title ?? 'Không xác định' }}"
                                            data-user="{{ $extension->user->name ?? 'Không xác định' }}"
                                            data-bs-dismiss="modal">
                                        <i class="fas fa-check me-1"></i>Duyệt yêu cầu
                                    </button>
                                    
                                    <button type="button" class="modal-button danger reject-btn"
                                            data-id="{{ $extension->id }}" 
                                            data-task="{{ $extension->task->title ?? 'Không xác định' }}"
                                            data-user="{{ $extension->user->name ?? 'Không xác định' }}"
                                            data-bs-dismiss="modal">
                                        <i class="fas fa-times me-1"></i>Từ chối
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Không có yêu cầu gia hạn nào</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div> 