<?php
// app/Http/Middleware/ScanUploadedFiles.php

namespace App\Http\Middleware;

use App\Services\FileScanService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ScanUploadedFiles
{
    protected FileScanService $scanService;
    
    public function __construct(FileScanService $scanService)
    {
        $this->scanService = $scanService;
    }
    
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip if scanning is disabled
        if (!config('services.pandora.enabled', true)) {
            return $next($request);
        }
        
        // Check if there are file uploads in the 'field' input array
        // This matches how Filament handles file uploads in Repeater fields, etc.
        if (!$request->hasFile('field')) {
            // Check if there are any files at all in the request directly, for simpler single file uploads
            if (empty($request->allFiles())) {
                 return $next($request);
            }
            // If files exist, but not under 'field', process them directly.
            // This part might need adjustment based on how single files are uploaded outside of Filament repeaters.
            $allFiles = $request->allFiles(); 
            foreach($allFiles as $inputName => $fileOrFiles) {
                if (is_array($fileOrFiles)) {
                    foreach ($fileOrFiles as $singleFile) {
                        if ($singleFile instanceof \Illuminate\Http\UploadedFile) {
                            $scanResult = $this->scanFile($singleFile, $inputName, $request);
                            if ($scanResult) {
                                return $scanResult;
                            }
                        }
                    }
                } elseif ($fileOrFiles instanceof \Illuminate\Http\UploadedFile) {
                    $scanResult = $this->scanFile($fileOrFiles, $inputName, $request);
                    if ($scanResult) {
                        return $scanResult;
                    }
                }
            }
            return $next($request); // If files were processed directly, continue
        }
        
        // Process files under the 'field' key (typical for Filament form submissions)
        $filesInput = $request->file('field');
        
        foreach ($filesInput as $fieldId => $fileOrFiles) {
            if (!$fileOrFiles) continue;
            
            if (is_array($fileOrFiles)) { // Handles multiple files for a single field_id
                foreach ($fileOrFiles as $singleFile) {
                     if (!$singleFile instanceof \Illuminate\Http\UploadedFile) continue;
                    $scanResult = $this->scanService->scanFile($singleFile);
                    if (config('services.pandora.block_malicious', true) && 
                        $scanResult['success'] && 
                        $scanResult['is_malicious']) {
                        
                        Log::warning('Blocked malicious file upload', [
                            'filename' => $singleFile->getClientOriginalName(),
                            'scan_results' => $scanResult['scan_results'] ?? [],
                        ]);
                        
                        return response()->json([
                            'message' => 'The uploaded file appears to be malicious and has been blocked.',
                            'errors' => [
                                // Adjust field name to match Filament's expected error format for repeaters/blocks
                                'field.'.$fieldId => ['The file \'' . $singleFile->getClientOriginalName() . '\' has been flagged as potentially malicious and cannot be uploaded.']
                            ]
                        ], 422);
                    }
                }
            } elseif ($fileOrFiles instanceof \Illuminate\Http\UploadedFile) {
                $scanResult = $this->scanService->scanFile($fileOrFiles);
                if (config('services.pandora.block_malicious', true) && 
                    $scanResult['success'] && 
                    $scanResult['is_malicious']) {
                    
                    Log::warning('Blocked malicious file upload', [
                        'filename' => $fileOrFiles->getClientOriginalName(),
                        'scan_results' => $scanResult['scan_results'] ?? [],
                    ]);
                    
                    return response()->json([
                        'message' => 'The uploaded file appears to be malicious and has been blocked.',
                        'errors' => [
                             // Adjust field name to match Filament's expected error format
                            'field.'.$fieldId => ['The file \'' . $fileOrFiles->getClientOriginalName() . '\' has been flagged as potentially malicious and cannot be uploaded.']
                        ]
                    ], 422);
                }
            }
        }
        
        return $next($request);
    }
    
    /**
     * Process and scan a single file
     * 
     * @param \Illuminate\Http\UploadedFile $file The file to scan
     * @param string $inputName The name of the input field
     * @param Request $request The request object
     * @return Response|null Response object if file is malicious, null otherwise
     */
    protected function scanFile(\Illuminate\Http\UploadedFile $file, string $inputName, Request $request): ?Response
    {
        $scanResult = $this->scanService->scanFile($file);
        
        if (config('services.pandora.block_malicious', true) && 
            $scanResult['success'] && 
            $scanResult['is_malicious']) {
            
            Log::warning('Blocked malicious file upload', [
                'filename' => $file->getClientOriginalName(),
                'scan_results' => $scanResult['scan_results'] ?? [],
            ]);
            
            return response()->json([
                'message' => 'The uploaded file (' . $file->getClientOriginalName() . ') appears to be malicious and has been blocked.',
                'errors' => [
                    $inputName => ['The file \'' . $file->getClientOriginalName() . '\' has been flagged as potentially malicious and cannot be uploaded.']
                ]
            ], 422);
        }
        
        return null;
    }
} 