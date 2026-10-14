<?php

namespace App\Models;

use App\Enums\CasePriority;
use App\Enums\CaseStatus;
use Database\Factories\MedicalCaseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A medical implant case. The class is not called "Case" because that is a
 * reserved word in PHP; it maps to the "cases" table.
 */
class MedicalCase extends Model
{
    /** @use HasFactory<MedicalCaseFactory> */
    use HasFactory;

    protected $table = 'cases';

    /** Columns the list may be sorted by (whitelist, never trust request input). */
    public const SORTABLE = ['case_number', 'surgeon_name', 'status', 'priority', 'surgery_date', 'created_at'];

    // created_by is intentionally not fillable: it is set from the logged-in user.
    protected $fillable = [
        'case_number',
        'patient_reference',
        'surgeon_name',
        'implant_type',
        'status',
        'priority',
        'surgery_date',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'status' => CaseStatus::class,
            'priority' => CasePriority::class,
            'surgery_date' => 'date',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Pick the supported list filters out of request input, keeping string values
     * only (so a query like ?search[]=x cannot cause an error).
     *
     * @param  array<string, mixed>  $input
     * @return array<string, string>
     */
    public static function filtersFrom(array $input): array
    {
        return array_filter(
            array_intersect_key($input, array_flip(['search', 'status', 'priority', 'sort', 'direction'])),
            'is_string'
        );
    }

    /**
     * Apply the list filters: search, status, priority and sorting.
     *
     * @param  array{search?: ?string, status?: ?string, priority?: ?string, sort?: ?string, direction?: ?string}  $filters
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        $search = trim((string) ($filters['search'] ?? ''));

        $query
            ->when($search !== '', function (Builder $q) use ($search) {
                $q->where(function (Builder $q) use ($search) {
                    $q->where('case_number', 'like', "%{$search}%")
                        ->orWhere('surgeon_name', 'like', "%{$search}%");
                });
            })
            ->when(
                CaseStatus::tryFrom((string) ($filters['status'] ?? '')),
                fn (Builder $q, CaseStatus $status) => $q->where('status', $status->value)
            )
            ->when(
                CasePriority::tryFrom((string) ($filters['priority'] ?? '')),
                fn (Builder $q, CasePriority $priority) => $q->where('priority', $priority->value)
            );

        $sort = $filters['sort'] ?? null;

        if (in_array($sort, self::SORTABLE, true)) {
            $direction = strtolower((string) ($filters['direction'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';
            $query->orderBy($sort, $direction)->orderBy('id');
        } else {
            $query->latest('id');
        }

        return $query;
    }

    /** Suggest the next case number for the create form, e.g. CASE-0021. */
    public static function suggestCaseNumber(): string
    {
        $next = ((int) static::max('id')) + 1;
        do {
            $candidate = 'CASE-'.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
            $next++;
        } while (static::where('case_number', $candidate)->exists());

        return $candidate;
    }
}
