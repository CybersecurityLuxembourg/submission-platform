<?php

namespace App\Http\Middleware;

use App\Models\ApiLog;
use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiLogMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Record the start time for performance tracking
        $startTime = microtime(true);
        
        // Process the request
        $response = $next($request);
        
        // Only log API requests
        if (!$this->shouldLogRequest($request)) {
            return $response;
        }
        
        try {
            $user = $request->user();
            
            if (!$user) {
                return $response;
            }
            
            // Get token information if available
            $tokenId = null;
            $bearerToken = $request->bearerToken();
            
            if ($bearerToken) {
                $tokenHash = hash('sha256', $bearerToken);
                $token = ApiToken::where('token', $tokenHash)
                                ->where('user_id', $user->id)
                                ->first();
                
                if ($token) {
                    $tokenId = $token->id;
                }
            }
            
            // Calculate execution time
            $executionTime = microtime(true) - $startTime;
            
            // Log the API request
            ApiLog::create([
                'user_id' => $user->id,
                'token_id' => $tokenId,
                'method' => $request->method(),
                'endpoint' => $request->path(),
                'ip_address' => $request->ip(),
                'request_data' => $this->sanitizeRequestData($request),
                'response_code' => $response->getStatusCode(),
                'execution_time' => $executionTime
            ]);
        } catch (\Exception $e) {
            // Log the error but don't affect the response
            \Log::error('API logging failed: ' . $e->getMessage());
        }
        
        return $response;
    }
    
    /**
     * Determine if the request should be logged.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    private function shouldLogRequest(Request $request): bool
    {
        // Only log API requests
        return str_starts_with($request->path(), 'api/');
    }
    
    /**
     * Sanitize request data to remove sensitive information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    private function sanitizeRequestData(Request $request): array
    {
        $data = $request->all();
        
        // Remove sensitive data
        foreach (['password', 'token', 'secret', 'key', 'authorization'] as $field) {
            if (isset($data[$field])) {
                $data[$field] = '***REDACTED***';
            }
        }
        
        return $data;
    }
} 