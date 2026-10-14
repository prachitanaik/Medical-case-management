@extends('layouts.app')

@section('title', 'New case')

@section('content')
    <h1>New case</h1>

    @include('cases._form', [
        'action' => route('cases.store'),
        'method' => 'POST',
        'submit' => 'Create case',
    ])
@endsection
