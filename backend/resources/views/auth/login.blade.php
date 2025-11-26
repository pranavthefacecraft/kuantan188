@extends('layouts.auth')

@section('title', 'Welcome Back')
@section('subtitle', 'Sign in to your Kuantan188 admin account')
@section('icon', 'login')

@section('content')
<form method="POST" action="{{ route('login') }}">
    @csrf

    <div class="form-group">
        <label for="email" class="form-label">Email Address</label>
        <input id="email" 
               type="email" 
               class="form-input @error('email') is-invalid @enderror" 
               name="email" 
               value="{{ old('email') }}" 
               required 
               autocomplete="email" 
               autofocus
               placeholder="Enter your email">

        @error('email')
            <div class="invalid-feedback">
                <span class="material-icons" style="font-size: 14px;">error</span>
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group">
        <label for="password" class="form-label">Password</label>
        <input id="password" 
               type="password" 
               class="form-input @error('password') is-invalid @enderror" 
               name="password" 
               required 
               autocomplete="current-password"
               placeholder="Enter your password">

        @error('password')
            <div class="invalid-feedback">
                <span class="material-icons" style="font-size: 14px;">error</span>
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="checkbox-group">
        <input class="checkbox-input" 
               type="checkbox" 
               name="remember" 
               id="remember" 
               {{ old('remember') ? 'checked' : '' }}>
        <label class="checkbox-label" for="remember">
            Remember me for 30 days
        </label>
    </div>

    <button type="submit" class="btn btn-primary">
        <span class="material-icons" style="font-size: 18px;">login</span>
        Sign In
    </button>

    @if (Route::has('password.request'))
        <a class="forgot-password-link" href="{{ route('password.request') }}">
            Forgot your password?
        </a>
    @endif
</form>
@endsection

@section('footer')
    <p class="auth-footer-text">
        Don't have an account? 
        <a href="{{ route('register') }}" class="auth-footer-link">Create one here</a>
    </p>
@endsection
