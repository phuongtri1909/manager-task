@extends('layouts.partials.sidebar')

@section('title', 'Yêu cầu gia hạn công việc')

@section('main-content')
    <div class="category-form-container">
        <!-- Breadcrumb -->
        <div class="content-breadcrumb">
            <ol class="breadcrumb-list">
                <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}">Công việc</a></li>
                <li class="breadcrumb-item"><a href="{{ route('tasks.show', $task) }}">Chi tiết</a></li>
                <li class="breadcrumb-item current">Yêu cầu gia hạn</li>
            </ol>
        </div>
        
        <div class="form-card">
            <div class="form-header">
                <div class="form-title">
                    <i class="fas fa-clock icon-title"></i>
                    <h5>Yêu cầu gia hạn cho công việc: {{ $task->title }}</h5>
                </div>
            </div>

            <div class="form-body">
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                <div class="task-info-container mb-4">
                    <div class="task-meta">
                        <div class="task-meta-item">
                            <div class="task-meta-label"><i class="fas fa-user me-1"></i>Người tạo</div>
                            <div class="task-meta-value">{{ $task->creator->name ?? 'N/A' }}</div>
                        </div>
                        <div class="task-meta-item">
                            <div class="task-meta-label"><i class="fas fa-calendar me-1"></i>Ngày tạo</div>
                            <div class="task-meta-value">{{ $task->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                        <div class="task-meta-item">
                            <div class="task-meta-label"><i class="fas fa-hourglass-end me-1"></i>Thời hạn hiện tại</div>
                            <div class="task-meta-value">
                                {{ $task->deadline->format('d/m/Y H:i') }}
                                @if(now() > $task->deadline)
                                    <span class="badge bg-danger ms-2">Đã quá hạn</span>
                                @elseif(now()->diffInDays($task->deadline) <= 3)
                                    <span class="badge bg-warning ms-2">Sắp hết hạn</span>
                                @endif
                            </div>
                        </div>
                        <div class="task-meta-item">
                            <div class="task-meta-label"><i class="fas fa-info-circle me-1"></i>Trạng thái</div>
                            <div class="task-meta-value">
                                <span class="badge rounded-pill {{ $task->users->where('id', Auth::id())->first()->pivot->status === 'pending' ? 'bg-secondary' : ($task->users->where('id', Auth::id())->first()->pivot->status === 'in_progress' ? 'bg-primary' : 'bg-success') }}">
                                    {{ $task->users->where('id', Auth::id())->first()->pivot->status === 'pending' ? 'Chưa thực hiện' : ($task->users->where('id', Auth::id())->first()->pivot->status === 'in_progress' ? 'Đang thực hiện' : 'Đã hoàn thành') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">
                        <i class="fas fa-edit me-2"></i>Thông tin gia hạn
                    </div>
                    
                    <form action="{{ route('task-extensions.store', $task) }}" method="POST" class="extension-form">
                        @csrf
                        
                        <div class="form-group">
                            <label for="new_deadline" class="form-label-custom">
                                Thời hạn mới <span class="required-mark">*</span>
                            </label>
                            <input type="datetime-local" id="new_deadline" name="new_deadline" 
                                    class="custom-input @error('new_deadline') input-error @enderror" 
                                    required value="{{ old('new_deadline') }}" 
                                    min="{{ now()->format('Y-m-d\TH:i') }}">
                            <div class="error-message">
                                @error('new_deadline')
                                    {{ $message }}
                                @enderror
                            </div>
                            <div class="form-hint">Thời hạn mới phải lớn hơn thời gian hiện tại.</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="reason" class="form-label-custom">
                                Lý do xin gia hạn <span class="required-mark">*</span>
                            </label>
                            <textarea id="reason" name="reason" 
                                    class="custom-input @error('reason') input-error @enderror" 
                                    rows="4" required>{{ old('reason') }}</textarea>
                            <div class="error-message">
                                @error('reason')
                                    {{ $message }}
                                @enderror
                            </div>
                            <div class="form-hint">Vui lòng nêu rõ lý do cần gia hạn thêm thời gian để người phê duyệt có thể xem xét.</div>
                        </div>
                        
                        <div class="form-actions">
                            <a href="{{ route('tasks.show', $task) }}" class="back-button">
                                <i class="fas fa-arrow-left me-1"></i> Quay lại
                            </a>
                            <button type="submit" class="save-button">
                                <i class="fas fa-paper-plane me-1"></i> Gửi yêu cầu
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@push('scripts')
    <script>
        $(document).ready(function() {
            // Set minimum date for datetime-local input
            var now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            var minDateTime = now.toISOString().slice(0,16);
            document.getElementById('new_deadline').min = minDateTime;
            
            // Set default value to current deadline + 1 day if not already set
            if (!document.getElementById('new_deadline').value) {
                var defaultDate = new Date('{{ $task->deadline }}');
                defaultDate.setDate(defaultDate.getDate() + 1);
                defaultDate.setMinutes(defaultDate.getMinutes() - defaultDate.getTimezoneOffset());
                document.getElementById('new_deadline').value = defaultDate.toISOString().slice(0,16);
            }
        });
    </script>
@endpush