<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenIPMiddleware
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
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        
        // Get the bearer token from the request
        $bearerToken = $request->bearerToken();
        
        if (!$bearerToken) {
            return response()->json(['message' => 'No API token provided'], 401);
        }
        
        // Find the token in our database
        $tokenHash = hash('sha256', $bearerToken);
        $token = ApiToken::where('token', $tokenHash)
                         ->where('user_id', $user->id)
                         ->first();
        
        if (!$token) {
            return response()->json(['message' => 'Invalid API token'], 401);
        }
        
        // Check if token is expired
        if ($token->isExpired()) {
            return response()->json(['message' => 'API token has expired'], 401);
        }
        
        // Check IP restrictions if any
        if (!$token->isValidIp($request->ip())) {
            return response()->json([
                'message' => 'Access denied from this IP address'
            ], 403);
        }
        
        // Update last used timestamp
        $token->markAsUsed();
        
        // Check token abilities based on the request
        $requiredAbility = $this->getRequiredAbility($request);
        if ($requiredAbility && !$token->can($requiredAbility)) {
            return response()->json([
                'message' => 'API token does not have the required permissions'
            ], 403);
        }
        
        return $next($request);
    }
    
    /**
     * Determine the required ability for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    private function getRequiredAbility(Request $request): ?string
    {
        $path = $request->path();
        $method = $request->method();
        
        // Forms management abilities
        if (preg_match('#^api/v1/forms#', $path)) {
            if ($method === 'GET') {
                return 'forms:read';
            } elseif ($method === 'POST') {
                return 'forms:create';
            } elseif ($method === 'PUT' || $method === 'PATCH') {
                return 'forms:update';
            } elseif ($method === 'DELETE') {
                return 'forms:delete';
            }
        }
        
        // Submissions management abilities
        if (preg_match('#^api/v1/forms/\d+/submissions#', $path)) {
            if ($method === 'GET') {
                return 'submissions:read';
            } elseif ($method === 'POST') {
                return 'submissions:create';
            } elseif ($method === 'PUT' || $method === 'PATCH') {
                return 'submissions:update';
            } elseif ($method === 'DELETE') {
                return 'submissions:delete';
            }
        }
        
        // Default to null (no specific ability required)
        return null;
    }
} 