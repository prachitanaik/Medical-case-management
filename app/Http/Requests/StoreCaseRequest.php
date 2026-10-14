<?php

namespace App\Http\Requests;

use App\Enums\CasePriority;
use App\Enums\CaseStatus;
use App\Models\MedicalCase;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

/**
 * Validates creating a case. Used by both the web form and the REST API.
 */
class StoreCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', MedicalCase::class) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'case_number' => ['required', 'string', 'max:50', $this->caseNumberRule()],
            'patient_reference' => ['required', 'string', 'max:100'],
            'surgeon_name' => ['required', 'string', 'max:150'],
            'implant_type' => ['required', 'string', 'max:150'],
            'status' => ['required', Rule::enum(CaseStatus::class)],
            'priority' => ['required', Rule::enum(CasePriority::class)],
            'surgery_date' => ['nullable', 'date_format:Y-m-d'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'case_number.unique' => 'This Case Number is already in use.',
            'surgery_date.date_format' => 'Surgery Date must be a valid date (YYYY-MM-DD).',
        ];
    }

    protected function caseNumberRule(): Unique
    {
        return Rule::unique('cases', 'case_number');
    }
}
