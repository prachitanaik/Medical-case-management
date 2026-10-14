@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <h1>Dashboard</h1>

    <div class="stats">
        @foreach ($stats as $label => $value)
            <div class="stat">
                <div class="label">{{ $label }}</div>
                <div class="value">{{ $value }}</div>
            </div>
        @endforeach
    </div>
@endsection
