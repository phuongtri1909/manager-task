@extends('manager_task.layouts.partials.sidebar')

@section('title', 'Thống kê công việc')

@section('main-content')
    <div class="category-container">
        <!-- Breadcrumb -->
        <div class="content-breadcrumb">
            <ol class="breadcrumb-list">
                <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}">Công việc</a></li>
                <li class="breadcrumb-item current">Thống kê</li>
            </ol>
        </div>

        <div class="content-card">
            <div class="card-top">
                <div class="card-title">
                    <i class="fas fa-chart-bar icon-title"></i>
                    <h5>Thống kê công việc</h5>
                </div>
                <div class="statistics-filter">
                    <form action="{{ route('tasks.statistics') }}" method="GET" class="filter-form-inline">
                        <div class="filter-group-inline">
                            <div class="filter-item">
                                <label for="month">Tháng:</label>
                                <select name="month" id="month" class="filter-input filter-input-sm">
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                            {{ str_pad($m, 2, '0', STR_PAD_LEFT) }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="filter-item">
                                <label for="year">Năm:</label>
                                <select name="year" id="year" class="filter-input filter-input-sm">
                                    @for ($y = date('Y'); $y >= date('Y') - 5; $y--)
                                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                                            {{ $y }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <button type="submit" class="filter-btn">
                                <i class="fas fa-filter"></i> Lọc
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card-content">
                <!-- Info Boxes -->
                <div class="stats-boxes">
                    <div class="stats-box stats-box-primary animate-slide-up" style="--delay: 0.1s">
                        <div class="stats-box-icon">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div class="stats-box-content">
                            <div class="stats-box-label">Tổng công việc</div>
                            <div class="stats-box-value">{{ $totalTasks }}</div>
                            <div class="stats-box-progress">
                                <div class="progress">
                                    <div class="progress-bar" style="width: 100%"></div>
                                </div>
                                <div class="progress-text">
                                    Tất cả công việc trong hệ thống
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="stats-box stats-box-success animate-slide-up" style="--delay: 0.2s">
                        <div class="stats-box-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stats-box-content">
                            <div class="stats-box-label">Hoàn thành</div>
                            <div class="stats-box-value">{{ $completedTasks }}</div>
                            <div class="stats-box-progress">
                                <div class="progress">
                                    <div class="progress-bar"
                                        style="width: {{ $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0 }}%">
                                    </div>
                                </div>
                                <div class="progress-text">
                                    {{ $totalTasks > 0 ? number_format(($completedTasks / $totalTasks) * 100, 1) : 0 }}%
                                    công việc đã hoàn thành
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="stats-box stats-box-warning animate-slide-up" style="--delay: 0.3s">
                        <div class="stats-box-icon">
                            <i class="fas fa-spinner"></i>
                        </div>
                        <div class="stats-box-content">
                            <div class="stats-box-label">Đang thực hiện</div>
                            <div class="stats-box-value">{{ $inProgressTasks }}</div>
                            <div class="stats-box-progress">
                                <div class="progress">
                                    <div class="progress-bar"
                                        style="width: {{ $totalTasks > 0 ? ($inProgressTasks / $totalTasks) * 100 : 0 }}%">
                                    </div>
                                </div>
                                <div class="progress-text">
                                    {{ $totalTasks > 0 ? number_format(($inProgressTasks / $totalTasks) * 100, 1) : 0 }}%
                                    công việc đang thực hiện
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="stats-box stats-box-danger animate-slide-up" style="--delay: 0.4s">
                        <div class="stats-box-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="stats-box-content">
                            <div class="stats-box-label">Quá hạn</div>
                            <div class="stats-box-value">{{ $overdueTasks }}</div>
                            <div class="stats-box-progress">
                                <div class="progress">
                                    <div class="progress-bar"
                                        style="width: {{ $totalTasks > 0 ? ($overdueTasks / $totalTasks) * 100 : 0 }}%">
                                    </div>
                                </div>
                                <div class="progress-text">
                                    {{ $totalTasks > 0 ? number_format(($overdueTasks / $totalTasks) * 100, 1) : 0 }}% công
                                    việc quá hạn
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Task Selection Section -->
                <div class="stats-card task-selection-card animate-slide-up full-width" style="--delay: 0.5s">
                    <div class="stats-card-header">
                        <i class="fas fa-search"></i>
                        <span>Xem thống kê theo nhiệm vụ cụ thể</span>
                    </div>
                    <div class="stats-card-body">
                        <form action="{{ route('tasks.statistics') }}" method="GET" class="task-selection-form">
                            <input type="hidden" name="month" value="{{ $month }}">
                            <input type="hidden" name="year" value="{{ $year }}">
                            <div class="task-selection-container">
                                <div class="task-selection-field">
                                    <label for="task_id">Chọn nhiệm vụ:</label>
                                    <select name="task_id" id="task_id" class="filter-input filter-input-lg">
                                        <option value="">-- Chọn nhiệm vụ để xem chi tiết --</option>
                                        @foreach($availableTasks as $task)
                                            <option value="{{ $task->id }}" {{ $selectedTaskId == $task->id ? 'selected' : '' }}>
                                                {{ $task->title }} ({{ $task->created_at->format('d/m/Y') }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="filter-btn task-selection-btn">
                                    <i class="fas fa-chart-line"></i> Xem thống kê
                                </button>
                                @if($selectedTaskId)
                                    <a href="{{ route('tasks.statistics', ['month' => $month, 'year' => $year]) }}" 
                                       class="filter-btn filter-btn-secondary">
                                        <i class="fas fa-undo"></i> Xem tất cả
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="stats-grid">
                    <div class="stats-card animate-slide-up" style="--delay: 0.5s">
                        <div class="stats-card-header stats-card-primary">
                            <i class="fas fa-chart-pie"></i>
                            <span>Phân bố trạng thái công việc</span>
                        </div>
                        <div class="stats-card-body">
                            <canvas id="taskStatusChart" height="250"></canvas>
                        </div>
                    </div>

                    <div class="stats-card animate-slide-up" style="--delay: 0.6s">
                        <div class="stats-card-header stats-card-info">
                            <i class="fas fa-building"></i>
                            <span>Công việc theo phòng ban</span>
                        </div>
                        <div class="stats-card-body">
                            <canvas id="departmentTasksChart" height="250"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Data Tables Section -->
                <div class="stats-grid">
                    <!-- Department Progress Table -->
                    <div class="stats-card animate-slide-up" style="--delay: 0.7s">
                        <div class="stats-card-header">
                            <i class="fas fa-tasks"></i>
                            <span>Tiến độ công việc theo phòng ban</span>
                        </div>
                        <div class="stats-card-body table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Phòng ban</th>
                                        <th class="column-small">Tổng SL</th>
                                        <th>Tiến độ</th>
                                        <th class="column-small text-center">Tỷ lệ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($departmentProgress as $dept)
                                        <tr class="hover-row">
                                            <td>{{ $dept['name'] }}</td>
                                            <td class="text-center">{{ $dept['total'] }}</td>
                                            <td>
                                                <div class="progress-container">
                                                    <div class="progress">
                                                        <div class="progress-success"
                                                            style="width: {{ $dept['completed_percent'] }}%"></div>
                                                        <div class="progress-warning"
                                                            style="width: {{ $dept['in_progress_percent'] }}%"></div>
                                                        <div class="progress-secondary"
                                                            style="width: {{ $dept['pending_percent'] }}%"></div>
                                                    </div>
                                                    <div class="progress-legend">
                                                        <span class="legend-success">{{ $dept['completed'] }} hoàn
                                                            thành</span>
                                                        <span class="legend-warning">{{ $dept['in_progress'] }} đang thực
                                                            hiện</span>
                                                        <span class="legend-secondary">{{ $dept['pending'] }} chưa thực
                                                            hiện</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span
                                                    class="completion-badge {{ $dept['completed_percent'] >= 80 ? 'badge-success' : ($dept['completed_percent'] >= 50 ? 'badge-info' : 'badge-warning') }}">
                                                    {{ number_format($dept['completed_percent'], 1) }}%
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Recent Task Completion -->
                    <div class="stats-card animate-slide-up" style="--delay: 0.8s">
                        <div class="stats-card-header">
                            <i class="fas fa-check-circle"></i>
                            <span>Công việc hoàn thành gần đây</span>
                        </div>
                        <div class="stats-card-body table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Công việc</th>
                                        <th>Người thực hiện</th>
                                        <th>Thời gian hoàn thành</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentCompletions as $completion)
                                        <tr class="hover-row">
                                            <td>
                                                <a href="{{ route('tasks.show', $completion->task_id) }}"
                                                    class="task-link">
                                                    {{ Str::limit($completion->title ?? 'N/A', 40) }}
                                                </a>
                                            </td>
                                            <td>
                                                <div class="user-info">
                                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($completion->name ?? 'N/A') }}&background=random"
                                                        class="user-avatar" alt="User Avatar">
                                                    <span>{{ $completion->name ?? 'N/A' }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="time-info">
                                                    <div class="time-date">
                                                        {{ $completion->completion_date ? \Carbon\Carbon::parse($completion->completion_date)->format('d/m/Y H:i') : 'N/A' }}
                                                    </div>
                                                    <div class="time-relative">
                                                        {{ $completion->completion_date ? \Carbon\Carbon::parse($completion->completion_date)->diffForHumans() : 'N/A' }}
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="empty-data">Không có công việc hoàn thành gần đây
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Top Performers Section -->
                <div class="stats-card full-width animate-slide-up" style="--delay: 0.9s">
                    <div class="stats-card-header stats-card-success">
                        <i class="fas fa-trophy"></i>
                        <span>Nhân viên hiệu quả nhất</span>
                    </div>
                    <div class="stats-card-body">
                        <div class="performer-grid">
                            @forelse($topPerformers as $index => $performer)
                                <div class="performer-card animate-slide-up" style="--delay: {{ 1 + $index * 0.1 }}s">
                                    <div class="performer-avatar">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($performer['user']) }}&background=random"
                                            alt="{{ $performer['user'] }}">
                                        <div class="performer-rank {{ $index < 3 ? 'top-rank' : '' }}">
                                            <i class="fas {{ $index === 0 ? 'fa-crown' : 'fa-star' }}"></i>
                                            {{ $index + 1 }}
                                        </div>
                                    </div>
                                    <div class="performer-name">{{ $performer['user'] }}</div>
                                    <div class="performer-dept">{{ $performer['department'] }}</div>
                                    <div class="performer-score">{{ $performer['completed'] }} hoàn thành</div>
                                </div>
                            @empty
                                <div class="empty-data centered">Không có dữ liệu để hiển thị</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Task Comparison Section -->
                <div class="stats-card full-width animate-slide-up" style="--delay: 1.0s">
                    <div class="stats-card-header stats-card-info">
                        <i class="fas fa-tasks"></i>
                        <span>So sánh tiến độ theo nhiệm vụ</span>
                    </div>
                    <div class="stats-card-body">
                        @if($selectedTaskId && !$selectedTaskDetails)
                            <div class="empty-data centered">
                                <i class="fas fa-exclamation-circle text-warning mb-2" style="font-size: 24px;"></i>
                                <p>Không tìm thấy dữ liệu cho nhiệm vụ đã chọn hoặc bạn không có quyền xem nhiệm vụ này.</p>
                                <a href="{{ route('tasks.statistics', ['month' => $month, 'year' => $year]) }}" class="task-detail-btn mt-3">
                                    <i class="fas fa-undo"></i> Quay lại xem tất cả
                                </a>
                            </div>
                        @elseif($selectedTaskDetails)
                            <!-- Single task detailed view -->
                            <div class="detailed-task-container">
                                <div class="detailed-task-header">
                                    <div class="detailed-task-title">
                                        <h4><a href="{{ route('tasks.show', $selectedTaskDetails['id']) }}">{{ $selectedTaskDetails['title'] }}</a></h4>
                                    </div>
                                    <div class="detailed-task-meta">
                                        <div class="meta-item">
                                            <span class="meta-label"><i class="fas fa-clock"></i> Thời hạn:</span>
                                            <span class="meta-value">{{ $selectedTaskDetails['deadline'] }}</span>
                                        </div>
                                        <div class="meta-item">
                                            <span class="meta-label"><i class="fas fa-user"></i> Người tạo:</span>
                                            <span class="meta-value">{{ $selectedTaskDetails['created_by'] }}</span>
                                        </div>
                                        <div class="meta-item">
                                            <span class="meta-label"><i class="fas fa-chart-pie"></i> Tỷ lệ hoàn thành:</span>
                                            <span class="meta-value completion-badge {{ $selectedTaskDetails['completion_rate'] >= 80 ? 'badge-success' : ($selectedTaskDetails['completion_rate'] >= 50 ? 'badge-info' : 'badge-warning') }}">
                                                {{ number_format($selectedTaskDetails['completion_rate'], 1) }}%
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="detailed-task-content">
                                    <div class="detailed-task-chart">
                                        <div class="chart-header">
                                            <h5>
                                                <i class="fas {{ $selectedTaskDetails['for_departments'] ? 'fa-building' : 'fa-users' }}"></i>
                                                Tiến độ theo {{ $selectedTaskDetails['for_departments'] ? 'phòng ban' : 'người dùng' }}
                                            </h5>
                                            <span class="summary-badge">
                                                {{ $selectedTaskDetails['for_departments'] ? count($selectedTaskDetails['departments']) : count($selectedTaskDetails['users']) }}
                                                {{ $selectedTaskDetails['for_departments'] ? 'phòng ban' : 'người dùng' }}
                                            </span>
                                        </div>
                                        
                                        <div class="comparison-chart">
                                            @if($selectedTaskDetails['for_departments'])
                                                <!-- Department comparison -->
                                                <div class="comparison-bars">
                                                    @foreach($selectedTaskDetails['departments'] as $dept)
                                                        <div class="comparison-bar-item">
                                                            <div class="comparison-label">{{ $dept['department_name'] }}</div>
                                                            <div class="comparison-bar-container">
                                                                <div class="comparison-bar" style="width: {{ $dept['completion_rate'] }}%"></div>
                                                                <div class="comparison-value">{{ number_format($dept['completion_rate'], 1) }}%</div>
                                                            </div>
                                                            <div class="comparison-details">
                                                                <span class="detail-item">
                                                                    <i class="fas fa-user-check text-success"></i>
                                                                    {{ $dept['completed_users'] }}/{{ $dept['total_users'] }}
                                                                </span>
                                                                @if($dept['overdue_users'] > 0)
                                                                    <span class="detail-item text-danger">
                                                                        <i class="fas fa-exclamation-triangle"></i>
                                                                        {{ $dept['overdue_users'] }} quá hạn
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <!-- User comparison -->
                                                <div class="comparison-bars">
                                                    @foreach($selectedTaskDetails['users'] as $userStat)
                                                        <div class="comparison-bar-item">
                                                            <div class="comparison-label user-label">
                                                                <span class="user-name">{{ $userStat['user_name'] }}</span>
                                                                <span class="user-dept">({{ $userStat['department'] }})</span>
                                                            </div>
                                                            <div class="comparison-bar-container">
                                                                <div class="comparison-bar {{ $userStat['is_overdue'] ? 'overdue' : '' }}" 
                                                                    style="width: {{ $userStat['completion_rate'] }}%"></div>
                                                                <div class="comparison-value">{{ number_format($userStat['completion_rate'], 1) }}%</div>
                                                            </div>
                                                            <div class="comparison-details">
                                                                <span class="detail-item {{ $userStat['status'] == 'completed' ? 'text-success' : ($userStat['is_overdue'] ? 'text-danger' : '') }}">
                                                                    <i class="fas {{ $userStat['status'] == 'completed' ? 'fa-check-circle' : ($userStat['is_overdue'] ? 'fa-exclamation-triangle' : 'fa-spinner') }}"></i>
                                                                    {{ $userStat['status'] == 'completed' ? 'Hoàn thành' : ($userStat['is_overdue'] ? 'Quá hạn' : 'Đang thực hiện') }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="detailed-task-footer">
                                    <div class="detailed-task-summary">
                                        <div class="summary-item {{ $selectedTaskDetails['completion_rate'] >= 80 ? 'success' : ($selectedTaskDetails['completion_rate'] >= 50 ? 'info' : 'warning') }}">
                                            <div class="summary-value">{{ number_format($selectedTaskDetails['completion_rate'], 1) }}%</div>
                                            <div class="summary-label">Tỷ lệ hoàn thành</div>
                                        </div>
                                    </div>
                                    <div class="detailed-task-actions">
                                        <a href="{{ route('tasks.show', $selectedTaskDetails['id']) }}" class="task-detail-btn">
                                            <i class="fas fa-eye"></i> Xem nhiệm vụ
                                        </a>
                                        <a href="{{ route('tasks.statistics', ['month' => $month, 'year' => $year]) }}" class="task-detail-btn secondary">
                                            <i class="fas fa-list"></i> Xem tất cả nhiệm vụ
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @elseif(empty($taskDetailStats))
                            <div class="empty-data centered">Không có dữ liệu nhiệm vụ trong thời gian đã chọn</div>
                        @else
                            <div class="task-comparison-grid">
                                @foreach (collect($taskDetailStats)->chunk(3) as $taskChunk)
                                    <div class="task-comparison-row">
                                        @foreach ($taskChunk as $task)
                                            <div class="task-comparison-col">
                                                <div class="task-comparison-item">
                                                    <div class="task-comparison-header">
                                                        <div class="task-comparison-title">
                                                            <a href="{{ route('tasks.show', $task['id']) }}">{{ Str::limit($task['title'], 70) }}</a>
                                                        </div>
                                                        <div class="task-comparison-info">
                                                            <div class="task-comparison-meta">
                                                                <span class="info-label"><i class="fas fa-clock"></i> Thời hạn:</span>
                                                                <span class="info-value">{{ $task['deadline'] }}</span>
                                                            </div>
                                                            <div class="task-comparison-meta">
                                                                <span class="info-label"><i class="fas fa-user"></i> Người tạo:</span>
                                                                <span class="info-value">{{ $task['created_by'] }}</span>
                                                            </div>
                                                            <div class="task-comparison-meta">
                                                                <span class="info-label"><i class="fas fa-chart-pie"></i> Tỷ lệ hoàn thành:</span>
                                                                <span class="info-value completion-badge {{ $task['completion_rate'] >= 80 ? 'badge-success' : ($task['completion_rate'] >= 50 ? 'badge-info' : 'badge-warning') }}">
                                                                    {{ number_format($task['completion_rate'], 1) }}%
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="task-comparison-body">
                                                        @if ($task['for_departments'])
                                                            <!-- Department comparison -->
                                                            <div class="comparison-chart-container">
                                                                <h5 class="chart-title">Tiến độ theo phòng ban</h5>
                                                                <div class="comparison-bars">
                                                                    @foreach ($task['departments'] as $dept)
                                                                        <div class="comparison-bar-item">
                                                                            <div class="comparison-label">
                                                                                {{ $dept['department_name'] }}</div>
                                                                            <div class="comparison-bar-container">
                                                                                <div class="comparison-bar"
                                                                                    style="width: {{ $dept['completion_rate'] }}%">
                                                                                </div>
                                                                                <div class="comparison-value">
                                                                                    {{ number_format($dept['completion_rate'], 1) }}%
                                                                                </div>
                                                                            </div>
                                                                            <div class="comparison-details">
                                                                                <span class="detail-item">
                                                                                    <i class="fas fa-user-check text-success"></i>
                                                                                    {{ $dept['completed_users'] }}/{{ $dept['total_users'] }}
                                                                                </span>
                                                                                @if ($dept['overdue_users'] > 0)
                                                                                    <span class="detail-item text-danger">
                                                                                        <i class="fas fa-exclamation-triangle"></i>
                                                                                        {{ $dept['overdue_users'] }} quá hạn
                                                                                    </span>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @else
                                                            <!-- User comparison -->
                                                            <div class="comparison-chart-container">
                                                                <h5 class="chart-title">Tiến độ theo người dùng</h5>
                                                                <div class="comparison-bars">
                                                                    @foreach ($task['users'] as $userStat)
                                                                        <div class="comparison-bar-item">
                                                                            <div class="comparison-label user-label">
                                                                                <span class="user-name">{{ $userStat['user_name'] }}</span>
                                                                                <span class="user-dept">({{ $userStat['department'] }})</span>
                                                                            </div>
                                                                            <div class="comparison-bar-container">
                                                                                <div class="comparison-bar {{ $userStat['is_overdue'] ? 'overdue' : '' }}"
                                                                                    style="width: {{ $userStat['completion_rate'] }}%">
                                                                                </div>
                                                                                <div class="comparison-value">
                                                                                    {{ number_format($userStat['completion_rate'], 1) }}%
                                                                                </div>
                                                                            </div>
                                                                            <div class="comparison-details">
                                                                                <span class="detail-item {{ $userStat['status'] == 'completed' ? 'text-success' : ($userStat['is_overdue'] ? 'text-danger' : '') }}">
                                                                                    <i class="fas {{ $userStat['status'] == 'completed' ? 'fa-check-circle' : ($userStat['is_overdue'] ? 'fa-exclamation-triangle' : 'fa-spinner') }}"></i>
                                                                                    {{ $userStat['status'] == 'completed' ? 'Hoàn thành' : ($userStat['is_overdue'] ? 'Quá hạn' : 'Đang thực hiện') }}
                                                                                </span>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="task-comparison-footer">
                                                        <a href="{{ route('tasks.statistics', ['task_id' => $task['id'], 'month' => $month, 'year' => $year]) }}" class="task-detail-btn">
                                                            <i class="fas fa-chart-line"></i> Chi tiết
                                                        </a>
                                                        <a href="{{ route('tasks.show', $task['id']) }}" class="task-detail-btn">
                                                            <i class="fas fa-eye"></i> Xem
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach

                                        @for ($i = count($taskChunk); $i < 3; $i++)
                                            <div class="task-comparison-col empty"></div>
                                        @endfor
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Timeline Section -->
                {{-- <div class="stats-card full-width animate-slide-up" style="--delay: 1.1s">
                    <div class="stats-card-header">
                        <i class="fas fa-history"></i>
                        <span>Hoạt động gần đây</span>
                    </div>
                    <div class="stats-card-body">
                        <div class="timeline">
                            @forelse($recentActivities as $index => $activity)
                                <div class="timeline-date">
                                    <span>{{ $activity['date'] }}</span>
                                </div>

                                @foreach ($activity['items'] as $item)
                                    <div class="timeline-item">
                                        <div class="timeline-icon {{ $item['color'] }}">
                                            <i class="fas {{ $item['icon'] }}"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <div class="timeline-time"><i class="far fa-clock"></i> {{ $item['time'] }}
                                            </div>
                                            <div class="timeline-header">
                                                <a href="#">{{ $item['user'] }}</a> {{ $item['action'] }}
                                            </div>
                                            <div class="timeline-body">
                                                {{ $item['description'] }}
                                            </div>
                                            @if (isset($item['task_id']))
                                                <div class="timeline-footer">
                                                    <a href="{{ route('tasks.show', $item['task_id']) }}"
                                                        class="timeline-btn">Xem chi tiết</a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach

                                @if ($index == count($recentActivities) - 1)
                                    <div class="timeline-end">
                                        <i class="far fa-clock"></i>
                                    </div>
                                @endif
                            @empty
                                <div class="empty-data centered">Không có hoạt động gần đây</div>
                            @endforelse
                        </div>
                    </div>
                </div> --}}
            </div>
        </div>

        <div class="text-end mt-4">
            <a href="{{ route('tasks.index') }}" class="action-button">
                <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách công việc
            </a>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        {{-- filepath: d:\manager-task\resources\views\manager_task\tasks\statistics.blade.php --}} {{-- Thêm vào phần @push('styles') --}}

        /* Task selection styles */
        .task-selection-card {
            margin-bottom: 30px;
        }
        
        .task-selection-form {
            width: 100%;
        }
        
        .task-selection-container {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .task-selection-field {
            flex: 1;
            min-width: 300px;
        }
        
        .task-selection-field label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #4b5563;
        }
        
        .filter-input-lg {
            width: 100%;
            height: 40px;
            padding: 8px 15px;
            border-radius: 5px;
            border: 1px solid #ced4da;
            background-color: #fff;
            transition: all 0.3s ease;
        }
        
        .filter-input-lg:focus {
            outline: none;
            border-color: #1a56db;
            box-shadow: 0 0 0 2px rgba(26, 86, 219, 0.25);
        }
        
        .task-selection-btn {
            padding: 8px 20px;
            height: 40px;
            margin-top: 24px;
        }
        
        .filter-btn-secondary {
            background-color: #6c757d;
        }
        
        .filter-btn-secondary:hover {
            background-color: #5a6268;
        }

        /* Task comparison grid styles */
        .task-comparison-grid {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }
        
        .task-comparison-row {
            display: flex;
            gap: 20px;
            margin-bottom: 10px;
        }
        
        .task-comparison-col {
            flex: 1;
            min-width: 0; /* Allows columns to shrink below content size */
        }
        
        .task-comparison-col.empty {
            flex: 1;
        }

        .task-comparison-item {
            background-color: #f9fbfd;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .task-comparison-item:hover {
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.15);
            transform: translateY(-3px);
        }

        .task-comparison-header {
            padding: 15px 20px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 10px;
        }

        .task-comparison-title {
            font-size: 16px;
            font-weight: 600;
        }

        .task-comparison-title a {
            color: #1a56db;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .task-comparison-title a:hover {
            color: #1a56db;
            text-decoration: underline;
        }

        .task-comparison-info {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .task-comparison-meta {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 13px;
            color: #64748b;
        }

        .task-comparison-meta i {
            color: #1a56db;
        }

        .task-comparison-body {
            padding: 15px 20px;
            flex-grow: 1; /* Makes the body take available space */
        }

        .comparison-chart-container {
            margin-top: 10px;
        }

        .chart-title {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 12px;
            color: #4b5563;
            padding-bottom: 5px;
            border-bottom: 1px dashed rgba(0, 0, 0, 0.05);
        }

        .comparison-bars {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 5px;
        }

        .comparison-bar-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .comparison-label {
            display: flex;
            justify-content: space-between;
            font-weight: 500;
            color: #374151;
            margin-bottom: 3px;
        }

        .comparison-bar-container {
            height: 20px;
            background-color: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
            position: relative;
        }

        .comparison-bar {
            height: 100%;
            background: linear-gradient(to right, #10b981, #059669);
            border-radius: 6px;
            transition: width 1s ease-out;
        }

        .comparison-bar.overdue {
            background: linear-gradient(to right, #f59e0b, #d97706);
        }

        .comparison-value {
            position: absolute;
            right: 10px;
            top: 0;
            height: 100%;
            display: flex;
            align-items: center;
            color: white;
            font-weight: 600;
            font-size: 12px;
        }

        .comparison-details {
            display: flex;
            gap: 15px;
            font-size: 12px;
            color: #6b7280;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .user-label {
            display: flex;
            gap: 5px;
            align-items: baseline;
        }

        .user-name {
            font-weight: 600;
        }

        .user-dept {
            font-size: 12px;
            color: #6b7280;
            font-weight: normal;
        }
        
        .task-comparison-footer {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 10px;
            padding: 10px 15px;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            background-color: rgba(0, 0, 0, 0.01);
        }
        
        .task-detail-btn {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            background-color: #1a56db;
            color: white;
            border-radius: 5px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .task-detail-btn i {
            margin-right: 6px;
        }
        
        .task-detail-btn:hover {
            background-color: #1a56db;
            transform: translateY(-2px);
            color: white;
        }
        
        .task-detail-btn.secondary {
            background-color: #6c757d;
        }
        
        .task-detail-btn.secondary:hover {
            background-color: #5a6268;
        }
        
        .detailed-view {
            background-color: #f8fafc;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
        }

        @media (max-width: 1200px) {
            .task-comparison-row {
                flex-wrap: wrap;
            }
            
            .task-comparison-col {
                flex: 0 0 calc(50% - 10px);
                margin-bottom: 20px;
            }
            
            .task-comparison-col.empty {
                display: none;
            }
        }
        
        @media (max-width: 768px) {
            .task-comparison-header {
                flex-direction: column;
                gap: 10px;
            }

            .task-comparison-info {
                flex-direction: column;
                gap: 8px;
            }
            
            .task-comparison-col {
                flex: 0 0 100%;
            }
        }

        .stats-boxes {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stats-box {
            display: flex;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .stats-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        }

        .stats-box-icon {
            width: 70px;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }

        .stats-box-primary .stats-box-icon {
            background: linear-gradient(135deg, #1a56db, #1a56db);
        }

        .stats-box-success .stats-box-icon {
            background: linear-gradient(135deg, #059669, #12203f);
        }

        .stats-box-warning .stats-box-icon {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        .stats-box-danger .stats-box-icon {
            background: linear-gradient(135deg, #b91c1c, #b91c1c);
        }

        .stats-box-content {
            padding: 15px;
            flex: 1;
        }

        .stats-box-label {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 5px;
        }

        .stats-box-value {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .progress {
            height: 8px;
            background-color: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 5px;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #1a56db, #1a56db);
        }

        .stats-box-primary .progress-bar {
            background: linear-gradient(90deg, #1a56db, #1a56db);
        }

        .stats-box-success .progress-bar {
            background: linear-gradient(90deg, #059669, #059669);
        }

        .stats-box-warning .progress-bar {
            background: linear-gradient(90deg, #f59e0b, #f59e0b);
        }

        .stats-box-danger .progress-bar {
            background: linear-gradient(90deg, #b91c1c, #b91c1c);
        }

        .progress-text {
            font-size: 12px;
            color: #6c757d;
        }

        /* Cards for charts and tables */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        @media (max-width: 992px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        .stats-card {
            background-color: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .stats-card:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
            transform: translateY(-3px);
        }

        .stats-card.full-width {
            grid-column: 1 / -1;
        }

        .stats-card-header {
            padding: 15px 20px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            font-weight: 600;
        }

        .stats-card-header i {
            font-size: 16px;
            margin-right: 10px;
        }

        .stats-card-primary {
            background: linear-gradient(135deg, #1a56db, #12203f);
            color: white;
        }

        .stats-card-info {
            background: linear-gradient(135deg, #30b8f8, #1a56db);
            color: white;
        }

        .stats-card-success {
            background: linear-gradient(135deg, #059669, #12203f);
        }

        .stats-card-body {
            padding: 20px;
        }

        .table-container {
            overflow-x: auto;
            padding: 0;
        }

        /* Performance table */
        .progress-container {
            width: 100%;
        }

        .progress {
            height: 8px;
            background-color: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 8px;
            display: flex;
        }

        .progress-success {
            height: 100%;
            background-color: #059669;
        }

        .progress-warning {
            height: 100%;
            background-color: #f59e0b;
        }

        .progress-secondary {
            height: 100%;
            background-color: #5a6268;
        }

        .progress-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            font-size: 12px;
        }

        .legend-success {
            color: #059669;
        }

        .legend-warning {
            color: #f59e0b;
        }

        .legend-secondary {
            color: #5a6268;
        }

        .completion-badge {
            display: inline-block;
            padding: 5px 10px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 20px;
            color: white;
        }

        .badge-success {
            background-color: #059669;
        }

        .badge-info {
            background-color: #30b8f8;
        }

        .badge-warning {
            background-color: #f59e0b;
        }

        /* User with avatar */
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .time-info {
            display: flex;
            flex-direction: column;
        }

        .time-date {
            font-weight: 500;
        }

        .time-relative {
            font-size: 12px;
            color: #6c757d;
        }

        /* Top performers */
        .performer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 20px;
            margin-top: 10px;
        }

        .performer-card {
            text-align: center;
            padding: 20px 10px;
            background: rgba(0, 0, 0, 0.02);
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .performer-card:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-5px);
            background-color: white;
        }

        .performer-avatar {
            position: relative;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin: 0 auto 15px;
        }

        .performer-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid rgba(0, 0, 0, 0.05);
        }

        .performer-rank {
            position: absolute;
            top: -5px;
            right: -5px;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            background-color: #30b8f8;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
        }

        .performer-rank.top-rank {
            background-color: #f59e0b;
            width: 30px;
            height: 30px;
        }

        .performer-name {
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 5px;
            color: #000;
        }

        .performer-dept {
            font-size: 13px;
            color: #6c757d;
            margin-bottom: 10px;
        }

        .performer-score {
            display: inline-block;
            background: #059669;
            color: white;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        /* Timeline */
        .timeline {
            position: relative;
            margin: 0 0 15px;
            padding-left: 20px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            width: 3px;
            background: #e9ecef;
            left: 0;
            border-radius: 2px;
        }

        .timeline-date {
            position: relative;
            margin-bottom: 20px;
        }

        .timeline-date span {
            background: #1a56db;
            padding: 5px 15px;
            color: white;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 15px;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 30px;
            padding-left: 30px;
        }

        .timeline-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #1a56db;
            position: absolute;
            left: -15px;
            top: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            z-index: 1;
        }

        .timeline-icon.bg-blue {
            background-color: #1a56db;
        }

        .timeline-icon.bg-green {
            background-color: #059669;
        }

        .timeline-icon.bg-red {
            background-color: #b91c1c;
        }

        .timeline-icon.bg-yellow {
            background-color: #f59e0b;
            color: #000;
        }

        .timeline-icon.bg-gray {
            background-color: #5a6268;
        }

        .timeline-content {
            background: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            position: relative;
            transition: all 0.3s ease;
        }

        .timeline-content:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transform: translateX(5px);
        }

        .timeline-time {
            color: #6c757d;
            font-size: 12px;
            margin-bottom: 10px;
        }

        .timeline-header {
            font-weight: 600;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .timeline-header a {
            color: #1a56db;
            text-decoration: none;
        }

        .timeline-body {
            margin-bottom: 15px;
            font-size: 14px;
        }

        .timeline-footer {
            text-align: right;
        }

        .timeline-btn {
            display: inline-block;
            padding: 5px 15px;
            background-color: #1a56db;
            color: white;
            border-radius: 5px;
            font-size: 12px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .timeline-btn:hover {
            background-color: #1a56db;
            color: white;
            transform: translateY(-2px);
        }

        .timeline-end {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #e9ecef;
            position: relative;
            left: -15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            margin-top: 20px;
        }

        .empty-data {
            padding: 20px;
            text-align: center;
            color: #6c757d;
            font-style: italic;
        }

        .empty-data.centered {
            padding: 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        
        .empty-data.centered i {
            margin-bottom: 10px;
        }
        
        .mt-3 {
            margin-top: 15px;
        }

        /* Animation */
        .animate-slide-up {
            opacity: 0;
            transform: translateY(20px);
            animation: slideUp 0.5s ease forwards;
            animation-delay: var(2, 0s);
        }

        @keyframes slideUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Filter styles similar to index.blade.php */
        .filter-form-inline {
            display: flex;
            align-items: center;
        }

        .filter-group-inline {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .filter-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filter-input-sm {
            height: 35px;
            padding: 0 10px;
            border-radius: 5px;
            border: 1px solid #ced4da;
        }

        /* Hover effect for table rows */
        .hover-row {
            transition: all 0.3s ease;
        }

        .hover-row:hover {
            background-color: rgba(0, 0, 0, 0.03);
            transform: translateX(5px);
        }

        /* Task link */
        .task-link {
            color: #1a56db;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .task-link:hover {
            color: #1a56db;
            text-decoration: underline;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .stats-boxes {
                grid-template-columns: 1fr;
            }

            .stats-card-header {
                padding: 12px 15px;
            }

            .performer-grid {
                grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            }

            .filter-group-inline {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Enhanced Chart.js defaults
            Chart.defaults.font.family = "'Nunito', 'Helvetica', 'Arial', sans-serif";
            Chart.defaults.font.size = 13;
            Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(0, 0, 0, 0.8)';
            Chart.defaults.plugins.tooltip.titleFont.weight = 'bold';
            Chart.defaults.plugins.tooltip.bodyFont.weight = 'bold';
            Chart.defaults.plugins.tooltip.padding = 10;
            Chart.defaults.plugins.tooltip.cornerRadius = 6;

            // Task Status Chart
            const taskStatusCtx = document.getElementById('taskStatusChart').getContext('2d');
            const taskStatusChart = new Chart(taskStatusCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Hoàn thành', 'Đang thực hiện', 'Chưa thực hiện', 'Quá hạn'],
                    datasets: [{
                        data: [
                            {{ $completedTasks }},
                            {{ $inProgressTasks }},
                            {{ $pendingTasks }},
                            {{ $overdueTasks }}
                        ],
                        backgroundColor: [
                            '#10B981', // Success
                            '#F59E0B', // Warning
                            '#64748B', // Secondary
                            '#EF4444' // Danger
                        ],
                        borderWidth: 0,
                        hoverOffset: 15,
                        hoverBorderWidth: 2,
                        hoverBorderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                font: {
                                    size: 12,
                                    weight: '500'
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const value = context.raw;
                                    const total = context.dataset.data.reduce((acc, val) => acc + val,
                                        0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${context.label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    },
                    cutout: '65%',
                    animation: {
                        animateScale: true,
                        animateRotate: true,
                        duration: 1500,
                        easing: 'easeOutCubic',
                        delay: function(context) {
                            return context.dataIndex * 100;
                        }
                    }
                }
            });

            // Department Tasks Chart with enhanced visuals
            const departmentTasksCtx = document.getElementById('departmentTasksChart').getContext('2d');
            const departmentTasksChart = new Chart(departmentTasksCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($departmentChartData['labels']) !!},
                    datasets: [{
                            label: 'Hoàn thành',
                            data: {!! json_encode($departmentChartData['completed']) !!},
                            backgroundColor: '#10B981',
                            borderWidth: 0,
                            borderRadius: 4,
                            hoverBackgroundColor: '#059669'
                        },
                        {
                            label: 'Đang thực hiện',
                            data: {!! json_encode($departmentChartData['in_progress']) !!},
                            backgroundColor: '#F59E0B',
                            borderWidth: 0,
                            borderRadius: 4,
                            hoverBackgroundColor: '#D97706'
                        },
                        {
                            label: 'Chưa thực hiện',
                            data: {!! json_encode($departmentChartData['pending']) !!},
                            backgroundColor: '#64748B',
                            borderWidth: 0,
                            borderRadius: 4,
                            hoverBackgroundColor: '#475569'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'rect',
                                padding: 20,
                                font: {
                                    size: 12,
                                    weight: '500'
                                }
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    },
                    scales: {
                        x: {
                            stacked: true,
                            grid: {
                                display: false
                            },
                            ticks: {
                                maxRotation: 45,
                                minRotation: 30
                            }
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                stepSize: 1
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        }
                    },
                    animation: {
                        duration: 1200,
                        easing: 'easeOutQuart',
                        delay: function(context) {
                            // Stage the animation of each dataset
                            return context.datasetIndex * 100 + context.dataIndex * 20;
                        }
                    },
                    barPercentage: 0.7,
                    categoryPercentage: 0.8
                }
            });
        });
    </script>
@endpush
