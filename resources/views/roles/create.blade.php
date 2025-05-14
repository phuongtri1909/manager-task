@extends('layouts.partials.sidebar')

@section('title', 'Thêm vai trò mới')

@section('main-content')
    <div class="category-form-container">
        <!-- Breadcrumb -->
        <div class="content-breadcrumb">
            <ol class="breadcrumb-list">
                <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Quản lý vai trò</a></li>
                <li class="breadcrumb-item current">Thêm mới</li>
            </ol>
        </div>

        <div class="form-card">
            <div class="form-header">
                <div class="form-title">
                    <i class="fas fa-plus-circle icon-title"></i>
                    <h5>Thêm vai trò mới</h5>
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

                <form action="{{ route('roles.store') }}" method="POST">
                    @csrf
                    
                    <div class="form-group">
                        <label for="name" class="form-label-custom">
                            Tên vai trò <span class="required-mark">*</span>
                        </label>
                        <input type="text" class="custom-input @error('name') input-error @enderror" id="name" name="name" value="{{ old('name') }}" required>
                        <div class="error-message">
                            @error('name')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="level" class="form-label-custom">
                            Cấp độ <span class="required-mark">*</span>
                        </label>
                        <input type="number" class="custom-input @error('level') input-error @enderror" id="level" name="level" value="{{ old('level', 0) }}" min="0" required>
                        <div class="error-message">
                            @error('level')
                                {{ $message }}
                            @enderror
                        </div>
                        <div class="form-hint">Số càng lớn thì cấp độ càng cao. Ví dụ: Admin = 100, Nhân viên = 10.</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description" class="form-label-custom">
                            Mô tả
                        </label>
                        <textarea class="custom-input @error('description') input-error @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                        <div class="error-message">
                            @error('description')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <a href="{{ route('roles.index') }}" class="back-button">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                        <button type="submit" class="save-button">
                            <i class="fas fa-save"></i> Lưu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Auto-focus on first input
            $('#name').focus();
        });
    </script>
@stop 