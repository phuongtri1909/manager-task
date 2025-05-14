<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - Hệ thống Quản lý Nhiệm vụ</title>

    <!-- Google Font: Nunito -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- icheck bootstrap -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/icheck-bootstrap/3.0.1/icheck-bootstrap.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/css/adminlte.min.css">
    <!-- Custom styles -->
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
    
    @yield('styles')
</head>
<body class="hold-transition login-page">
    
    @yield('content')

    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/js/adminlte.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Add entrance animation to the login box
            $('.login-card-body').addClass('fade-in');
            
            // Adding hover effect to input fields
            $('.form-control').on('focus', function() {
                $(this).parent().addClass('is-focused');
            }).on('blur', function() {
                if ($(this).val() === '') {
                    $(this).parent().removeClass('is-focused');
                }
            });
            
            // Check if inputs have values on page load
            $('.form-control').each(function() {
                if ($(this).val() !== '') {
                    $(this).parent().addClass('is-focused');
                }
            });
        });
    </script>
    
    @yield('scripts')
</body>
</html> 