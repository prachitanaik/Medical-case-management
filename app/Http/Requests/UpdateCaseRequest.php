<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

/**
 * Validates editing a case. Same rules as creating, except the unique
 * Case Number rule ignores the case being edited.
 */
class UpdateCaseRequest extends StoreCaseRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('case')) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $rules = parent::rules();

        if ($this->isMethod('PATCH')) {
            return array_map(function (array $fieldRules) {
                return array_merge(['sometimes'], $fieldRules);
            }, $rules);
        }

        return $rules;
    }

    protected function caseNumberRule(): Unique
    {
        return Rule::unique('cases', 'case_number')->ignore($this->route('case'));
    }
}
