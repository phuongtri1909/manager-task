@extends('manager_task.layouts.partials.sidebar')

@section('title', 'Phân quyền người dùng')

@section('main-content')
<div class="category-container">
    <!-- Breadcrumb -->
    <div class="content-breadcrumb">
        <ol class="breadcrumb-list">
            <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Quản lý vai trò</a></li>
            <li class="breadcrumb-item current">Phân quyền người dùng</li>
        </ol>
    </div>

    <div class="content-card">
        <div class="card-top">
            <div class="card-title">
                <i class="fas fa-user-cog icon-title"></i>
                <h5>Phân quyền người dùng</h5>
            </div>
            <a href="{{ route('roles.index') }}" class="action-button bg-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách vai trò
            </a>
        </div>
        
        <div class="card-content">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Filter Form -->
            <div class="filter-section mb-4">
                <div class="filter-header d-flex justify-content-between align-items-center mb-2">
                    <h6 class="m-0"><i class="fas fa-filter me-1"></i> Lọc người dùng</h6>
                    <button class="btn btn-sm btn-link p-0" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </div>
                
                <div class="collapse show" id="filterCollapse">
                    <form action="{{ route('user-roles.index') }}" method="GET" class="filter-form">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label for="name" class="form-label">Tên nhân viên</label>
                                    <input type="text" class="form-control form-control-sm" id="name" name="name" value="{{ request('name') }}" placeholder="Nhập tên nhân viên...">
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="text" class="form-control form-control-sm" id="email" name="email" value="{{ request('email') }}" placeholder="Nhập email...">
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label for="role_id" class="form-label">Vai trò</label>
                                    <select class="form-select form-select-sm" id="role_id" name="role_id">
                                        <option value="">-- Tất cả vai trò --</option>
                                        @foreach($allRoles as $role)
                                            <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                                                {{ $role->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label for="department_id" class="form-label">Phòng ban</label>
                                    <select class="form-select form-select-sm" id="department_id" name="department_id">
                                        <option value="">-- Tất cả phòng ban --</option>
                                        @foreach($allDepartments as $department)
                                            <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                                {{ $department->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="filter-actions">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fas fa-search me-1"></i> Tìm kiếm
                            </button>
                            <a href="{{ route('user-roles.index') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-redo me-1"></i> Đặt lại
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            @if($users->isEmpty())
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h4>Không tìm thấy người dùng</h4>
                    <p>Không có người dùng nào phù hợp với tiêu chí tìm kiếm.</p>
                </div>
            @else
                <div class="data-table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th class="column-small">STT</th>
                                <th class="column-medium">Tên nhân viên</th>
                                <th class="column-medium">Email</th>
                                <th class="column-medium">Vai trò hiện tại</th>
                                <th class="column-medium">Phòng ban</th>
                                <th class="column-small">Được tạo task</th>
                                <th class="column-small text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $index => $user)
                                <tr>
                                    <td class="text-center">{{ ($users->currentPage() - 1) * $users->perPage() + $index + 1 }}</td>
                                    <td class="item-title">{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @if($user->role)
                                            <span class="badge bg-primary">{{ $user->role->name }}</span>
                                        @else
                                            <span class="badge bg-secondary">Chưa có vai trò</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->department)
                                            {{ $user->department->name }}
                                        @else
                                            <span class="text-muted">Không có</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($user->can_assign_task)
                                            <i class="fas fa-check-circle text-success"></i>
                                        @else
                                            <i class="fas fa-times-circle text-danger"></i>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!$user->isAdmin())
                                
                                            <div class="action-buttons-wrapper">
                                                <a href="{{ route('user-roles.edit', $user) }}" class="action-icon edit-icon" title="Phân quyền">
                                                    <i class="fas fa-user-cog"></i>
                                                </a>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                @if($users->hasPages())
                <div class="pagination-wrapper">
                    <div class="pagination-info">
                        Hiển thị {{ $users->firstItem() ?? 0 }} đến {{ $users->lastItem() ?? 0 }} của {{ $users->total() }} người dùng
                    </div>
                    <div class="pagination-controls">
                        {{ $users->appends(request()->query())->links('manager_task.components.paginate') }}
                    </div>
                </div>
                @endif
            @endif
        </div>
    </div>
</div>


@endsection 

@push('styles')
<style>
    .filter-section {
        background-color: #f8f9fa;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 1.5rem;
        border: 1px solid #e9ecef;
    }
    
    .filter-header {
        border-bottom: 1px solid #e9ecef;
        padding-bottom: 0.5rem;
        margin-bottom: 1rem;
    }
    
    .filter-form {
        padding-top: 0.5rem;
    }
    
    .filter-actions {
        display: flex;
        gap: 0.5rem;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Set focus on first filter input when page loads
        $('#name').focus();
        
        // Add reset functionality to reset button
        $('.btn-outline-secondary').click(function(e) {
            e.preventDefault();
            $('input[type="text"]').val('');
            $('select').val('');
            $(this).closest('form').submit();
        });
    });
</script>
@endpush