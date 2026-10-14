<?php

namespace App\Http\Controllers;

use App\Enums\CasePriority;
use App\Enums\CaseStatus;
use App\Http\Requests\StoreCaseRequest;
use App\Http\Requests\UpdateCaseRequest;
use App\Models\MedicalCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CaseController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', MedicalCase::class);

        $filters = MedicalCase::filtersFrom($request->query());

        $cases = MedicalCase::query()
            ->with('creator:id,name')
            ->filter($filters)
            ->paginate(10)
            ->withQueryString();

        return view('cases.index', [
            'cases' => $cases,
            'filters' => $filters,
            'statuses' => CaseStatus::cases(),
            'priorities' => CasePriority::cases(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', MedicalCase::class);

        $case = new MedicalCase([
            'case_number' => MedicalCase::suggestCaseNumber(),
            'status' => CaseStatus::Draft,
            'priority' => CasePriority::Medium,
        ]);

        return view('cases.create', $this->formData($case));
    }

    public function store(StoreCaseRequest $request): RedirectResponse
    {
        // created_by comes from the logged-in user, never from the request.
        $case = $request->user()->cases()->create($request->validated());

        return redirect()->route('cases.show', $case)->with('status', 'Case created.');
    }

    public function show(MedicalCase $case): View
    {
        Gate::authorize('view', $case);

        return view('cases.show', ['case' => $case->load('creator')]);
    }

    public function edit(MedicalCase $case): View
    {
        Gate::authorize('update', $case);

        return view('cases.edit', $this->formData($case));
    }

    public function update(UpdateCaseRequest $request, MedicalCase $case): RedirectResponse
    {
        $case->update($request->validated());

        return redirect()->route('cases.show', $case)->with('status', 'Case updated.');
    }

    public function destroy(MedicalCase $case): RedirectResponse
    {
        // Server-side check: hiding the button in the view is not enough.
        Gate::authorize('delete', $case);

        $case->delete();

        return redirect()->route('cases.index')->with('status', 'Case deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(MedicalCase $case): array
    {
        return [
            'case' => $case,
            'statuses' => CaseStatus::cases(),
            'priorities' => CasePriority::cases(),
        ];
    }
}
