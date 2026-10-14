<?php

namespace Database\Factories;

use App\Enums\CasePriority;
use App\Enums\CaseStatus;
use App\Models\MedicalCase;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Dummy data only - no real patient information.
 *
 * @extends Factory<MedicalCase>
 */
class MedicalCaseFactory extends Factory
{
    protected $model = MedicalCase::class;

    private const IMPLANTS = [
        'Hip Stem', 'Knee Femoral Component', 'Tibial Tray', 'Spinal Fusion Cage',
        'Shoulder Humeral Head', 'Pedicle Screw System', 'Bone Plate', 'Intramedullary Nail',
    ];

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'case_number' => 'CASE-'.str_pad((string) fake()->unique()->numberBetween(1, 99999), 5, '0', STR_PAD_LEFT),
            'patient_reference' => 'PT-'.fake()->unique()->numerify('######'),
            'surgeon_name' => 'Dr. '.fake()->name(),
            'implant_type' => fake()->randomElement(self::IMPLANTS),
            'status' => fake()->randomElement(CaseStatus::cases()),
            'priority' => fake()->randomElement(CasePriority::cases()),
            'surgery_date' => fake()->optional(0.8)->dateTimeBetween('-2 months', '+4 months')?->format('Y-m-d'),
            'created_by' => User::factory()->admin(),
        ];
    }
}
