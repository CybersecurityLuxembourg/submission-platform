<?php
// app/Services/FileScanService.php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FileScanService
{
    protected string $pandoraUrl;
    
    public function __construct()
    {
        $this->pandoraUrl = config('services.pandora.url', 'http://pandora:6100');
    }
    
    /**
     * Scan an uploaded file for malware
     * 
     * @param UploadedFile $file The file to scan
     * @return array The scan results
     */
    public function scanFile(UploadedFile $file): array
    {
        try {
            // Store file temporarily
            $tempPath = $file->storeAs('temp/scans', $file->getClientOriginalName());
            $fullPath = Storage::path($tempPath);
            
            // Call Pandora API to scan the file
            $response = Http::timeout(60)
                ->attach('file', file_get_contents($fullPath), $file->getClientOriginalName())
                ->post("{$this->pandoraUrl}/submit_file");
            
            // Clean up temporary file
            Storage::delete($tempPath);
            
            if ($response->successful()) {
                $scanResults = $response->json();
                return [
                    'success' => true,
                    'is_malicious' => $this->determineMalicious($scanResults),
                    'scan_results' => $scanResults,
                ];
            }
            
            Log::error('File scan failed', [
                'response' => $response->body(),
                'file' => $file->getClientOriginalName()
            ]);
            
            return [
                'success' => false,
                'message' => 'Scan service failed to process file'
            ];
        } catch (\Exception $e) {
            Log::error('Exception during file scan', [
                'message' => $e->getMessage(),
                'file' => $file->getClientOriginalName()
            ]);
            
            return [
                'success' => false,
                'message' => 'Error scanning file: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Determines if a file is malicious based on scan results
     * 
     * @param array $scanResults The results from Pandora
     * @return bool Whether the file is considered malicious
     */
    protected function determineMalicious(array $scanResults): bool
    {
        // Initially check just ClamAV results
        if (isset($scanResults['antivirus']['clamav']) && !empty($scanResults['antivirus']['clamav'])) {
            return true;
        }
        
        return false;
    }
} 