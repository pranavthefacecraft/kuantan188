@extends('layouts.auth')

@section('title', 'Create Account')
@section('subtitle', 'Join Kuantan188 admin dashboard')
@section('icon', 'person_add')

@section('content')
<form method="POST" action="{{ route('register') }}">
    @csrf

    <div class="form-group">
        <label for="name" class="form-label">Full Name</label>
        <input id="name" 
               type="text" 
               class="form-input @error('name') is-invalid @enderror" 
               name="name" 
               value="{{ old('name') }}" 
               required 
               autocomplete="name" 
               autofocus
               placeholder="Enter your full name">

        @error('name')
            <div class="invalid-feedback">
                <span class="material-icons" style="font-size: 14px;">error</span>
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group">
        <label for="email" class="form-label">Email Address</label>
        <input id="email" 
               type="email" 
               class="form-input @error('email') is-invalid @enderror" 
               name="email" 
               value="{{ old('email') }}" 
               required 
               autocomplete="email"
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
               autocomplete="new-password"
               placeholder="Create a strong password">

        @error('password')
            <div class="invalid-feedback">
                <span class="material-icons" style="font-size: 14px;">error</span>
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group">
        <label for="password-confirm" class="form-label">Confirm Password</label>
        <input id="password-confirm" 
               type="password" 
               class="form-input" 
               name="password_confirmation" 
               required 
               autocomplete="new-password"
               placeholder="Confirm your password">
    </div>

    <div class="checkbox-group">
        <input class="checkbox-input" 
               type="checkbox" 
               name="terms" 
               id="terms" 
               required>
        <label class="checkbox-label" for="terms">
            I agree to the <a href="#" style="color: var(--primary);">Terms of Service</a> and <a href="#" style="color: var(--primary);">Privacy Policy</a>
        </label>
    </div>

    <button type="submit" class="btn btn-primary">
        <span class="material-icons" style="font-size: 18px;">person_add</span>
        Create Account
    </button>
</form>
@endsection

@section('footer')
    <p class="auth-footer-text">
        Already have an account? 
        <a href="{{ route('login') }}" class="auth-footer-link">Sign in here</a>
    </p>
@endsection
