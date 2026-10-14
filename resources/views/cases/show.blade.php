@extends('layouts.app')

@section('title', $case->case_number)

@section('content')
    <div class="page-head">
        <h1>{{ $case->case_number }}</h1>
        <div style="display:flex; gap:0.5rem;">
            <a class="btn btn-light" href="{{ route('cases.index') }}">Back to list</a>
            @can('update', $case)
                <a class="btn" href="{{ route('cases.edit', $case) }}">Edit</a>
            @endcan
            @can('delete', $case)
                <form method="POST" action="{{ route('cases.destroy', $case) }}"
                      onsubmit="return confirm('Delete this case? This cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            @endcan
        </div>
    </div>

    <div class="card">
        <dl class="details">
            <dt>ID</dt>
            <dd>{{ $case->id }}</dd>
            <dt>Case Number</dt>
            <dd>{{ $case->case_number }}</dd>
            <dt>Patient Reference</dt>
            <dd>{{ $case->patient_reference }}</dd>
            <dt>Surgeon Name</dt>
            <dd>{{ $case->surgeon_name }}</dd>
            <dt>Implant Type</dt>
            <dd>{{ $case->implant_type }}</dd>
            <dt>Status</dt>
            <dd><span class="badge badge-{{ \Illuminate\Support\Str::slug($case->status->value) }}">{{ $case->status->value }}</span></dd>
            <dt>Priority</dt>
            <dd><span class="badge badge-{{ \Illuminate\Support\Str::slug($case->priority->value) }}">{{ $case->priority->value }}</span></dd>
            <dt>Surgery Date</dt>
            <dd>{{ $case->surgery_date?->format('Y-m-d') ?? 'Not scheduled' }}</dd>
            <dt>Created By</dt>
            <dd>{{ $case->creator?->name ?? '—' }}</dd>
            <dt>Created At</dt>
            <dd>{{ $case->created_at?->format('Y-m-d H:i') ?? '—' }}</dd>
            <dt>Updated At</dt>
            <dd>{{ $case->updated_at?->format('Y-m-d H:i') ?? '—' }}</dd>
        </dl>
    </div>
@endsection
