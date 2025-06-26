<?php
// app/Console/Commands/HealthCheck.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

class HealthCheck extends Command
{
    protected $signature = 'health:check 
                            {--type=basic : Type of health check (basic|full|liveness|readiness)}
                            {--json : Output results as JSON}
                            {--timeout=10 : Timeout for individual checks in seconds}';
    
    protected $description = 'Perform health checks on the application (supports Docker/Kubernetes patterns)';

    private array $checks = [];
    private int $exitCode = 0;
    private int $timeout;

    public function handle(): int
    {
        $this->timeout = (int) $this->option('timeout');
        $type = $this->option('type');

        $this->info("Running {$type} health checks...");

        switch ($type) {
            case 'liveness':
                $this->runLivenessChecks();
                break;
            case 'readiness':
                $this->runReadinessChecks();
                break;
            case 'basic':
                $this->runBasicChecks();
                break;
            case 'full':
            default:
                $this->runFullChecks();
                break;
        }

        // Output results
        if ($this->option('json')) {
            $this->outputJson();
        } else {
            $this->outputTable();
        }

        return $this->exitCode;
    }

    /**
     * Liveness checks - minimal checks to determine if the app should be restarted
     * Should only fail if the application itself is broken, not dependencies
     */
    private function runLivenessChecks(): void
    {
        $this->checkAppBasics();
    }

    /**
     * Readiness checks - determines if the app is ready to serve traffic
     * Can fail when dependencies are unavailable
     */
    private function runReadinessChecks(): void
    {
        $this->checkAppBasics();
        $this->checkDatabase();
        $this->checkCache();
        $this->checkStorage();
    }

    /**
     * Basic checks - lightweight version for general monitoring
     */
    private function runBasicChecks(): void
    {
        $this->checkAppBasics();
        $this->checkDatabase();
        $this->checkCache();
    }

    /**
     * Full checks - comprehensive monitoring including optional services
     */
    private function runFullChecks(): void
    {
        $this->checkAppBasics();
        $this->checkDatabase();
        $this->checkCache();
        $this->checkRedis();
        $this->checkStorage();
        $this->checkQueue();
        $this->checkExternalServices();
    }

    private function checkAppBasics(): void
    {
        try {
            // Check if Laravel can boot properly
            $start = microtime(true);
            $appName = config('app.name');
            $responseTime = round((microtime(true) - $start) * 1000, 2);
            
            if ($appName) {
                $this->addCheck('Application', 'success', 'Laravel application running', $responseTime);
            } else {
                $this->addCheck('Application', 'error', 'Configuration not loaded properly');
                $this->exitCode = 1;
            }
        } catch (\Exception $e) {
            $this->addCheck('Application', 'error', $e->getMessage());
            $this->exitCode = 1;
        }
    }

    private function checkDatabase(): void
    {
        try {
            $start = microtime(true);
            
            // Wrap in timeout
            $this->executeWithTimeout(function() {
                DB::select('SELECT 1');
            }, $this->timeout);
            
            $responseTime = round((microtime(true) - $start) * 1000, 2);

            // Only check migrations in full mode to avoid restart loops
            if ($this->option('type') === 'full') {
                $pendingMigrations = collect(app('migrator')->getMigrationFiles())
                    ->diff(app('migrator')->getRepository()->getRan())
                    ->count();

                if ($pendingMigrations > 0) {
                    $this->addCheck('Database', 'warning', "Connected but {$pendingMigrations} pending migrations", $responseTime);
                } else {
                    $this->addCheck('Database', 'success', 'Connected and up to date', $responseTime);
                }
            } else {
                $this->addCheck('Database', 'success', 'Connected', $responseTime);
            }
        } catch (\Exception $e) {
            $this->addCheck('Database', 'error', $e->getMessage());
            $this->exitCode = 1;
        }
    }

    private function checkCache(): void
    {
        try {
            $key = 'health_check_' . time() . '_' . uniqid();
            
            $this->executeWithTimeout(function() use ($key) {
                Cache::put($key, true, 10);
                $value = Cache::get($key);
                Cache::forget($key);
                
                if ($value !== true) {
                    throw new \Exception('Read/write test failed');
                }
            }, $this->timeout);

            $this->addCheck('Cache', 'success', 'Working properly');
        } catch (\Exception $e) {
            $this->addCheck('Cache', 'error', $e->getMessage());
            $this->exitCode = 1;
        }
    }

    private function checkRedis(): void
    {
        try {
            $start = microtime(true);
            
            $this->executeWithTimeout(function() {
                Redis::ping();
            }, $this->timeout);
            
            $responseTime = round((microtime(true) - $start) * 1000, 2);

            // Check Redis memory usage in full mode
            if ($this->option('type') === 'full') {
                $info = Redis::info('memory');
                $usedMemory = $info['used_memory_human'] ?? 'Unknown';
                $this->addCheck('Redis', 'success', "Connected (Memory: {$usedMemory})", $responseTime);
            } else {
                $this->addCheck('Redis', 'success', 'Connected', $responseTime);
            }
        } catch (\Exception $e) {
            // Redis is often optional - don't fail the health check
            $this->addCheck('Redis', 'warning', $e->getMessage());
        }
    }

    private function checkStorage(): void
    {
        try {
            $testFile = 'health_check_' . time() . '_' . uniqid() . '.txt';
            
            $this->executeWithTimeout(function() use ($testFile) {
                Storage::put($testFile, 'test');
                $content = Storage::get($testFile);
                Storage::delete($testFile);
                
                if ($content !== 'test') {
                    throw new \Exception('Read/write test failed');
                }
            }, $this->timeout);

            // Only check disk space in full mode to keep basic checks lightweight
            if ($this->option('type') === 'full') {
                $disk = Storage::disk();
                $path = $disk->path('');
                $free = disk_free_space($path);
                $total = disk_total_space($path);
                $usedPercent = round(($total - $free) / $total * 100, 2);

                if ($usedPercent > 90) {
                    $this->addCheck('Storage', 'warning', "Working but disk {$usedPercent}% full");
                } else {
                    $this->addCheck('Storage', 'success', "Working (Disk usage: {$usedPercent}%)");
                }
            } else {
                $this->addCheck('Storage', 'success', 'Working properly');
            }
        } catch (\Exception $e) {
            $this->addCheck('Storage', 'error', $e->getMessage());
            $this->exitCode = 1;
        }
    }

    private function checkQueue(): void
    {
        try {
            $this->executeWithTimeout(function() {
                // Check if queue workers are running
                $stuckJobs = DB::table('jobs')->where('reserved_at', '<', now()->subMinutes(5))->count();
                
                if ($stuckJobs > 0) {
                    $this->addCheck('Queue', 'warning', "{$stuckJobs} stuck jobs detected");
                    return;
                }

                $jobCount = DB::table('jobs')->count();
                $failedCount = DB::table('failed_jobs')->count();
                
                $status = $failedCount > 10 ? 'warning' : 'success';
                $message = "Processing ({$jobCount} pending, {$failedCount} failed)";
                
                $this->addCheck('Queue', $status, $message);
            }, $this->timeout);
        } catch (\Exception $e) {
            $this->addCheck('Queue', 'warning', $e->getMessage());
        }
    }

    private function checkExternalServices(): void
    {
        // Check mail service
        try {
            $mailConfig = config('mail.mailers.smtp');
            if ($mailConfig && isset($mailConfig['host'], $mailConfig['port'])) {
                $this->executeWithTimeout(function() use ($mailConfig) {
                    $fp = @fsockopen($mailConfig['host'], $mailConfig['port'], $errno, $errstr, 3);
                    if (!$fp) {
                        throw new \Exception("Unreachable: {$errstr}");
                    }
                    fclose($fp);
                }, 5);
                
                $this->addCheck('Mail Service', 'success', 'Reachable');
            } else {
                $this->addCheck('Mail Service', 'info', 'Not configured');
            }
        } catch (\Exception $e) {
            $this->addCheck('Mail Service', 'warning', $e->getMessage());
        }
    }

    private function executeWithTimeout(callable $callback, int $timeout): void
    {
        $originalTimeout = ini_get('default_socket_timeout');
        ini_set('default_socket_timeout', $timeout);
        
        try {
            $callback();
        } finally {
            ini_set('default_socket_timeout', $originalTimeout);
        }
    }

    private function addCheck(string $service, string $status, string $message, ?float $responseTime = null): void
    {
        $this->checks[] = [
            'service' => $service,
            'status' => $status,
            'message' => $message,
            'response_time' => $responseTime,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    private function outputTable(): void
    {
        $headers = ['Service', 'Status', 'Message', 'Response Time (ms)'];
        $rows = collect($this->checks)->map(function ($check) {
            $status = match($check['status']) {
                'success' => '<fg=green>✓ ' . strtoupper($check['status']) . '</>',
                'warning' => '<fg=yellow>⚠ ' . strtoupper($check['status']) . '</>',
                'error' => '<fg=red>✗ ' . strtoupper($check['status']) . '</>',
                'info' => '<fg=blue>ℹ ' . strtoupper($check['status']) . '</>',
                default => strtoupper($check['status']),
            };

            return [
                $check['service'],
                $status,
                $check['message'],
                $check['response_time'] ? $check['response_time'] . 'ms' : 'N/A',
            ];
        })->toArray();

        $this->table($headers, $rows);

        $summary = collect($this->checks)->groupBy('status')->map->count();
        $this->info("\nSummary:");
        foreach (['success', 'warning', 'error', 'info'] as $status) {
            if ($count = $summary[$status] ?? 0) {
                $color = match($status) {
                    'success' => 'green',
                    'warning' => 'yellow',
                    'error' => 'red',
                    'info' => 'blue',
                };
                $this->line("<fg={$color}>" . ucfirst($status) . ": {$count}</>");
            }
        }
    }

    private function outputJson(): void
    {
        $output = [
            'status' => $this->exitCode === 0 ? 'healthy' : 'unhealthy',
            'timestamp' => now()->toIso8601String(),
            'type' => $this->option('type'),
            'checks' => $this->checks,
            'summary' => collect($this->checks)->groupBy('status')->map->count(),
        ];

        $this->line(json_encode($output, JSON_PRETTY_PRINT));
    }
}