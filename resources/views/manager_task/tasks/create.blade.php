@extends('manager_task.layouts.partials.sidebar')

@section('title', 'Tạo công việc mới')

@section('main-content')
    <div class="category-form-container">
        <!-- Breadcrumb -->
        <div class="content-breadcrumb">
            <ol class="breadcrumb-list">
                <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}">Công việc</a></li>
                <li class="breadcrumb-item current">Tạo mới</li>
            </ol>
        </div>

        <div class="form-card">
            <div class="form-header">
                <div class="form-title">
                    <i class="fas fa-plus-circle icon-title"></i>
                    <h5>Tạo công việc mới</h5>
                </div>
            </div>

            <div class="form-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('tasks.store') }}" method="POST" class="task-form" id="task-form">
                    @csrf
                    
                    <div class="form-tabs">
                        <div class="form-group">
                            <label for="title" class="form-label-custom">
                                Tiêu đề <span class="required-mark">*</span>
                            </label>
                            <input type="text" class="custom-input {{ $errors->has('title') ? 'input-error' : '' }}"
                                id="title" name="title" value="{{ old('title') }}" required>
                            <div class="error-message" id="error-title">
                                @error('title')
                                    {{ $message }}
                                @enderror
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description" class="form-label-custom">
                                Mô tả
                            </label>
                            <textarea class="custom-input {{ $errors->has('description') ? 'input-error' : '' }}" 
                                id="description" name="description" rows="4">{{ old('description') }}</textarea>
                            <div class="error-message" id="error-description">
                                @error('description')
                                    {{ $message }}
                                @enderror
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="deadline" class="form-label-custom">
                                Thời hạn <span class="required-mark">*</span>
                            </label>
                            <input type="datetime-local" class="custom-input {{ $errors->has('deadline') ? 'input-error' : '' }}" 
                                id="deadline" name="deadline" value="{{ old('deadline') }}" required>
                            <div class="error-message" id="error-deadline">
                                @error('deadline')
                                    {{ $message }}
                                @enderror
                            </div>
                        </div>

                        <div class="form-check-group">
                            <div class="custom-checkbox">
                                <input type="checkbox" id="for_departments" name="for_departments" value="1" 
                                    {{ old('for_departments') ? 'checked' : '' }} onchange="toggleTaskType()">
                                <label for="for_departments" class="form-label-custom mb-0">
                                    Giao cho phòng ban
                                </label>
                            </div>
                            <div class="form-hint">
                                <i class="fas fa-info-circle"></i>
                                <span>Nếu bật, công việc sẽ được giao cho (các) phòng ban.</span>
                            </div>
                        </div>
                        
                        <div id="department_section" class="{{ old('for_departments') ? '' : 'd-none' }}">
                            <div class="form-group">
                                <label for="departments" class="form-label-custom">
                                    Chọn phòng ban <span class="required-mark">*</span>
                                </label>
                                <select class="custom-input select2 {{ $errors->has('departments') ? 'input-error' : '' }}" 
                                    id="departments" name="departments[]" multiple="multiple">
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" {{ (old('departments') && in_array($department->id, old('departments'))) ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="error-message" id="error-departments">
                                    @error('departments')
                                        {{ $message }}
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="form-check-group">
                                <div class="custom-checkbox">
                                    <input type="checkbox" id="include_department_heads" name="include_department_heads" value="1" 
                                        {{ old('include_department_heads', true) ? 'checked' : '' }}>
                                    <label for="include_department_heads" class="form-label-custom mb-0">
                                        Bao gồm trưởng/phó phòng
                                    </label>
                                </div>
                                <div class="form-hint">
                                    <i class="fas fa-info-circle"></i>
                                    <span>Nếu bật, trưởng phòng và phó phòng của các phòng ban được chọn sẽ tự động được gán công việc.</span>
                                </div>
                            </div>
                        </div>
                        
                        <div id="user_section" class="{{ old('for_departments') ? 'd-none' : '' }}">
                            <div class="form-group">
                                <label for="users" class="form-label-custom">
                                    Chọn người thực hiện <span class="required-mark">*</span>
                                </label>
                                <select class="custom-input select2 {{ $errors->has('users') ? 'input-error' : '' }}" 
                                    id="users" name="users[]" multiple="multiple">
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ (old('users') && in_array($user->id, old('users'))) ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->department->name ?? 'N/A' }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="error-message" id="error-users">
                                    @error('users')
                                        {{ $message }}
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('tasks.index') }}" class="back-button">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                        <button type="submit" class="save-button">
                            <i class="fas fa-save"></i> Tạo công việc
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
    <style>
        .select2-container {
            width: 100% !important;
        }
        .select2-container .select2-selection--multiple {
            min-height: 38px;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            margin: 4px 4px 0 0;
            padding: 4px 8px;
            background-color: var(--primary-color);
            border: none;
            color: white;
            border-radius: 4px;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: #fff;
            margin-right: 5px;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: #f8f9fa;
        }
        .select2-dropdown {
            border-color: var(--border-color);
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: var(--primary-color);
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap-5',
                placeholder: 'Chọn...',
                allowClear: true,
                width: '100%'
            });
            
            // Set minimum date for datetime-local input
            var now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            var minDateTime = now.toISOString().slice(0,16);
            document.getElementById('deadline').min = minDateTime;
            
            // Set default deadline to tomorrow
            if (!document.getElementById('deadline').value) {
                var tomorrow = new Date();
                tomorrow.setDate(tomorrow.getDate() + 1);
                tomorrow.setMinutes(tomorrow.getMinutes() - tomorrow.getTimezoneOffset());
                document.getElementById('deadline').value = tomorrow.toISOString().slice(0,16);
            }
        });
        
        function toggleTaskType() {
            var forDepartments = document.getElementById('for_departments').checked;
            
            if (forDepartments) {
                document.getElementById('department_section').classList.remove('d-none');
                document.getElementById('user_section').classList.add('d-none');
            } else {
                document.getElementById('department_section').classList.add('d-none');
                document.getElementById('user_section').classList.remove('d-none');
            }
        }
    </script>
@endpush 