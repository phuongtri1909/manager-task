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
            <div class="sidebar-user">
                <div class="user-info">
                    {{-- <img src="{{ asset('assets/images/user.png') }}" alt="User Avatar" class="user-avatar"> --}}
                    <div class="user-name">
                        {{ Auth::user()->name }}
                    </div>
                </div>
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
            </div>
            <div class="sidebar-menu">
                <ul>
                    <li class="{{ Route::currentRouteNamed('tasks.index') ? 'active' : '' }}">
                        <a href="{{ route('tasks.index') }}">
                            <i class="fas fa-tasks"></i>
                            <span>Danh sách công việc</span>
                        </a>
                    </li>

                    @if (Auth::user()->canAssignTasks() && Auth::user()->isDirector() || Auth::user()->isDeputyDirector() || Auth::user()->isDepartmentHead() || Auth::user()->isDeputyDepartmentHead())
                        <li class="{{ Route::currentRouteNamed('tasks.create') ? 'active' : '' }}">
                            <a href="{{ route('tasks.create') }}">
                                <i class="fas fa-plus"></i>
                                <span>Tạo công việc</span>
                            </a>
                        </li>
                    @endif

                    <li class="{{ Route::currentRouteNamed('task-extensions.index') ? 'active' : '' }}">
                        <a href="{{ route('task-extensions.index') }}">
                            <i class="fas fa-coffee"></i>
                            <span>Yêu cầu gia hạn</span>
                        </a>
                    </li>

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
                        <h1 class="page-title">@yield('title', 'Dashboard')</h1>
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
