<?php

namespace App\Providers;

use App\Models\Form;
use App\Policies\FormPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Form::class => FormPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // If you need any custom Gates, you can define them here
        // Gate::define('update-form', [FormPolicy::class, 'update']);
    }
}
