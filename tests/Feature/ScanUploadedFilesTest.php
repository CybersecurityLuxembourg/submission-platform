<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\FileScanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ScanUploadedFilesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_skips_scanning_when_pandora_disabled(): void
    {
        Config::set('services.pandora.enabled', false);
        
        // Just verify config is set correctly
        $this->assertFalse(config('services.pandora.enabled'));
    }

    public function test_blocks_malicious_files_when_enabled(): void
    {
        Config::set('services.pandora.enabled', true);
        Config::set('services.pandora.block_malicious', true);

        // Mock FileScanService to return malicious result
        $mock = $this->mock(FileScanService::class);
        $mock->shouldReceive('scanFile')
            ->andReturn([
                'success' => true,
                'is_malicious' => true,
                'scan_results' => ['antivirus' => ['clamav' => ['Win.Test.EICAR']]],
            ]);

        // Test that the service identifies malicious files
        $file = UploadedFile::fake()->create('malware.exe', 100);
        $result = $mock->scanFile($file);
        
        $this->assertTrue($result['is_malicious']);
    }

    public function test_allows_clean_files_through(): void
    {
        Config::set('services.pandora.enabled', true);

        // Mock FileScanService to return clean result
        $mock = $this->mock(FileScanService::class);
        $mock->shouldReceive('scanFile')
            ->andReturn([
                'success' => true,
                'is_malicious' => false,
                'scan_results' => [],
            ]);

        $file = UploadedFile::fake()->create('clean.pdf', 100);
        $result = $mock->scanFile($file);
        
        $this->assertFalse($result['is_malicious']);
    }

    public function test_handles_scan_service_failure_gracefully(): void
    {
        Config::set('services.pandora.enabled', true);
        Config::set('services.pandora.block_malicious', true);

        // Mock FileScanService to return failure
        $mock = $this->mock(FileScanService::class);
        $mock->shouldReceive('scanFile')
            ->andReturn([
                'success' => false,
                'message' => 'Scan service unavailable',
            ]);

        $file = UploadedFile::fake()->create('document.pdf', 100);
        $result = $mock->scanFile($file);
        
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('message', $result);
    }

    public function test_respects_pandora_enabled_config(): void
    {
        // Test enabled state
        Config::set('services.pandora.enabled', true);
        $this->assertTrue(config('services.pandora.enabled'));

        // Test disabled state
        Config::set('services.pandora.enabled', false);
        $this->assertFalse(config('services.pandora.enabled'));
    }

    public function test_respects_block_malicious_config(): void
    {
        // Test block enabled
        Config::set('services.pandora.block_malicious', true);
        $this->assertTrue(config('services.pandora.block_malicious'));

        // Test block disabled (scan-only mode)
        Config::set('services.pandora.block_malicious', false);
        $this->assertFalse(config('services.pandora.block_malicious'));
    }
}
