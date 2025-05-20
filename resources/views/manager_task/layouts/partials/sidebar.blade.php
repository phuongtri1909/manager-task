@extends('manager_task.layouts.app')

@section('content')
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div id="sidebar" class="sidebar">
            <div class="sidebar-header">
                <img src="{{ asset('vendor/adminlte/dist/img/AdminLTELogo.png') }}" alt="logo" height="70">
                <span>Quản lý công việc</span>
                <button id="close-sidebar" class="close-sidebar d-md-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            {{-- <div class="sidebar-user px-4">
                <div class="user-info">
                    <img src="{{ asset('assets/images/user.png') }}" alt="User Avatar" class="user-avatar">
                    <div class="user-name">
                        {{ Auth::user()->name }}
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <div class="user-role">
                        @if (Auth::user()->isAdmin())
                            <span class="badge bg-danger">Quản trị viên</span>
                        @elseif (Auth::user()->isDirector())
                            <span class="badge bg-primary">Giám đốc</span>
                        @elseif (Auth::user()->isDeputyDirector())
                            <span class="badge bg-success">Phó giám đốc</span>
                        @elseif (Auth::user()->isDepartmentHead())
                            <span class="badge bg-info">Trưởng phòng</span>
                        @elseif (Auth::user()->isDeputyDepartmentHead())
                            <span class="badge bg-warning">Phó trưởng phòng</span>
                        @elseif (Auth::user()->isStaff())
                            <span class="badge bg-secondary">Nhân viên</span>
                        @endif
                    </div>
                    <div>
                        <span class="badge bg-light text-dark">
                            {{ Auth::user()->department->name ?? 'Không có phòng ban' }}
                        </span>
                    </div>
                </div>
            </div> --}}
            <div class="sidebar-menu">
                <ul>
                    @if (Auth::user()->isAdmin())
                        <li class="{{ Route::currentRouteNamed('tasks.index') ? 'active' : '' }}">
                            <a href="{{ route('tasks.index.admin') }}">
                                <i class="fas fa-tasks"></i>
                                <span>Tổng quan công việc</span>
                            </a>
                        </li>
                    @endif

                    @if (Auth::user()->isDirector() ||
                            Auth::user()->isDeputyDirector() ||
                            Auth::user()->isDepartmentHead() ||
                            Auth::user()->isDeputyDepartmentHead())
                        <li class="{{ Route::currentRouteNamed('tasks.managed') ? 'active' : '' }}">
                            <a href="{{ route('tasks.managed') }}">
                                <i class="fas fa-tasks"></i>
                                <span>Công việc quản lý</span>
                            </a>
                        </li>
                    @endif

                    @if (Auth::user()->canAssignTasks() && !Auth::user()->isAdmin())
                        <li class="{{ Route::currentRouteNamed('tasks.assigned') ? 'active' : '' }}">
                            <a href="{{ route('tasks.assigned') }}">
                                <i class="fas fa-paper-plane"></i>
                                <span>Công việc đã giao</span>
                            </a>
                        </li>

                        <li class="{{ Route::currentRouteNamed('tasks.pending-approval') ? 'active' : '' }}">
                            <a href="{{ route('tasks.pending-approval') }}">
                                <i class="fas fa-clipboard-check"></i>
                                <span>Công việc chờ duyệt</span>
                                @php
                                    $pendingCount = DB::table('task_user')
                                        ->join('tasks', 'task_user.task_id', '=', 'tasks.id')
                                        ->where('tasks.created_by', Auth::id())
                                        ->where('task_user.status', 'completed')
                                        ->whereNull('task_user.approved_at')
                                        ->count();
                                @endphp
                                @if ($pendingCount > 0)
                                    <span class="badge bg-danger ms-2">{{ $pendingCount }}</span>
                                @endif
                            </a>
                        </li>
                    @endif

                    @if (!Auth::user()->isDirector() && !Auth::user()->isAdmin())
                        <li
                            class="{{ Route::currentRouteNamed('tasks.received') || Route::currentRouteNamed('tasks.received.*') ? 'active' : '' }}">
                            <a href="{{ route('tasks.received') }}">
                                <i class="fas fa-tasks"></i>
                                <span>Công việc được giao</span>

                                @php
                                    // Đếm số lượng công việc mới (sending, viewed)
                                    $newTasksCount = DB::table('task_user')
                                        ->where('user_id', Auth::id())
                                        ->whereIn('status', [
                                            \App\Models\TaskUser::STATUS_SENDING,
                                            \App\Models\TaskUser::STATUS_VIEWED,
                                        ])
                                        ->count();

                                    // Đếm số lượng công việc đang thực hiện
                                    $inProgressCount = DB::table('task_user')
                                        ->where('user_id', Auth::id())
                                        ->where('status', \App\Models\TaskUser::STATUS_IN_PROGRESS)
                                        ->count();
                                @endphp

                                @if ($newTasksCount > 0)
                                    <span class="badge bg-danger ms-2 badge-new">{{ $newTasksCount }}</span>
                                @endif

                                @if ($inProgressCount > 0)
                                    <span class="badge bg-info ms-1 badge-in-progress">{{ $inProgressCount }}</span>
                                @endif
                            </a>
                        </li>
                    @endif

                    @if (
                        (Auth::user()->canAssignTasks() && Auth::user()->isDirector()) ||
                            Auth::user()->isDeputyDirector() ||
                            Auth::user()->isDepartmentHead() ||
                            Auth::user()->isDeputyDepartmentHead())
                        <li class="{{ Route::currentRouteNamed('tasks.create') ? 'active' : '' }}">
                            <a href="{{ route('tasks.create') }}">
                                <i class="fas fa-plus"></i>
                                <span>Tạo công việc</span>
                            </a>
                        </li>
                    @endif

                    @if (Auth::user()->canAssignTasks() && !Auth::user()->isAdmin())
                        <li class="{{ Route::currentRouteNamed('task-extensions.index') ? 'active' : '' }}">
                            <a href="{{ route('task-extensions.index') }}">
                                <i class="fas fa-coffee"></i>
                                <span>Yêu cầu gia hạn</span>
                            </a>
                        </li>
                    @endif

                    <li class="{{ Route::currentRouteNamed('tasks.statistics') ? 'active' : '' }}">
                        <a href="{{ route('tasks.statistics') }}">
                            <i class="fa-regular fa-newspaper"></i>
                            <span>Thống kê báo cáo</span>
                        </a>
                    </li>

                    @if (Auth::user()->isAdmin())
                        <li class="{{ Route::currentRouteNamed('roles.index') ? 'active' : '' }}">
                            <a href="{{ route('roles.index') }}">
                                <i class="nav-icon fas fa-user-tag"></i>
                                <span>Quản lý vai trò</span>
                            </a>
                        </li>
                    @endif


                    <li class="mt-4">
                        <a href="#"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Đăng xuất</span>
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Toggle sidebar button - half-circle attached to the sidebar edge -->
        <button id="toggle-sidebar" class="toggle-sidebar-btn">
            <i class="fas fa-chevron-left"></i>
        </button>

        <!-- Main Content -->
        <div class="main-content">
            <div class="content-wrapper">
                <div class="content-header">
                    <div class="container-fluid">
                        <div class="d-flex justify-content-between align-items-center">
                            <h1 class="page-title">@yield('title', 'Dashboard')</h1>
                            <div class="d-flex align-items-center">
                                
                                @php
                                   
                                    $newTasksCount = DB::table('task_user')
                                        ->where('user_id', Auth::id())
                                        ->whereIn('status', [
                                            \App\Models\TaskUser::STATUS_SENDING,
                                            \App\Models\TaskUser::STATUS_VIEWED,
                                        ])
                                        ->count();
                                @endphp

                                @if ($newTasksCount > 0)
                                    <a href="{{ route('tasks.received') }}" class="notification-bell-container me-3">
                                        <i class="fas fa-bell notification-bell"></i>
                                        <span class="notification-badge">{{ $newTasksCount }}</span>
                                    </a>
                                @endif
                                <div class="user-avatar-container me-2">
                                    @php
                                        // Lấy tên người dùng và tạo các ký tự đầu
                                        $nameParts = explode(' ', trim(Auth::user()->name));
                                        $initials = '';

                                        // Lấy chữ cái đầu từ tên (nếu có 2 từ trở lên, lấy từ đầu và từ cuối)
                                        if (count($nameParts) >= 2) {
                                            $initials =
                                                mb_substr($nameParts[0], 0, 1, 'UTF-8') .
                                                mb_substr(end($nameParts), 0, 1, 'UTF-8');
                                        } else {
                                            // Nếu chỉ có một từ, lấy 2 chữ cái đầu hoặc chữ cái đầu nếu tên quá ngắn
                                            $initials = mb_substr(
                                                $nameParts[0],
                                                0,
                                                min(2, mb_strlen($nameParts[0], 'UTF-8')),
                                                'UTF-8',
                                            );
                                        }

                                        // Chuyển thành chữ hoa
                                        $initials = mb_strtoupper($initials, 'UTF-8');

                                        // Tạo màu ngẫu nhiên nhưng ổn định cho mỗi người dùng
                                        $colorIndex = crc32(Auth::user()->id . Auth::user()->name) % 5;
                                        $bgColors = ['#4f46e5', '#0ea5e9', '#10b981', '#f59e0b', '#ef4444'];
                                        $avatarBgColor = $bgColors[$colorIndex];
                                    @endphp

                                    <div class="user-avatar" style="background-color: {{ $avatarBgColor }};">
                                        {{ $initials }}
                                    </div>
                                </div>
                                <div>

                                    <div class="user-info d-flex align-items-center">
                                        <div class="user-name me-2">
                                            {{ Auth::user()->name }}
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <div class="user-role">
                                            @if (Auth::user()->isAdmin())
                                                <span class="badge bg-danger">Quản trị viên</span>
                                            @elseif (Auth::user()->isDirector())
                                                <span class="badge bg-primary">Giám đốc</span>
                                            @elseif (Auth::user()->isDeputyDirector())
                                                <span class="badge bg-success">Phó giám đốc</span>
                                            @elseif (Auth::user()->isDepartmentHead())
                                                <span class="badge bg-info">Trưởng phòng</span>
                                            @elseif (Auth::user()->isDeputyDepartmentHead())
                                                <span class="badge bg-warning">Phó trưởng phòng</span>
                                            @elseif (Auth::user()->isStaff())
                                                <span class="badge bg-secondary">Nhân viên</span>
                                            @endif
                                        </div>
                                        <hr class="mx-2" style="height: 10px; width: 1px; background-color: #070707;">

                                        <div class="">
                                            <span class="badge bg-light text-dark">
                                                {{ Auth::user()->department->name ?? 'Không có phòng ban' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="content">
                    <div class="container-fluid">
                        @yield('main-content')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
