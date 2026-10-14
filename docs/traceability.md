# Traceability matrix

Links each requirement to the code that implements it and the test that proves it.

| ID | Requirement (input) | Implementation (output) | Verification |
| --- | --- | --- | --- |
| REQ-001 | Admin shall be able to create a case | `CaseController@store` + `cases/create.blade.php` form; API: `Api\CaseController@store`; `StoreCaseRequest` | Feature test: `CaseManagementTest::test_admin_can_create_a_case` (web) and `test_admin_can_create_a_case_through_the_api` |
| REQ-002 | Case Number shall be unique | Unique DB index (`cases_case_number_unique` migration) + `Rule::unique` in `StoreCaseRequest` / `UpdateCaseRequest` (ignores the edited case) | Feature test: `CaseManagementTest::test_duplicate_case_number_fails_with_422` |
| REQ-003 | Only Admin shall delete cases | `MedicalCasePolicy@delete` (admin only), enforced by `Gate::authorize('delete', ...)` in `CaseController@destroy` and `Api\CaseController@destroy` | Feature test: `CaseManagementTest::test_viewer_cannot_delete_a_case` (403 on web and API) |
