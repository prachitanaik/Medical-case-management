@extends('layouts.app')

@section('title', 'Cases')

@php
    // Builds a sortable column header link that keeps the current search/filters.
    $sortLink = function (string $column, string $label) use ($filters) {
        $current = ($filters['sort'] ?? null) === $column;
        $direction = ($filters['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        $next = $current && $direction === 'asc' ? 'desc' : 'asc';
        $arrow = $current ? ($direction === 'asc' ? ' ▲' : ' ▼') : '';
        $url = route('cases.index', array_merge(request()->except('page'), ['sort' => $column, 'direction' => $next]));

        return new \Illuminate\Support\HtmlString('<a href="'.e($url).'">'.e($label).$arrow.'</a>');
    };
@endphp

@section('content')
    <div class="page-head">
        <h1>Cases</h1>
        @can('create', \App\Models\MedicalCase::class)
            <a class="btn" href="{{ route('cases.create') }}">New case</a>
        @endcan
    </div>

    <form method="GET" action="{{ route('cases.index') }}" class="card filters">
        <div class="field">
            <label for="search">Search</label>
            <input id="search" type="search" name="search" value="{{ $filters['search'] ?? '' }}"
                   placeholder="Case number or surgeon">
        </div>
        <div class="field">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="">All statuses</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->value }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label for="priority">Priority</label>
            <select id="priority" name="priority">
                <option value="">All priorities</option>
                @foreach ($priorities as $priority)
                    <option value="{{ $priority->value }}" @selected(($filters['priority'] ?? '') === $priority->value)>{{ $priority->value }}</option>
                @endforeach
            </select>
        </div>
        @if (! empty($filters['sort']))
            <input type="hidden" name="sort" value="{{ $filters['sort'] }}">
            <input type="hidden" name="direction" value="{{ $filters['direction'] ?? 'asc' }}">
        @endif
        <div>
            <button type="submit" class="btn">Filter</button>
            <a class="btn btn-light" href="{{ route('cases.index') }}">Reset</a>
        </div>
    </form>

    <div class="card table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ $sortLink('case_number', 'Case No.') }}</th>
                    <th>{{ $sortLink('surgeon_name', 'Surgeon') }}</th>
                    <th>Implant</th>
                    <th>{{ $sortLink('status', 'Status') }}</th>
                    <th>{{ $sortLink('priority', 'Priority') }}</th>
                    <th>{{ $sortLink('surgery_date', 'Surgery date') }}</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($cases as $case)
                    <tr>
                        <td><a href="{{ route('cases.show', $case) }}">{{ $case->case_number }}</a></td>
                        <td>{{ $case->surgeon_name }}</td>
                        <td>{{ $case->implant_type }}</td>
                        <td><span class="badge badge-{{ \Illuminate\Support\Str::slug($case->status->value) }}">{{ $case->status->value }}</span></td>
                        <td><span class="badge badge-{{ \Illuminate\Support\Str::slug($case->priority->value) }}">{{ $case->priority->value }}</span></td>
                        <td>{{ $case->surgery_date?->format('Y-m-d') ?? '—' }}</td>
                        <td class="actions">
                            @can('view', $case)
                                <a class="btn btn-light btn-small" href="{{ route('cases.show', $case) }}">View</a>
                            @endcan
                            @can('update', $case)
                                <a class="btn btn-light btn-small" href="{{ route('cases.edit', $case) }}">Edit</a>
                            @endcan
                            @can('delete', $case)
                                <form method="POST" action="{{ route('cases.destroy', $case) }}"
                                      onsubmit="return confirm('Delete this case? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-small">Delete</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty">No cases found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $cases->links('pagination.simple') }}
@endsection
