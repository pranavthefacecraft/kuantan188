@extends('layouts.auth')

@section('title', 'Reset Password')
@section('subtitle', 'Enter your new password below')
@section('icon', 'vpn_key')

@section('content')
<form method="POST" action="{{ route('password.update') }}">
    @csrf

    <input type="hidden" name="token" value="{{ $token }}">

    <div class="form-group">
        <label for="email" class="form-label">Email Address</label>
        <input id="email" 
               type="email" 
               class="form-input @error('email') is-invalid @enderror" 
               name="email" 
               value="{{ $email ?? old('email') }}" 
               required 
               autocomplete="email" 
               autofocus
               readonly
               style="background-color: var(--surface-variant);">

        @error('email')
            <div class="invalid-feedback">
                <span class="material-icons" style="font-size: 14px;">error</span>
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group">
        <label for="password" class="form-label">New Password</label>
        <input id="password" 
               type="password" 
               class="form-input @error('password') is-invalid @enderror" 
               name="password" 
               required 
               autocomplete="new-password"
               placeholder="Enter your new password">

        @error('password')
            <div class="invalid-feedback">
                <span class="material-icons" style="font-size: 14px;">error</span>
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group">
        <label for="password-confirm" class="form-label">Confirm New Password</label>
        <input id="password-confirm" 
               type="password" 
               class="form-input" 
               name="password_confirmation" 
               required 
               autocomplete="new-password"
               placeholder="Confirm your new password">
    </div>

    <button type="submit" class="btn btn-primary">
        <span class="material-icons" style="font-size: 18px;">vpn_key</span>
        Reset Password
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
