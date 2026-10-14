<?php

namespace App\Providers;

use App\Models\MedicalCase;
use App\Policies\MedicalCasePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(MedicalCase::class, MedicalCasePolicy::class);
    }
}
