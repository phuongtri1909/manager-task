@extends('layouts.partials.sidebar')

@section('title', 'Phân quyền người dùng')

@section('main-content')
    <div class="category-form-container">
        <!-- Breadcrumb -->
        <div class="content-breadcrumb">
            <ol class="breadcrumb-list">
                <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Quản lý vai trò</a></li>
                <li class="breadcrumb-item"><a href="{{ route('user-roles.index') }}">Phân quyền người dùng</a></li>
                <li class="breadcrumb-item current">{{ $user->name }}</li>
            </ol>
        </div>

        <div class="form-card">
            <div class="form-header">
                <div class="form-title">
                    <i class="fas fa-user-cog icon-title"></i>
                    <h5>Phân quyền cho: {{ $user->name }}</h5>
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

                <!-- Role selection guidance alert -->
                <div class="alert alert-info mb-4">
                    <div class="d-flex">
                        <div class="me-3">
                            <i class="fas fa-info-circle fa-2x"></i>
                        </div>
                        <div>
                            <h5 class="alert-heading mb-1">Hướng dẫn phân quyền</h5>
                            <p class="mb-0">Mỗi vai trò có các quyền hạn khác nhau trong hệ thống:</p>
                            <ul class="mb-0 mt-2">
                                <li><strong>Admin:</strong> Có toàn quyền trong hệ thống</li>
                                <li><strong>Giám đốc/Phó giám đốc:</strong> Có quyền toàn hệ thống, có thể được cấp quyền tạo task</li>
                                <li><strong>Trưởng phòng/Phó phòng:</strong> Cần chọn phòng ban và có thể được cấp quyền tạo task</li>
                                <li><strong>Nhân viên:</strong> Cần chọn phòng ban và không có quyền tạo task</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <form action="{{ route('user-roles.update', $user) }}" method="POST" id="roleForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label-custom">
                                    Thông tin người dùng
                                </label>
                                <div class="user-info-card p-3 border rounded mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="avatar me-3">
                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=random" class="rounded-circle" width="50" height="50" alt="{{ $user->name }}">
                                        </div>
                                        <div>
                                            <h5 class="mb-0">{{ $user->name }}</h5>
                                            <p class="text-muted mb-0">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                    <div class="user-details mt-2">
                                        <div class="detail-item">
                                            <span class="text-muted">Phòng ban hiện tại:</span>
                                            <span class="fw-bold">{{ $user->department ? $user->department->name : 'Không có' }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="text-muted">Vai trò hiện tại:</span>
                                            <span class="fw-bold">{{ $user->role ? $user->role->name : 'Chưa có vai trò' }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="text-muted">Quyền tạo task:</span>
                                            <span class="fw-bold">
                                                @if($user->can_assign_job)
                                                    <span class="text-success">Có</span>
                                                @else
                                                    <span class="text-danger">Không</span>
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="role_id" class="form-label-custom">
                                    Vai trò <span class="required-mark">*</span>
                                </label>
                                <select class="custom-input form-select @error('role_id') input-error @enderror" id="role_id" name="role_id" required>
                                    <option value="">-- Chọn vai trò --</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" 
                                            data-scope="{{ $role->scope }}"
                                            data-slug="{{ $role->slug }}"
                                            {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                            {{ $role->name }}
                                            @if($role->scope)
                                                ({{ $role->scope == \App\Models\Role::SCOPE_GLOBAL ? 'Toàn hệ thống' : 'Phòng ban' }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <div class="error-message">
                                    @error('role_id')
                                        {{ $message }}
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Role descriptions -->
                            <div class="role-description-panel card border-0 shadow-sm p-3 mb-4">
                                <h6 class="mb-2"><i class="fas fa-info-circle me-2"></i>Chi tiết vai trò</h6>
                                
                                <div id="roleDirectorDescription" class="role-info d-none">
                                    <div class="role-info-header bg-primary text-white py-2 px-3 rounded">
                                        <i class="fas fa-user-tie me-2"></i>Giám đốc / Phó Giám đốc
                                    </div>
                                    <div class="role-info-body p-3 bg-light rounded-bottom">
                                        <ul class="role-features mb-0">
                                            <li><i class="fas fa-check-circle text-primary me-2"></i>Có quyền trên toàn hệ thống</li>
                                            <li><i class="fas fa-check-circle text-primary me-2"></i>Không thuộc phòng ban cụ thể</li>
                                            <li><i class="fas fa-check-circle text-primary me-2"></i>Có thể được cấp quyền tạo và giao task</li>
                                        </ul>
                                    </div>
                                </div>
                                
                                <div id="roleDepartmentHeadDescription" class="role-info d-none">
                                    <div class="role-info-header bg-success text-white py-2 px-3 rounded">
                                        <i class="fas fa-user-tie me-2"></i>Trưởng phòng / Phó Trưởng phòng
                                    </div>
                                    <div class="role-info-body p-3 bg-light rounded-bottom">
                                        <ul class="role-features mb-0">
                                            <li><i class="fas fa-check-circle text-success me-2"></i>Có quyền trong phòng ban quản lý</li>
                                            <li><i class="fas fa-check-circle text-success me-2"></i>Bắt buộc chọn phòng ban</li>
                                            <li><i class="fas fa-check-circle text-success me-2"></i>Có thể được cấp quyền tạo và giao task</li>
                                        </ul>
                                    </div>
                                </div>
                                
                                <div id="roleStaffDescription" class="role-info d-none">
                                    <div class="role-info-header bg-info text-white py-2 px-3 rounded">
                                        <i class="fas fa-user me-2"></i>Nhân viên
                                    </div>
                                    <div class="role-info-body p-3 bg-light rounded-bottom">
                                        <ul class="role-features mb-0">
                                            <li><i class="fas fa-check-circle text-info me-2"></i>Thực hiện nhiệm vụ trong phòng ban</li>
                                            <li><i class="fas fa-check-circle text-info me-2"></i>Bắt buộc chọn phòng ban</li>
                                            <li><i class="fas fa-times-circle text-danger me-2"></i>Không có quyền tạo và giao task</li>
                                        </ul>
                                    </div>
                                </div>
                                
                                <div id="roleAdminDescription" class="role-info d-none">
                                    <div class="role-info-header bg-danger text-white py-2 px-3 rounded">
                                        <i class="fas fa-user-shield me-2"></i>Admin
                                    </div>
                                    <div class="role-info-body p-3 bg-light rounded-bottom">
                                        <ul class="role-features mb-0">
                                            <li><i class="fas fa-check-circle text-danger me-2"></i>Có toàn quyền trong hệ thống</li>
                                            <li><i class="fas fa-check-circle text-danger me-2"></i>Không thuộc phòng ban nào</li>
                                            <li><i class="fas fa-check-circle text-danger me-2"></i>Luôn có quyền tạo và giao task</li>
                                        </ul>
                                    </div>
                                </div>
                                
                                <div id="roleDefaultDescription" class="role-info">
                                    <div class="role-info-body p-3 bg-light rounded text-center">
                                        <i class="fas fa-hand-point-up fa-2x mb-2 text-muted"></i>
                                        <p class="text-muted mb-0">Vui lòng chọn vai trò để xem chi tiết</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="department-section option-card mb-4">
                                <div class="option-card-header">
                                    <div class="option-card-icon">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <div class="option-card-title">
                                        <h6>Phòng ban <span class="department-required d-none text-danger">*</span></h6>
                                        <p class="department-hint text-muted mb-0">Phòng ban chỉ bắt buộc đối với vai trò phòng ban cụ thể.</p>
                                    </div>
                                </div>
                                <div class="option-card-body">
                                    <select class="custom-input form-select @error('department_id') input-error @enderror" id="department_id" name="department_id">
                                        <option value="">-- Chọn phòng ban --</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}" {{ old('department_id', $user->department_id) == $department->id ? 'selected' : '' }}>
                                                {{ $department->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="error-message">
                                        @error('department_id')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="task-permission-section option-card mb-4">
                                <div class="option-card-header">
                                    <div class="option-card-icon">
                                        <i class="fas fa-tasks"></i>
                                    </div>
                                    <div class="option-card-title">
                                        <h6>Quyền tạo task</h6>
                                        <p class="task-permission-hint text-muted mb-0">
                                            <span class="task-permission-default">Cấp quyền tạo và giao task cho người dùng.</span>
                                            <span class="staff-warning text-danger d-none">Nhân viên không được phép tạo task.</span>
                                            <span class="admin-info text-success d-none">Admin luôn có quyền tạo task.</span>
                                        </p>
                                    </div>
                                </div>
                                <div class="option-card-body">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" class="form-check-input" id="can_assign_job" name="can_assign_job" value="1" 
                                            {{ old('can_assign_job', $user->can_assign_job) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="can_assign_job">Cho phép tạo và giao nhiệm vụ</label>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Current settings display -->
                            <div class="current-settings-panel card border shadow-sm mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-cog me-2"></i>Cài đặt hiện tại</h6>
                                </div>
                                <div class="card-body">
                                    <div class="current-settings">
                                        <div class="setting-item d-flex justify-content-between mb-2">
                                            <span class="setting-label">Vai trò:</span>
                                            <span class="setting-value fw-bold" id="currentRoleDisplay">{{ $user->role ? $user->role->name : 'Chưa có vai trò' }}</span>
                                        </div>
                                        <div class="setting-item d-flex justify-content-between mb-2">
                                            <span class="setting-label">Phòng ban:</span>
                                            <span class="setting-value fw-bold" id="currentDeptDisplay">{{ $user->department ? $user->department->name : 'Không có' }}</span>
                                        </div>
                                        <div class="setting-item d-flex justify-content-between">
                                            <span class="setting-label">Quyền tạo task:</span>
                                            <span class="setting-value fw-bold" id="currentPermDisplay">
                                                @if($user->can_assign_job)
                                                    <span class="text-success">Có</span>
                                                @else
                                                    <span class="text-danger">Không</span>
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <a href="{{ route('user-roles.index') }}" class="back-button">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                        <button type="submit" class="save-button">
                            <i class="fas fa-save"></i> Cập nhật quyền
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@push('styles')
<style>
    /* Role description panel */
    .role-description-panel {
        background-color: #f8f9fa;
    }
    
    .role-info {
        border-radius: 0.25rem;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .role-features {
        list-style: none;
        padding-left: 0;
    }
    
    .role-features li {
        padding: 0.25rem 0;
    }
    
    /* Option cards */
    .option-card {
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .option-card-header {
        display: flex;
        align-items: center;
        padding: 1rem;
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }
    
    .option-card-icon {
        width: 40px;
        height: 40px;
        background-color: #e9ecef;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
    }
    
    .option-card-icon i {
        color: #495057;
        font-size: 1.25rem;
    }
    
    .option-card-title h6 {
        margin-bottom: 0.25rem;
    }
    
    .option-card-body {
        padding: 1rem;
    }
    
    /* Highlighted sections based on role */
    .option-card.highlight {
        border-color: #4dabf7;
        box-shadow: 0 0 0 0.25rem rgba(77, 171, 247, 0.25);
    }
    
    .option-card.disabled {
        opacity: 0.6;
        background-color: #f8f9fa;
    }
    
    /* Form check switch styling */
    .form-check-input {
        width: 3em !important;
        height: 1.5em !important;
        margin-top: 0.25em;
    }
    
    /* Current settings panel */
    .current-settings-panel {
        background-color: #fff;
    }
    
    .setting-label {
        color: #6c757d;
    }
    
    /* Overall spacing and styling */
    .form-body {
        padding-bottom: 1rem;
    }
</style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            console.log('ready');
            
            // Handle role change to update form fields based on role
            function updateFormBasedOnRole() {
                const selectedOption = $('#role_id option:selected');
                const scope = selectedOption.data('scope');
                const slug = selectedOption.data('slug');
                const selectedText = selectedOption.text();
                
                // Update current role display
                if (selectedOption.val()) {
                    $('#currentRoleDisplay').text(selectedText);
                } else {
                    $('#currentRoleDisplay').text('Chưa chọn');
                }
                
                // Hide all role descriptions first
                $('.role-info').addClass('d-none');
                $('#roleDefaultDescription').addClass('d-none');
                
                // Reset highlights
                $('.option-card').removeClass('highlight disabled');
                
                // Reset all settings
                $('.department-section').show();
                $('#department_id').prop('required', false);
                $('.department-required').addClass('d-none');
                $('.task-permission-section').show();
                $('#can_assign_job').prop('disabled', false);
                $('.staff-warning, .admin-info').addClass('d-none');
                $('.task-permission-default').removeClass('d-none');
                
                if (!selectedOption.val()) {
                    // No role selected
                    $('#roleDefaultDescription').removeClass('d-none');
                    return;
                }
                
                // Handle specific role types
                if (slug === 'admin') {
                    // Admin role
                    $('#roleAdminDescription').removeClass('d-none');
                    $('.department-section').addClass('disabled');
                    $('.task-permission-section').addClass('disabled');
                    $('#department_id').prop('required', false).val('');
                    $('.task-permission-default').addClass('d-none');
                    $('.admin-info').removeClass('d-none');
                    $('#can_assign_job').prop('checked', true).prop('disabled', true);
                    
                    // Update current display
                    $('#currentDeptDisplay').text('Không có');
                    $('#currentPermDisplay').html('<span class="text-success">Có</span>');
                } 
                else if (['director', 'deputy-director'].includes(slug)) {
                    // Director/Deputy Director roles
                    $('#roleDirectorDescription').removeClass('d-none');
                    $('.department-section').addClass('disabled');
                    $('.task-permission-section').addClass('highlight');
                    $('#department_id').prop('required', false).val('');
                    
                    // Update current display
                    $('#currentDeptDisplay').text('Không có');
                    updateTaskPermissionDisplay();
                } 
                else if (['department-head', 'deputy-department-head'].includes(slug)) {
                    // Department Head/Deputy Department Head roles
                    $('#roleDepartmentHeadDescription').removeClass('d-none');
                    $('.department-section').addClass('highlight');
                    $('.task-permission-section').addClass('highlight');
                    $('#department_id').prop('required', true);
                    $('.department-required').removeClass('d-none');
                    
                    // Update current display
                    updateDepartmentDisplay();
                    updateTaskPermissionDisplay();
                } 
                else if (slug === 'staff') {
                    // Staff role
                    $('#roleStaffDescription').removeClass('d-none');
                    $('.department-section').addClass('highlight');
                    $('.task-permission-section').addClass('disabled');
                    $('#department_id').prop('required', true);
                    $('.department-required').removeClass('d-none');
                    $('.task-permission-default').addClass('d-none');
                    $('.staff-warning').removeClass('d-none');
                    $('#can_assign_job').prop('checked', false).prop('disabled', true);
                    
                    // Update current display
                    updateDepartmentDisplay();
                    $('#currentPermDisplay').html('<span class="text-danger">Không</span>');
                }
                else if (scope === '{{ \App\Models\Role::SCOPE_DEPARTMENT }}') {
                    // Other department-specific roles
                    $('.department-section').addClass('highlight');
                    $('#department_id').prop('required', true);
                    $('.department-required').removeClass('d-none');
                    
                    // Update current display
                    updateDepartmentDisplay();
                    updateTaskPermissionDisplay();
                }
                else if (scope === '{{ \App\Models\Role::SCOPE_GLOBAL }}') {
                    // Other global roles
                    $('.department-section').addClass('disabled');
                    $('#department_id').prop('required', false).val('');
                    
                    // Update current display
                    $('#currentDeptDisplay').text('Không có');
                    updateTaskPermissionDisplay();
                }
            }
            
            // Helper to update department display
            function updateDepartmentDisplay() {
                const deptSelect = $('#department_id');
                if (deptSelect.val()) {
                    const selectedText = deptSelect.find('option:selected').text();
                    $('#currentDeptDisplay').text(selectedText);
                } else {
                    $('#currentDeptDisplay').text('Chưa chọn');
                }
            }
            
            // Helper to update task permission display
            function updateTaskPermissionDisplay() {
                if ($('#can_assign_job').is(':checked')) {
                    $('#currentPermDisplay').html('<span class="text-success">Có</span>');
                } else {
                    $('#currentPermDisplay').html('<span class="text-danger">Không</span>');
                }
            }
            
            // Run on page load
            updateFormBasedOnRole();
            
            // Run when role changes
            $('#role_id').change(updateFormBasedOnRole);
            
            // Run when department changes
            $('#department_id').change(updateDepartmentDisplay);
            
            // Run when task permission changes
            $('#can_assign_job').change(updateTaskPermissionDisplay);
            
            // Make sure staff cannot get task creation permissions
            $('#roleForm').on('submit', function() {
                const selectedOption = $('#role_id option:selected');
                const slug = selectedOption.data('slug');
                
                if (slug === 'staff') {
                    $('#can_assign_job').prop('checked', false);
                }
                if (slug === 'admin') {
                    $('#can_assign_job').prop('checked', true);
                }
            });
        });
    </script>
@endpush