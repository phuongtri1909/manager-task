@extends('layouts.auth')

@section('title', 'Đăng nhập')

@section('content')
<div class="login-box">
    <div class="login-logo">
        <a href="{{ url('/') }}"><b>Quản lý</b> Nhiệm vụ</a>
    </div>
    
    <div class="card">
        <div class="card-body login-card-body">
            <p class="login-box-msg">Đăng nhập để bắt đầu phiên làm việc</p>

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('login') }}" method="post">
                @csrf
                
                <div class="input-group mb-3">
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                           placeholder="Email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                
                <div class="input-group mb-3">
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" 
                           placeholder="Mật khẩu" required autocomplete="current-password">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                
                <div class="row">
                    <div class="col-8">
                        <div class="icheck-primary">
                            <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label for="remember">
                                Ghi nhớ đăng nhập
                            </label>
                        </div>
                    </div>
                    <div class="col-4">
                        <button type="submit" class="btn btn-primary btn-block">Đăng nhập</button>
                    </div>
                </div>
            </form>

            @if (Route::has('password.request'))
                <p class="mb-1">
                    <a href="{{ route('password.request') }}">Quên mật khẩu?</a>
                </p>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .login-page {
        background: linear-gradient(135deg, #2980b9 0%, #2c3e50 100%);
        height: 100vh;
    }
    .login-box {
        margin-top: 10vh;
        width: 360px;
    }
    .login-logo a {
        color: #fff;
        text-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
    }
    .login-card-body {
        border-radius: 5px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    .login-box-msg {
        font-weight: 500;
    }
    .btn-primary {
        background-color: #3c8dbc;
        border-color: #367fa9;
    }
    .btn-primary:hover {
        background-color: #367fa9;
    }
    .input-group-text {
        color: #3c8dbc;
    }
</style>
@endpush