@extends('layouts.app')

@section('title', 'Log in')

@section('content')
    <div class="card login-card">
        <h1>Log in</h1>

        <form method="POST" action="{{ url('/login') }}" novalidate>
            @csrf

            <div class="field">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}"
                       class="{{ $errors->has('email') ? 'invalid' : '' }}" required autofocus autocomplete="username">
                @error('email')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input id="password" type="password" name="password"
                       class="{{ $errors->has('password') ? 'invalid' : '' }}" required autocomplete="current-password">
                @error('password')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <label class="remember">
                <input type="checkbox" name="remember" value="1"> Remember me
            </label>

            <button type="submit" class="btn">Log in</button>
        </form>
    </div>
@endsection
