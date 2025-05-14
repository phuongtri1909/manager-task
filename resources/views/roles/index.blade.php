@extends('layouts.partials.sidebar')

@section('title', 'Quản lý vai trò')

@section('main-content')
<div class="category-container">
    <!-- Breadcrumb -->
    <div class="content-breadcrumb">
        <ol class="breadcrumb-list">
            <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}">Dashboard</a></li>
            <li class="breadcrumb-item current">Quản lý vai trò</li>
        </ol>
    </div>

    <div class="content-card">
        <div class="card-top">
            <div class="card-title">
                <i class="fas fa-user-tag icon-title"></i>
                <h5>Danh sách vai trò</h5>
            </div>
            <div class="action-buttons">
                <a href="{{ route('user-roles.index') }}" class="action-button bg-info">
                    <i class="fas fa-user-cog"></i> Phân quyền người dùng
                </a>
                {{-- <a href="{{ route('roles.create') }}" class="action-button">
                    <i class="fas fa-plus-circle"></i> Thêm vai trò
                </a> --}}
            </div>
        </div>
        
        <div class="card-content">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($roles->isEmpty())
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-user-tag"></i>
                    </div>
                    <h4>Chưa có vai trò nào</h4>
                    <p>Bắt đầu bằng cách thêm vai trò đầu tiên.</p>
                    {{-- <a href="{{ route('roles.create') }}" class="action-button">
                        <i class="fas fa-plus-circle"></i> Thêm vai trò
                    </a> --}}
                </div>
            @else
                <div class="data-table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th class="column-small">STT</th>
                                <th class="column-medium">Tên vai trò</th>
                                
                                <th class="column-small">Cấp độ</th>
                                <th class="column-small">Phạm vi</th>
                                <th class="column-small">Tạo task</th>
                                <th class="column-medium">Mô tả</th>
                                <th class="column-small text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($roles as $index => $role)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td class="item-title">{{ $role->name }}</td>
                                   
                                    <td class="text-center">{{ $role->level }}</td>
                                    <td>
                                        @if(isset($role->scope))
                                            @if($role->scope == \App\Models\Role::SCOPE_GLOBAL)
                                                <span class="badge bg-primary">Toàn hệ thống</span>
                                            @else
                                                <span class="badge bg-info">Phòng ban</span>
                                            @endif
                                        @else 
                                            <span class="badge bg-secondary">Chưa xác định</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($role->slug === 'admin')
                                            <i class="fas fa-check-circle text-success" title="Mặc định có quyền"></i>
                                        @elseif($role->slug === 'staff')
                                            <i class="fas fa-times-circle text-danger" title="Không được phép"></i>
                                        @else
                                            <i class="fas fa-question-circle text-warning" title="Cần được cấp quyền"></i>
                                        @endif
                                    </td>
                                    <td>{{ $role->description }}</td>
                                    <td>
                                        <div class="action-buttons-wrapper">
                                            <a href="{{ route('roles.edit', $role) }}" class="action-icon edit-icon" title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            {{-- <form action="{{ route('roles.destroy', $role) }}" method="POST" class="delete-action-container d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="action-icon bg-danger" style="border: none" onclick="confirmDelete(event, this.form)" title="Xóa">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form> --}}
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                @if(method_exists($roles, 'links'))
                <div class="pagination-wrapper">
                    <div class="pagination-info">
                        Hiển thị {{ $roles->firstItem() ?? 0 }} đến {{ $roles->lastItem() ?? 0 }} của {{ $roles->total() }} vai trò
                    </div>
                    <div class="pagination-controls">
                        {{ $roles->appends(request()->query())->links() }}
                    </div>
                </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.data-table').DataTable({
            "responsive": true,
            "autoWidth": false,
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.11.4/i18n/vi.json"
            }
        });
    });
    
    // Confirm delete function with enhanced alert
    function confirmDelete(event, form) {
        event.preventDefault();
        event.stopPropagation();
        
        // Use SweetAlert if available, otherwise fallback to confirm
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Bạn có chắc chắn?',
                text: "Bạn không thể hoàn tác hành động này!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Xóa',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        } else {
            if (confirm('Bạn có chắc chắn muốn xóa vai trò này?')) {
                form.submit();
            }
        }
    }
</script>
@endpush 