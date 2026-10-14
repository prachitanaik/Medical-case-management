@extends('layouts.app')

@section('title', 'Edit '.$case->case_number)

@section('content')
    <h1>Edit {{ $case->case_number }}</h1>

    @include('cases._form', [
        'action' => route('cases.update', $case),
        'method' => 'PUT',
        'submit' => 'Save changes',
    ])
@endsection
