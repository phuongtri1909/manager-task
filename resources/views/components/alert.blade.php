@php
    // Lấy các thông báo từ session hoặc từ tham số truyền vào
    $success = $success ?? session('success') ?? null;
    $error = $error ?? session('error') ?? null;
    $info = $info ?? session('info') ?? null;
    $warning = $warning ?? session('warning') ?? null;
    
    // Thời gian tự động đóng alert (mặc định: 5000ms = 5 giây)
    $autoClose = $autoClose ?? true;
    $autoCloseTime = $autoCloseTime ?? 5000;
    
    // Kiểm tra xem alert có thể đóng được không
    $dismissible = $dismissible ?? true;
    
    // Kiểu alert: 'toast' hoặc 'modal' (mặc định: 'alert')
    $alertType = $alertType ?? 'alert';
@endphp

@if($alertType == 'alert')
    {{-- Bootstrap Alerts --}}
    @if($success)
        <div class="alert alert-success {{ $dismissible ? 'alert-dismissible' : '' }} fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> {!! $success !!}
            @if($dismissible)
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            @endif
        </div>
    @endif

    @if($error)
        <div class="alert alert-danger {{ $dismissible ? 'alert-dismissible' : '' }} fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> {!! $error !!}
            @if($dismissible)
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            @endif
        </div>
    @endif

    @if($info)
        <div class="alert alert-info {{ $dismissible ? 'alert-dismissible' : '' }} fade show" role="alert">
            <i class="fas fa-info-circle me-2"></i> {!! $info !!}
            @if($dismissible)
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            @endif
        </div>
    @endif

    @if($warning)
        <div class="alert alert-warning {{ $dismissible ? 'alert-dismissible' : '' }} fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> {!! $warning !!}
            @if($dismissible)
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            @endif
        </div>
    @endif

    @if($autoClose && ($success || $error || $info || $warning))
    <script>
        // Tự động đóng các alert sau một khoảng thời gian
        setTimeout(function() {
            document.querySelectorAll('.alert').forEach(function(alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, {{ $autoCloseTime }});
    </script>
    @endif
@elseif($alertType == 'toast' || $alertType == 'sweetalert')
    {{-- SweetAlert Toasts --}}
    @if($success)
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Thành công',
            text: "{!! $success !!}",
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: {{ $autoClose ? $autoCloseTime : 10000 }},
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });
    </script>
    @endif
    
    @if($error)
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Lỗi',
            text: "{!! $error !!}",
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: {{ $autoClose ? $autoCloseTime : 10000 }},
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });
    </script>
    @endif
    
    @if($info)
    <script>
        Swal.fire({
            icon: 'info',
            title: 'Thông tin',
            text: "{!! $info !!}",
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: {{ $autoClose ? $autoCloseTime : 10000 }},
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });
    </script>
    @endif
    
    @if($warning)
    <script>
        Swal.fire({
            icon: 'warning',
            title: 'Cảnh báo',
            text: "{!! $warning !!}",
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: {{ $autoClose ? $autoCloseTime : 10000 }},
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });
    </script>
    @endif
@elseif($alertType == 'modal')
    {{-- SweetAlert Modal Alerts --}}
    @if($success)
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Thành công',
            text: "{!! $success !!}",
            timer: {{ $autoClose ? $autoCloseTime : null }},
            showConfirmButton: {{ $autoClose ? 'false' : 'true' }}
        });
    </script>
    @endif
    
    @if($error)
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Lỗi',
            text: "{!! $error !!}",
            timer: {{ $autoClose ? $autoCloseTime : null }},
            showConfirmButton: {{ $autoClose ? 'false' : 'true' }}
        });
    </script>
    @endif
    
    @if($info)
    <script>
        Swal.fire({
            icon: 'info',
            title: 'Thông tin',
            text: "{!! $info !!}",
            timer: {{ $autoClose ? $autoCloseTime : null }},
            showConfirmButton: {{ $autoClose ? 'false' : 'true' }}
        });
    </script>
    @endif
    
    @if($warning)
    <script>
        Swal.fire({
            icon: 'warning',
            title: 'Cảnh báo',
            text: "{!! $warning !!}",
            timer: {{ $autoClose ? $autoCloseTime : null }},
            showConfirmButton: {{ $autoClose ? 'false' : 'true' }}
        });
    </script>
    @endif
@endif

{{-- Hướng dẫn sử dụng (sẽ không hiển thị khi component được gọi) --}}
{{-- 
    Hiển thị thông báo từ session:
    @include('components.alert')
    
    Sử dụng SweetAlert dạng toast:
    @include('components.alert', ['alertType' => 'toast'])
    
    Sử dụng SweetAlert dạng modal:
    @include('components.alert', ['alertType' => 'modal'])
    
    Truyền thông báo trực tiếp:
    @include('components.alert', ['success' => 'Thao tác thành công!'])
    
    Tùy chỉnh thời gian tự động đóng:
    @include('components.alert', ['autoCloseTime' => 3000])
    
    Tắt tính năng tự động đóng:
    @include('components.alert', ['autoClose' => false])
    
    Tắt nút đóng:
    @include('components.alert', ['dismissible' => false])
--}}