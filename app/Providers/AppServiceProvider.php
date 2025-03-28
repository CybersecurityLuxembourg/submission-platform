<?php

namespace App\Providers;

use App\Models\ApiToken;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Define rate limiter for general API usage
        RateLimiter::for('api', function (Request $request) {
            // Get the API token if available
            $apiToken = $request->attributes->get('api_token');
            
            if ($apiToken) {
                // For valid API tokens, allow 120 requests per minute
                // Identify by token ID (better than user ID as one user may have multiple tokens)
                return Limit::perMinute(120)->by('token:' . $apiToken->id);
            }
            
            // For unauthenticated requests, allow only 20 per minute per IP address
            // This prevents brute forcing tokens
            return Limit::perMinute(20)->by('ip:' . $request->ip());
        });
        
        // Define specific rate limiter for token validation failures
        RateLimiter::for('api-auth', function (Request $request) {
            // Strict rate limit for failed auth attempts - 5 per minute per IP
            return Limit::perMinute(5)->by('ip:' . $request->ip());
        });
        
        // Define rate limiter for form submissions
        RateLimiter::for('api-submissions', function (Request $request) {
            $apiToken = $request->attributes->get('api_token');
            
            // Higher limits for read operations
            if ($request->isMethod('GET')) {
                return Limit::perMinute(200)->by('token:' . ($apiToken?->id ?? $request->ip()));
            }
            
            // More restricted limits for write operations
            return [
                // Per-minute restriction
                Limit::perMinute(60)->by('token:' . ($apiToken?->id ?? $request->ip())),
                
                // Daily cap for write operations
                Limit::perDay(1000)->by('daily:token:' . ($apiToken?->id ?? $request->ip())),
            ];
        });
    }
}
