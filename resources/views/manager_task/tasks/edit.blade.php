@extends('manager_task.layouts.partials.sidebar')

@section('title', 'Chỉnh sửa công việc')

@section('main-content')
    <div class="category-form-container">
        <!-- Breadcrumb -->
        <div class="content-breadcrumb">
            <ol class="breadcrumb-list">
                <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}">Công việc</a></li>
                <li class="breadcrumb-item current">Chỉnh sửa</li>
            </ol>
        </div>

        <div class="form-card">
            <div class="form-header">
                <div class="form-title">
                    <i class="fas fa-edit icon-title"></i>
                    <h5>Chỉnh sửa công việc: {{ $task->title }}</h5>
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

                <form action="{{ route('tasks.update', $task) }}" method="POST" class="task-form" id="task-form"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="form-tabs">
                        <div class="form-group">
                            <label for="title" class="form-label-custom">
                                Tiêu đề <span class="required-mark">*</span>
                            </label>
                            <input type="text" class="custom-input {{ $errors->has('title') ? 'input-error' : '' }}"
                                id="title" name="title" value="{{ old('title', $task->title) }}" required>
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
                            <textarea class="custom-input {{ $errors->has('description') ? 'input-error' : '' }}" id="description"
                                name="description" rows="4">{{ old('description', $task->description) }}</textarea>
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
                            <input type="datetime-local"
                                class="custom-input {{ $errors->has('deadline') ? 'input-error' : '' }}" id="deadline"
                                name="deadline" value="{{ old('deadline', $task->deadline->format('Y-m-d\TH:i')) }}"
                                required>
                            <div class="error-message" id="error-deadline">
                                @error('deadline')
                                    {{ $message }}
                                @enderror
                            </div>
                        </div>

                        <!-- File Attachments -->
                        <div class="form-group">
                            <label for="attachments" class="form-label-custom">
                                Tệp đính kèm mới
                            </label>
                            <div class="custom-file-upload">
                                <input type="file" class="file-input" id="attachments" name="attachments[]" multiple>
                                <div class="file-upload-button">
                                    <i class="fas fa-cloud-upload-alt"></i> Chọn tệp
                                </div>
                                <div class="file-upload-info">Hỗ trợ: .doc, .docx, .xlsx, .pdf, .mp4</div>
                            </div>
                            <div id="selected-files" class="mt-2"></div>
                            <div class="error-message">
                                @error('attachments')
                                    {{ $message }}
                                @enderror
                                @error('attachments.*')
                                    {{ $message }}
                                @enderror
                            </div>
                        </div>

                        <!-- Existing Attachments -->
                        @if (isset($task->attachments) && $task->attachments->count() > 0)
                            <div class="form-group">
                                <label class="form-label-custom">
                                    Tệp đính kèm hiện tại
                                </label>
                                <div class="existing-attachments">
                                    @foreach ($task->attachments as $attachment)
                                        <div class="selected-file">
                                            <div class="file-info">
                                                @php
                                                    $icon = 'fa-file';
                                                    if (in_array($attachment->file_type, ['pdf'])) {
                                                        $icon = 'fa-file-pdf';
                                                    } elseif (in_array($attachment->file_type, ['doc', 'docx'])) {
                                                        $icon = 'fa-file-word';
                                                    } elseif (
                                                        in_array($attachment->file_type, ['xls', 'xlsx', 'csv'])
                                                    ) {
                                                        $icon = 'fa-file-excel';
                                                    } elseif (in_array($attachment->file_type, ['mp4', 'avi', 'mov'])) {
                                                        $icon = 'fa-file-video';
                                                    }
                                                    $fileSize = round($attachment->file_size / 1024, 2) . ' KB';
                                                @endphp
                                                <i class="fas {{ $icon }} file-icon"></i>
                                                <span class="file-name">{{ $attachment->original_filename }}</span>
                                                <span class="file-size">({{ $fileSize }})</span>
                                            </div>
                                            <div class="attachment-actions">
                                                <a href="{{ route('tasks.attachments.download', $attachment->id) }}"
                                                    class="btn btn-sm btn-info" title="Tải xuống">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <div class="custom-checkbox d-inline-block ml-2">
                                                    <input type="checkbox" id="remove_attachment_{{ $attachment->id }}"
                                                        name="remove_attachments[]" value="{{ $attachment->id }}">
                                                    <label for="remove_attachment_{{ $attachment->id }}"
                                                        class="form-label-custom mb-0">Xóa</label>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="form-check-group">
                            <div class="custom-checkbox">
                                <input type="checkbox" id="for_departments" name="for_departments" value="1"
                                    {{ old('for_departments', $task->for_departments) ? 'checked' : '' }}
                                    onchange="toggleTaskType()">
                                <label for="for_departments" class="form-label-custom mb-0">
                                    Giao cho phòng ban
                                </label>
                            </div>
                            <div class="form-hint">
                                <i class="fas fa-info-circle"></i>
                                <span>Nếu bật, công việc sẽ được giao cho (các) phòng ban.</span>
                            </div>
                        </div>

                        <div id="department_section"
                            class="{{ old('for_departments', $task->for_departments) ? '' : 'd-none' }}">
                            <div class="form-group">
                                <label for="departments" class="form-label-custom">
                                    Chọn phòng ban <span class="required-mark">*</span>
                                </label>
                                <select
                                    class="custom-input select2 {{ $errors->has('departments') ? 'input-error' : '' }}"
                                    id="departments" name="departments[]" multiple="multiple">
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}"
                                            {{ old('departments', $selectedDepartments) && in_array($department->id, old('departments', $selectedDepartments)) ? 'selected' : '' }}>
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
                                    <input type="checkbox" id="include_department_heads" name="include_department_heads"
                                        value="1"
                                        {{ old('include_department_heads', $task->include_department_heads) ? 'checked' : '' }}>
                                    <label for="include_department_heads" class="form-label-custom mb-0">
                                        Bao gồm trưởng/phó phòng
                                    </label>
                                </div>
                                <div class="form-hint">
                                    <i class="fas fa-info-circle"></i>
                                    <span>Nếu bật, trưởng phòng và phó phòng của các phòng ban được chọn sẽ tự động được gán
                                        công việc.</span>
                                </div>
                            </div>
                        </div>

                        <div id="user_section"
                            class="{{ old('for_departments', $task->for_departments) ? 'd-none' : '' }}">
                            <div class="form-group">
                                <label for="users" class="form-label-custom">
                                    Chọn người thực hiện <span class="required-mark">*</span>
                                </label>

                                <!-- Filtering options -->
                                <div class="filter-options mb-2">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <select id="role_filter" class="custom-input">
                                                <option value="">Tất cả vai trò</option>
                                                <option value="deputy-director">Phó giám đốc</option>
                                                <option value="department-head">Trưởng phòng</option>
                                                <option value="deputy-department-head">Phó phòng</option>
                                                <option value="staff">Nhân viên</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <select id="department_filter" class="custom-input">
                                                <option value="">Tất cả phòng ban</option>
                                                @foreach ($departments as $dept)
                                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" id="name_filter" class="custom-input"
                                                placeholder="Tìm theo tên...">
                                        </div>
                                    </div>
                                </div>

                                <select class="custom-input select2 {{ $errors->has('users') ? 'input-error' : '' }}"
                                    id="users" name="users[]" multiple="multiple">
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}"
                                            {{ old('users', $selectedUsers) && in_array($user->id, old('users', $selectedUsers)) ? 'selected' : '' }}
                                            data-role="{{ $user->role->slug ?? '' }}"
                                            data-department="{{ $user->department_id ?? '' }}">
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
                        <a href="{{ route('tasks.show', $task) }}" class="back-button">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                        <button type="submit" class="save-button">
                            <i class="fas fa-save"></i> Cập nhật
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
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

        /* File Upload Styles */
        .custom-file-upload {
            position: relative;
            border: 2px dashed var(--border-color);
            border-radius: 6px;
            padding: 20px;
            text-align: center;
            background-color: #f9f9f9;
            transition: all 0.3s;
        }

        .custom-file-upload:hover {
            border-color: var(--primary-color);
            background-color: #f0f7ff;
        }

        .file-input {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .file-upload-button {
            background-color: var(--primary-color);
            color: white;
            padding: 8px 16px;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 10px;
            font-weight: 500;
        }

        .file-upload-info {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .selected-file {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: #fff;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            padding: 8px 12px;
            margin-top: 8px;
        }

        .file-info {
            display: flex;
            align-items: center;
        }

        .file-icon {
            margin-right: 10px;
            color: var(--primary-color);
        }

        .file-name {
            font-weight: 500;
            color: #333;
        }

        .file-size {
            color: #6c757d;
            font-size: 0.85rem;
            margin-left: 8px;
        }

        .attachment-actions {
            display: flex;
            align-items: center;
        }

        .attachment-actions .btn {
            margin-right: 8px;
        }

        .attachment-actions .custom-checkbox {
            display: flex;
            align-items: center;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2
            initializeSelect2();

            // User filtering functionality
            $('#role_filter, #department_filter, #name_filter').on('change keyup', function() {
                applyFilters();
            });

            function initializeSelect2() {
                $('.select2').select2({
                    theme: 'bootstrap-5',
                    placeholder: 'Chọn...',
                    allowClear: true,
                    width: '100%',
                    templateResult: formatUser
                });
            }

            function formatUser(user) {
                if (!user.id) {
                    return user.text;
                }

                var $option = $(user.element);
                if ($option.hasClass('filtered-out')) {
                    return null; // Don't show filtered out options
                }

                return $('<span>' + user.text + '</span>');
            }

            function applyFilters() {
                var roleFilter = $('#role_filter').val();
                var departmentFilter = $('#department_filter').val();
                var nameFilter = $('#name_filter').val().toLowerCase();

                // Save current selections
                var currentlySelected = $('#users').val() || [];

                // Remove previous filtering classes
                $('#users option').removeClass('filtered-out');

                // Mark options that should be filtered out
                $('#users option').each(function() {
                    var $option = $(this);
                    var userRole = $option.data('role');
                    var userDepartment = $option.data('department');
                    var userName = $option.text().toLowerCase();

                    var matchesRole = roleFilter === '' || userRole === roleFilter;
                    var matchesDepartment = departmentFilter === '' || userDepartment == departmentFilter;
                    var matchesName = nameFilter === '' || userName.indexOf(nameFilter) > -1;

                    // If it doesn't match filters and isn't selected, hide it
                    if (!(matchesRole && matchesDepartment && matchesName) &&
                        !currentlySelected.includes($option.val())) {
                        $option.addClass('filtered-out');
                    }
                });

                // Refresh Select2 to apply changes
                $('#users').select2('destroy');
                initializeSelect2();

                // Restore selection
                $('#users').val(currentlySelected).trigger('change');
            }

            // Initial filter application
            applyFilters();

            // File upload handling
            document.getElementById('attachments').addEventListener('change', function(e) {
                const fileInput = e.target;
                const selectedFilesDiv = document.getElementById('selected-files');
                selectedFilesDiv.innerHTML = '';

                // File type validation
                const allowedTypes = [
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'video/mp4'
                ];

                // Display selected files
                if (fileInput.files.length > 0) {
                    for (let i = 0; i < fileInput.files.length; i++) {
                        const file = fileInput.files[i];

                        // Check file type
                        if (!allowedTypes.includes(file.type)) {
                            alert(
                                `Loại tệp không được hỗ trợ: ${file.name}. Vui lòng chọn tệp .doc, .docx, .xlsx, .pdf hoặc .mp4`);
                            continue;
                        }

                        // File icon based on type
                        let fileIcon = 'fa-file';
                        if (file.type === 'application/pdf') {
                            fileIcon = 'fa-file-pdf';
                        } else if (file.type.includes('word')) {
                            fileIcon = 'fa-file-word';
                        } else if (file.type.includes('excel') || file.type.includes('spreadsheet')) {
                            fileIcon = 'fa-file-excel';
                        } else if (file.type.includes('video')) {
                            fileIcon = 'fa-file-video';
                        }

                        // Format file size
                        const fileSize = (file.size / 1024).toFixed(2) + ' KB';

                        // Create file element
                        const fileElement = document.createElement('div');
                        fileElement.className = 'selected-file';
                        fileElement.innerHTML = `
                            <div class="file-info">
                                <i class="fas ${fileIcon} file-icon"></i>
                                <span class="file-name">${file.name}</span>
                                <span class="file-size">(${fileSize})</span>
                            </div>
                        `;

                        selectedFilesDiv.appendChild(fileElement);
                    }
                }
            });
        });

        function toggleTaskType() {
            var forDepartmentsElem = document.getElementById('for_departments');

            // Only proceed if the element exists (not for Deputy Department Heads)
            if (forDepartmentsElem) {
                var forDepartments = forDepartmentsElem.checked;

                if (forDepartments) {
                    document.getElementById('department_section').classList.remove('d-none');
                    document.getElementById('user_section').classList.add('d-none');
                } else {
                    document.getElementById('department_section').classList.add('d-none');
                    document.getElementById('user_section').classList.remove('d-none');
                }
            } else {
                // For Deputy Department Heads, always show user section
                var userSection = document.getElementById('user_section');
                if (userSection) {
                    userSection.classList.remove('d-none');
                }
            }
        }
    </script>
@endpush
