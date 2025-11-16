@extends('layouts.guest')

@section('title', 'Login')

@section('content')
<div class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            {{-- Header --}}
            <div class="auth-header">
                <img src="{{ asset('assets/images/logonekas.png') }}" alt="Logo NEKAS" class="auth-logo">
                <h1 class="auth-title">SIAG NEKAS</h1>
                <p class="auth-subtitle">Sistem Informasi Absensi Guru<br>SMK Negeri Kasomalang</p>
            </div>

            {{-- Body --}}
            <div class="auth-body">
                {{-- Flash Messages --}}
                @if (session('success'))
                    <div class="auth-alert auth-alert-success">
                        <i class="bi bi-check-circle me-2"></i>
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="auth-alert auth-alert-error">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        {{ session('error') }}
                    </div>
                @endif

                {{-- Login Form --}}
                <form action="{{ route('login.post') }}" method="POST" class="auth-form">
                    @csrf

                    {{-- Username Field --}}
                    <div class="auth-form-group">
                        <i class="bi bi-person auth-form-icon"></i>
                        <input type="text" 
                               name="username" 
                               class="auth-form-input @error('username') is-invalid @enderror" 
                               placeholder="Username" 
                               value="{{ old('username') }}" 
                               required 
                               autofocus>
                        @error('username')
                            <span class="auth-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Password Field --}}
                    <div class="auth-form-group">
                        <i class="bi bi-lock auth-form-icon"></i>
                        <input type="password" 
                               name="password" 
                               class="auth-form-input @error('password') is-invalid @enderror" 
                               placeholder="Password" 
                               required>
                        @error('password')
                            <span class="auth-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Remember Me --}}
                    <div class="auth-remember">
                        <input type="checkbox" 
                               name="remember" 
                               id="remember" 
                               class="auth-remember-input" 
                               {{ old('remember') ? 'checked' : '' }}>
                        <label for="remember" class="auth-remember-label">Ingat saya</label>
                    </div>

                    {{-- Submit Button --}}
                    <button type="submit" class="auth-submit">
                        <i class="bi bi-box-arrow-in-right me-2"></i>
                        Login
                    </button>
                </form>
            </div>

            {{-- Footer --}}
            <div class="auth-footer">
                <p class="auth-footer-text">
                    © {{ date('Y') }} SMK Negeri Kasomalang. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
