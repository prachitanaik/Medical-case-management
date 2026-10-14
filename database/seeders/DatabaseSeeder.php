<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\MedicalCase;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    public const CASE_COUNT = 24;

    public function run(): void
    {
        foreach (['admin_email', 'admin_password', 'viewer_email', 'viewer_password'] as $key) {
            if (blank(config("seeding.{$key}"))) {
                throw new RuntimeException(
                    'Missing seeding credentials. Copy .env.example to .env (SEED_* variables) and run again.'
                );
            }
        }

        $admin = User::updateOrCreate(
            ['email' => config('seeding.admin_email')],
            ['name' => 'Demo Admin', 'password' => config('seeding.admin_password')],
        );
        $admin->forceFill(['role' => Role::Admin, 'email_verified_at' => now()])->save();

        $viewer = User::updateOrCreate(
            ['email' => config('seeding.viewer_email')],
            ['name' => 'Demo Viewer', 'password' => config('seeding.viewer_password')],
        );
        $viewer->forceFill(['role' => Role::Viewer, 'email_verified_at' => now()])->save();

        if (MedicalCase::query()->doesntExist()) {
            MedicalCase::factory()
                ->count(self::CASE_COUNT)
                ->sequence(fn ($sequence) => ['case_number' => sprintf('CASE-%04d', $sequence->index + 1)])
                ->create(['created_by' => $admin->id]);
        }
    }
}
