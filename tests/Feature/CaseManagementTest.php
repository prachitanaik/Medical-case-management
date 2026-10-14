<?php

namespace Tests\Feature;

use App\Enums\CaseStatus;
use App\Models\MedicalCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CaseManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'case_number' => 'CASE-0100',
            'patient_reference' => 'PT-000100',
            'surgeon_name' => 'Dr. Test Surgeon',
            'implant_type' => 'Hip Stem',
            'status' => 'Draft',
            'priority' => 'Medium',
            'surgery_date' => '2026-12-01',
        ], $overrides);
    }

    // ---------------------------------------------------------------- create

    /** Required test 4: admin can create a case. */
    public function test_admin_can_create_a_case(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/cases', $this->payload());

        $case = MedicalCase::where('case_number', 'CASE-0100')->firstOrFail();
        $response->assertRedirect(route('cases.show', $case));

        $this->assertSame($admin->id, $case->created_by);
        $this->assertSame(CaseStatus::Draft, $case->status);
    }

    public function test_admin_can_create_a_case_through_the_api(): void
    {
        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin);

        $this->postJson('/api/cases', $this->payload())
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.case_number', 'CASE-0100')
            ->assertJsonPath('data.created_by', $admin->id);

        $this->assertDatabaseHas('cases', ['case_number' => 'CASE-0100', 'created_by' => $admin->id]);
    }

    public function test_created_by_cannot_be_set_from_the_request(): void
    {
        $admin = User::factory()->admin()->create();
        $other = User::factory()->admin()->create();
        Sanctum::actingAs($admin);

        $this->postJson('/api/cases', $this->payload(['created_by' => $other->id]))->assertCreated();

        $this->assertDatabaseHas('cases', ['case_number' => 'CASE-0100', 'created_by' => $admin->id]);
    }

    public function test_viewer_cannot_create_a_case(): void
    {
        $viewer = User::factory()->viewer()->create();

        $this->actingAs($viewer)->get('/cases/create')->assertForbidden();
        $this->actingAs($viewer)->post('/cases', $this->payload())->assertForbidden();

        Sanctum::actingAs($viewer);
        $this->postJson('/api/cases', $this->payload())
            ->assertForbidden()
            ->assertJsonPath('success', false);

        $this->assertDatabaseCount('cases', 0);
    }

    // ------------------------------------------------------------ validation

    /** Required test 5: a duplicate Case Number fails with 422. */
    public function test_duplicate_case_number_fails_with_422(): void
    {
        MedicalCase::factory()->create(['case_number' => 'CASE-0100']);
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->postJson('/api/cases', $this->payload(['case_number' => 'CASE-0100']))
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['case_number']);

        $this->assertDatabaseCount('cases', 1);
    }

    public function test_duplicate_case_number_shows_an_error_on_the_web_form(): void
    {
        MedicalCase::factory()->create(['case_number' => 'CASE-0100']);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->from('/cases/create')
            ->post('/cases', $this->payload(['case_number' => 'CASE-0100']))
            ->assertRedirect('/cases/create')
            ->assertSessionHasErrors('case_number');
    }

    public function test_required_fields_and_allowed_values_are_validated(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->postJson('/api/cases', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'case_number', 'patient_reference', 'surgeon_name', 'implant_type', 'status', 'priority',
            ]);

        $this->postJson('/api/cases', $this->payload([
            'status' => 'Unknown',
            'priority' => 'Urgent',
            'surgery_date' => 'not-a-date',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status', 'priority', 'surgery_date']);
    }

    public function test_surgery_date_is_optional(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->postJson('/api/cases', $this->payload(['surgery_date' => null]))
            ->assertCreated()
            ->assertJsonPath('data.surgery_date', null);
    }

    // ---------------------------------------------------------------- update

    public function test_admin_can_edit_a_case_and_keep_its_own_case_number(): void
    {
        $admin = User::factory()->admin()->create();
        $case = MedicalCase::factory()->create(['case_number' => 'CASE-0100', 'status' => 'Draft']);

        // Same Case Number as before must not trigger the unique rule.
        $this->actingAs($admin)
            ->put("/cases/{$case->id}", $this->payload(['case_number' => 'CASE-0100', 'status' => 'Approved']))
            ->assertRedirect(route('cases.show', $case));

        $this->assertSame(CaseStatus::Approved, $case->fresh()->status);
    }

    public function test_edit_cannot_reuse_another_cases_number(): void
    {
        MedicalCase::factory()->create(['case_number' => 'CASE-0001']);
        $case = MedicalCase::factory()->create(['case_number' => 'CASE-0002']);
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->putJson("/api/cases/{$case->id}", $this->payload(['case_number' => 'CASE-0001']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['case_number']);
    }

    public function test_admin_can_update_a_case_through_the_api(): void
    {
        $case = MedicalCase::factory()->create(['case_number' => 'CASE-0100', 'status' => 'Draft']);
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->putJson("/api/cases/{$case->id}", $this->payload(['status' => 'In Review']))
            ->assertOk()
            ->assertJsonPath('data.status', 'In Review');
    }

    public function test_viewer_cannot_edit_a_case(): void
    {
        $case = MedicalCase::factory()->create(['status' => 'Draft']);
        $viewer = User::factory()->viewer()->create();

        $this->actingAs($viewer)->get("/cases/{$case->id}/edit")->assertForbidden();
        $this->actingAs($viewer)->put("/cases/{$case->id}", $this->payload())->assertForbidden();

        Sanctum::actingAs($viewer);
        $this->putJson("/api/cases/{$case->id}", $this->payload())->assertForbidden();

        $this->assertSame(CaseStatus::Draft, $case->fresh()->status);
    }

    // ---------------------------------------------------------------- delete

    /** Required test 6: a viewer cannot delete a case (403). */
    public function test_viewer_cannot_delete_a_case(): void
    {
        $case = MedicalCase::factory()->create();
        $viewer = User::factory()->viewer()->create();

        $this->actingAs($viewer)->delete("/cases/{$case->id}")->assertForbidden();

        Sanctum::actingAs($viewer);
        $this->deleteJson("/api/cases/{$case->id}")
            ->assertForbidden()
            ->assertJsonPath('success', false);

        $this->assertDatabaseHas('cases', ['id' => $case->id]);
    }

    public function test_admin_can_delete_a_case(): void
    {
        $case = MedicalCase::factory()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->delete("/cases/{$case->id}")->assertRedirect(route('cases.index'));

        $this->assertDatabaseMissing('cases', ['id' => $case->id]);
    }

    public function test_admin_can_delete_a_case_through_the_api(): void
    {
        $case = MedicalCase::factory()->create();
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->deleteJson("/api/cases/{$case->id}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('cases', ['id' => $case->id]);
    }

    // -------------------------------------------------------------- engineer

    public function test_engineer_can_edit_but_not_delete(): void
    {
        $case = MedicalCase::factory()->create(['case_number' => 'CASE-0100', 'status' => 'Draft']);
        $engineer = User::factory()->engineer()->create();

        $this->actingAs($engineer)
            ->put("/cases/{$case->id}", $this->payload(['status' => 'Planning']))
            ->assertRedirect();
        $this->assertSame(CaseStatus::Planning, $case->fresh()->status);

        $this->actingAs($engineer)->delete("/cases/{$case->id}")->assertForbidden();
        $this->assertDatabaseHas('cases', ['id' => $case->id]);
    }

    // ------------------------------------------------------------------ read

    public function test_viewer_can_see_list_and_details_without_action_buttons(): void
    {
        $case = MedicalCase::factory()->create(['case_number' => 'CASE-0100']);
        $viewer = User::factory()->viewer()->create();

        $this->actingAs($viewer)->get('/cases')
            ->assertOk()
            ->assertSee('CASE-0100')
            ->assertDontSee('New case')
            ->assertDontSee('>Delete<', false)
            ->assertDontSee('>Edit<', false);

        $this->actingAs($viewer)->get("/cases/{$case->id}")->assertOk()->assertSee('CASE-0100');
    }

    public function test_admin_sees_edit_and_delete_buttons(): void
    {
        MedicalCase::factory()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/cases')
            ->assertOk()
            ->assertSee('New case')
            ->assertSee('>Delete<', false)
            ->assertSee('>Edit<', false);
    }

    public function test_details_page_shows_creator_and_timestamps(): void
    {
        $creator = User::factory()->admin()->create(['name' => 'Alice Creator']);
        $case = MedicalCase::factory()->create(['created_by' => $creator->id]);

        $this->actingAs(User::factory()->viewer()->create())
            ->get("/cases/{$case->id}")
            ->assertOk()
            ->assertSee('Alice Creator')
            ->assertSee($case->created_at->format('Y-m-d H:i'));
    }

    public function test_case_list_is_paginated_ten_per_page(): void
    {
        MedicalCase::factory()->count(12)->create();

        $this->actingAs(User::factory()->viewer()->create())
            ->get('/cases')
            ->assertViewHas('cases', fn ($cases) => $cases->count() === 10 && $cases->total() === 12);
    }

    public function test_case_list_can_be_searched_and_filtered(): void
    {
        MedicalCase::factory()->create(['case_number' => 'CASE-7001', 'surgeon_name' => 'Dr. Alpha', 'status' => 'Approved', 'priority' => 'High']);
        MedicalCase::factory()->create(['case_number' => 'CASE-7002', 'surgeon_name' => 'Dr. Beta', 'status' => 'Draft', 'priority' => 'Low']);
        $viewer = User::factory()->viewer()->create();

        $numbers = fn (string $query) => $this->actingAs($viewer)
            ->get("/cases?{$query}")
            ->viewData('cases')
            ->pluck('case_number')
            ->all();

        $this->assertSame(['CASE-7001'], $numbers('search=Alpha'));
        $this->assertSame(['CASE-7002'], $numbers('search=CASE-7002'));
        $this->assertSame(['CASE-7001'], $numbers('status=Approved'));
        $this->assertSame(['CASE-7002'], $numbers('priority=Low'));
        $this->assertSame(['CASE-7001', 'CASE-7002'], $numbers('sort=case_number&direction=asc'));
        $this->assertSame(['CASE-7002', 'CASE-7001'], $numbers('sort=case_number&direction=desc'));
    }

    public function test_malformed_filter_input_does_not_break_the_list(): void
    {
        MedicalCase::factory()->count(2)->create();

        $this->actingAs(User::factory()->viewer()->create())
            ->get('/cases?search[]=x&status[]=Draft&sort[]=id')
            ->assertOk();
    }

    public function test_dashboard_shows_counts(): void
    {
        MedicalCase::factory()->create(['status' => 'Planning']);
        MedicalCase::factory()->count(2)->create(['status' => 'In Review']);
        MedicalCase::factory()->create(['status' => 'Approved']);
        MedicalCase::factory()->create(['status' => 'Completed']);
        MedicalCase::factory()->create(['status' => 'Draft']);

        $this->actingAs(User::factory()->viewer()->create())
            ->get('/dashboard')
            ->assertOk()
            ->assertViewHas('stats', [
                'Total cases' => 6,
                'Planning' => 1,
                'In Review' => 2,
                'Approved' => 1,
                'Completed' => 1,
            ]);
    }

    public function test_api_lists_cases_with_pagination_meta_and_filters(): void
    {
        MedicalCase::factory()->count(12)->create(['status' => 'Draft']);
        MedicalCase::factory()->count(2)->create(['status' => 'Approved']);
        Sanctum::actingAs(User::factory()->viewer()->create());

        $this->getJson('/api/cases')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.total', 14)
            ->assertJsonCount(10, 'data');

        $this->getJson('/api/cases?status=Approved')->assertJsonPath('meta.total', 2);
    }

    public function test_api_shows_one_case_and_returns_404_for_a_missing_one(): void
    {
        $case = MedicalCase::factory()->create(['case_number' => 'CASE-0100']);
        Sanctum::actingAs(User::factory()->viewer()->create());

        $this->getJson("/api/cases/{$case->id}")
            ->assertOk()
            ->assertJsonPath('data.case_number', 'CASE-0100');

        $this->getJson('/api/cases/999999')
            ->assertNotFound()
            ->assertJsonPath('success', false);
    }
}
