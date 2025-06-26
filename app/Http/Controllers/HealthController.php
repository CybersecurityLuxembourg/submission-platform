<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class HealthController extends Controller
{
    /**
     * Simple liveness probe - minimal check to determine if app should be restarted
     * Should only fail if the application itself is broken, not dependencies
     */
    public function liveness(): JsonResponse
    {
        try {
            // Basic check - can Laravel respond?
            $appName = config('app.name');
            
            if (!$appName) {
                return response()->json([
                    'status' => 'unhealthy',
                    'message' => 'Application configuration not loaded properly',
                    'timestamp' => now()->toIso8601String(),
                ], 503);
            }

            return response()->json([
                'status' => 'healthy',
                'message' => 'Application is running',
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'unhealthy',
                'message' => $e->getMessage(),
                'timestamp' => now()->toIso8601String(),
            ], 503);
        }
    }

    /**
     * Readiness probe - determines if app is ready to serve traffic
     * Can fail when dependencies are unavailable
     */
    public function readiness(): JsonResponse
    {
        $exitCode = Artisan::call('health:check', [
            '--type' => 'readiness',
            '--json' => true,
        ]);

        $output = json_decode(Artisan::output(), true);

        if ($exitCode === 0) {
            return response()->json($output);
        }

        return response()->json($output, 503);
    }

    /**
     * Full health check endpoint for monitoring
     */
    public function health(Request $request): JsonResponse
    {
        $type = $request->get('type', 'basic');
        
        $exitCode = Artisan::call('health:check', [
            '--type' => $type,
            '--json' => true,
        ]);

        $output = json_decode(Artisan::output(), true);

        if ($exitCode === 0) {
            return response()->json($output);
        }

        return response()->json($output, 503);
    }

    /**
     * Kubernetes-style startup probe
     * Similar to readiness but specifically for startup phase
     */
    public function startup(): JsonResponse
    {
        return $this->readiness();
    }
} 