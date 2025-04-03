<?php

namespace App\Providers;

use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerGates();
        $this->registerRateLimiters();
    }

    /**
     * Register application gates.
     *
     * @return void
     */
    private function registerGates(): void
    {
        Gate::define('viewApiDocs', function (User $user) {
            return $this->isDomainAllowed($user->email);
        });
    }

    /**
     * Check if the user's email domain is allowed to access API docs.
     *
     * @param string $email
     * @return bool
     */
    private function isDomainAllowed(string $email): bool
    {
        $host = request()->getHost();
        
        // Allow all access if domain starts with "test."
        if (str_starts_with($host, 'test.')) {
            return true;
        }
        
        $allowedDomains = explode(',', env('API_DOCS_ALLOWED_DOMAINS', 'lhc.lu,circl.lu,nc3.lu'));
        $emailDomain = substr(strrchr($email, "@"), 1);
        
        return in_array($emailDomain, $allowedDomains);
    }

    /**
     * Register application rate limiters.
     *
     * @return void
     */
    private function registerRateLimiters(): void
    {
        $this->registerGeneralApiRateLimiter();
        $this->registerApiAuthRateLimiter();
        $this->registerApiSubmissionsRateLimiter();
    }

    /**
     * Register general API rate limiter.
     *
     * @return void
     */
    private function registerGeneralApiRateLimiter(): void
    {
        RateLimiter::for('api', function (Request $request) {
            $apiToken = $request->attributes->get('api_token');
            
            if ($apiToken) {
                return Limit::perMinute(120)->by('token:' . $apiToken->id);
            }
            
            return Limit::perMinute(20)->by('ip:' . $request->ip());
        });
    }

    /**
     * Register API authentication rate limiter.
     *
     * @return void
     */
    private function registerApiAuthRateLimiter(): void
    {
        RateLimiter::for('api-auth', function (Request $request) {
            return Limit::perMinute(5)->by('ip:' . $request->ip());
        });
    }

    /**
     * Register API submissions rate limiter.
     *
     * @return void
     */
    private function registerApiSubmissionsRateLimiter(): void
    {
        RateLimiter::for('api-submissions', function (Request $request) {
            $apiToken = $request->attributes->get('api_token');
            $identifier = $apiToken?->id ?? $request->ip();
            
            if ($request->isMethod('GET')) {
                return Limit::perMinute(200)->by('token:' . $identifier);
            }
            
            return [
                Limit::perMinute(60)->by('token:' . $identifier),
                Limit::perDay(1000)->by('daily:token:' . $identifier),
            ];
        });
    }
}
