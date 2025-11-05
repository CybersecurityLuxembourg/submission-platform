<?php

namespace App\Providers;

use App\Models\ApiSetting;
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
        
        // Get allowed domains from database settings, fallback to env
        $allowedDomainsStr = ApiSetting::get('api_docs_allowed_domains', env('API_DOCS_ALLOWED_DOMAINS', 'lhc.lu,circl.lu,nc3.lu'));
        $allowedDomains = array_map('trim', explode(',', $allowedDomainsStr));
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
                $limit = (int) ApiSetting::get('rate_limit_api_authenticated', 120);
                return Limit::perMinute($limit)->by('token:' . $apiToken->id);
            }
            
            $limit = (int) ApiSetting::get('rate_limit_api_unauthenticated', 20);
            return Limit::perMinute($limit)->by('ip:' . $request->ip());
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
            $limit = (int) ApiSetting::get('rate_limit_auth_attempts', 5);
            return Limit::perMinute($limit)->by('ip:' . $request->ip());
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
                $limit = (int) ApiSetting::get('rate_limit_submissions_read', 200);
                return Limit::perMinute($limit)->by('token:' . $identifier);
            }
            
            $writeLimit = (int) ApiSetting::get('rate_limit_submissions_write', 60);
            $dailyLimit = (int) ApiSetting::get('rate_limit_submissions_daily', 1000);
            
            return [
                Limit::perMinute($writeLimit)->by('token:' . $identifier),
                Limit::perDay($dailyLimit)->by('daily:token:' . $identifier),
            ];
        });
    }
}
