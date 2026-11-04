<?php
/* Login page using custom auth-login layout */
?>
@extends('layouts.auth-login')

@section('content')
    <h2 class="login-title">Login</h2>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-0">
        @csrf
        <!-- Email -->
        <input type="email" name="email" id="email" class="login-input" placeholder="Email" value="{{ old('email') }}" required autofocus autocomplete="username">
        <div class="error-msg"><x-input-error :messages="$errors->get('email')" /></div>

        <!-- Password -->
        <input type="password" name="password" id="password" class="login-input" placeholder="Password" required autocomplete="current-password">
        <div class="error-msg"><x-input-error :messages="$errors->get('password')" /></div>

        <!-- Remember Me & Forgot Password -->
        <div class="login-options">
            <label class="flex items-center">
                <input type="checkbox" name="remember" id="remember_me" class="login-checkbox" />
                <span class="ml-2 text-[#475569]">{{ __('Ingat saya') }}</span>
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}">{{ __('Lupa password?') }}</a>
            @endif
        </div>

        <!-- Submit -->
        <button type="submit" class="login-btn">{{ __('Masuk') }}</button>

        @if (Route::has('register'))
            <div class="register-link">
                Belum punya akun? <a href="{{ route('register') }}">Daftar</a>
            </div>
        @endif
    </form>
@endsection
