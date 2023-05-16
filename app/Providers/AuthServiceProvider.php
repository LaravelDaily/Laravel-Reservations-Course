<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Company;
use App\Models\Activity;
use App\Policies\ActivityPolicy;
use App\Policies\CompanyUserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Company::class => CompanyUserPolicy::class,
        Activity::class => ActivityPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
