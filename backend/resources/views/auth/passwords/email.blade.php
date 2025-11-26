@extends('layouts.auth')

@section('title', 'Reset Password')
@section('subtitle', 'Enter your email to receive a password reset link')
@section('icon', 'lock_reset')

@section('content')
<form method="POST" action="{{ route('password.email') }}">
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
               placeholder="Enter your email address">

        @error('email')
            <div class="invalid-feedback">
                <span class="material-icons" style="font-size: 14px;">error</span>
                {{ $message }}
            </div>
        @enderror
    </div>

    <button type="submit" class="btn btn-primary">
        <span class="material-icons" style="font-size: 18px;">email</span>
        Send Reset Link
    </button>

    <a class="forgot-password-link" href="{{ route('login') }}">
        Back to login
    </a>
</form>
@endsection

@section('footer')
    <p class="auth-footer-text">
        Remember your password? 
        <a href="{{ route('login') }}" class="auth-footer-link">Sign in here</a>
    </p>
@endsection
