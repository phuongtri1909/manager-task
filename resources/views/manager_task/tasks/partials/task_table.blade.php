<table class="table table-striped table-bordered task-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Tiêu đề</th>
            <th>Người tạo</th>
            <th>Thời hạn</th>
            <th>Trạng thái</th>
            <th style="width: 120px;">Thao tác</th>
        </tr>
    </thead>
    <tbody>
        @forelse($taskList as $task)
            <tr class="task-row" data-href="{{ route('tasks.show', $task) }}">
                <td>{{ $task->id }}</td>
                <td>
                    {{ Str::limit($task->title, 50) }}
                    @if(now()->diffInHours($task->created_at) < 24)
                        <span class="badge bg-success">Mới</span>
                    @endif
                </td>
                <td>{{ $task->creator->name ?? 'N/A' }}</td>
                <td>
                    {{ $task->deadline->format('d/m/Y H:i') }}
                    @if(now() > $task->deadline && (is_string($task->status) ? $task->status !== 'completed' : true))
                        <span class="badge bg-danger badge-deadline">Quá hạn</span>
                    @elseif($task->deadline->diffInDays(now()) <= 3)
                        <span class="badge bg-warning text-dark badge-deadline">Sắp hết hạn</span>
                    @endif
                </td>
                <td>
                    @if(is_string($task->status))
                        <span class="badge 
                            {{ $task->status === 'pending' ? 'bg-secondary' : 
                              ($task->status === 'in_progress' ? 'bg-primary' : 
                              ($task->status === 'completed' ? 'bg-success' : 'bg-info')) }}">
                            {{ $task->status === 'pending' ? 'Chưa thực hiện' : 
                              ($task->status === 'in_progress' ? 'Đang thực hiện' : 
                              ($task->status === 'completed' ? 'Hoàn thành' : $task->status)) }}
                        </span>
                    @else
                        @php
                            $userStatus = Auth::user()->tasks->where('id', $task->id)->first()->pivot->status ?? 'pending';
                        @endphp
                        <span class="badge 
                            {{ $userStatus === 'pending' ? 'bg-secondary' : 
                              ($userStatus === 'in_progress' ? 'bg-primary' : 
                              ($userStatus === 'completed' ? 'bg-success' : 'bg-info')) }}">
                            {{ $userStatus === 'pending' ? 'Chưa thực hiện' : 
                              ($userStatus === 'in_progress' ? 'Đang thực hiện' : 
                              ($userStatus === 'completed' ? 'Hoàn thành' : $userStatus)) }}
                        </span>
                    @endif
                </td>
                <td class="text-center" onclick="event.stopPropagation();">
                    <a href="{{ route('tasks.show', $task) }}" class="btn btn-sm btn-info mb-1">
                        <i class="fas fa-eye"></i>
                    </a>
                    
                    @if(Auth::user()->isAdmin())
                        <a href="{{ route('tasks.edit', $task) }}" class="btn btn-sm btn-primary mb-1">
                            <i class="fas fa-edit"></i>
                        </a>
                        
                        <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-sm btn-danger mb-1" onclick="confirmDelete(event, this.form)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center">Không có công việc nào</td>
            </tr>
        @endforelse
    </tbody>
</table> 