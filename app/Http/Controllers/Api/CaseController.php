<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCaseRequest;
use App\Http\Requests\UpdateCaseRequest;
use App\Http\Resources\CaseResource;
use App\Models\MedicalCase;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CaseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', MedicalCase::class);

        $perPage = min(max($request->integer('per_page', 10), 1), 100);

        $cases = MedicalCase::query()
            ->with('creator:id,name')
            ->filter($request->only(['search', 'status', 'priority', 'sort', 'direction']))
            ->paginate($perPage)
            ->withQueryString();

        return ApiResponse::success(
            $cases->getCollection()->map(fn (MedicalCase $case) => $this->present($case))->all(),
            'Cases retrieved.',
            200,
            [
                'current_page' => $cases->currentPage(),
                'last_page' => $cases->lastPage(),
                'per_page' => $cases->perPage(),
                'total' => $cases->total(),
            ],
        );
    }

    public function store(StoreCaseRequest $request): JsonResponse
    {
        $case = $request->user()->cases()->create($request->validated());

        return ApiResponse::success($this->present($case->load('creator')), 'Case created.', 201);
    }

    public function show(MedicalCase $case): JsonResponse
    {
        Gate::authorize('view', $case);

        return ApiResponse::success($this->present($case->load('creator')), 'Case retrieved.');
    }

    public function update(UpdateCaseRequest $request, MedicalCase $case): JsonResponse
    {
        $case->update($request->validated());

        return ApiResponse::success($this->present($case->load('creator')), 'Case updated.');
    }

    public function destroy(MedicalCase $case): JsonResponse
    {
        Gate::authorize('delete', $case);

        $case->delete();

        return ApiResponse::success(null, 'Case deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function present(MedicalCase $case): array
    {
        return (new CaseResource($case))->resolve();
    }
}
