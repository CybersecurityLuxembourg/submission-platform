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
        $this->loadDatabaseConfigs();
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
        
        // Get allowed domains from database settings
        $allowedDomainsStr = ApiSetting::get('api_docs_allowed_domains', env('API_DOCS_ALLOWED_DOMAINS', ''));
        
        if (empty($allowedDomainsStr)) {
            return false;
        }
        
        $allowedDomains = array_filter(array_map('trim', explode(',', $allowedDomainsStr)));
        $emailDomain = substr(strrchr($email, "@"), 1);
        
        return !empty($emailDomain) && in_array($emailDomain, $allowedDomains);
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
                $limit = max(1, (int) ApiSetting::get('rate_limit_api_authenticated'));
                return Limit::perMinute($limit)->by('token:' . $apiToken->id);
            }
            
            $limit = max(1, (int) ApiSetting::get('rate_limit_api_unauthenticated'));
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
            $limit = max(1, (int) ApiSetting::get('rate_limit_auth_attempts'));
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
                $limit = max(1, (int) ApiSetting::get('rate_limit_submissions_read'));
                return Limit::perMinute($limit)->by('token:' . $identifier);
            }
            
            $writeLimit = max(1, (int) ApiSetting::get('rate_limit_submissions_write'));
            $dailyLimit = max(1, (int) ApiSetting::get('rate_limit_submissions_daily'));
            
            return [
                Limit::perMinute($writeLimit)->by('token:' . $identifier),
                Limit::perDay($dailyLimit)->by('daily:token:' . $identifier),
            ];
        });
    }

    /**
     * Load configuration values from database settings.
     *
     * @return void
     */
    private function loadDatabaseConfigs(): void
    {
        try {
            // Update CORS allowed origins from database
            $corsOrigins = ApiSetting::get('cors_allowed_origins', env('CORS_ALLOWED_ORIGINS', ''));
            
            if (!empty($corsOrigins)) {
                $corsArray = array_filter(array_map('trim', explode(',', $corsOrigins)));
                if (!empty($corsArray)) {
                    config(['cors.allowed_origins' => $corsArray]);
                }
            }

            // Update Sanctum token prefix from database
            $tokenPrefix = ApiSetting::get('sanctum_token_prefix', env('SANCTUM_TOKEN_PREFIX', ''));
            config(['sanctum.token_prefix' => $tokenPrefix ?? '']);
        } catch (\Throwable $e) {
            // Silently fail if database is not available (e.g., during migrations)
            // Log the error for debugging purposes
            if (app()->environment('local')) {
                logger()->debug('Failed to load database configs: ' . $e->getMessage());
            }
        }
    }
}
