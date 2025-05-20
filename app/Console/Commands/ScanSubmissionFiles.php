<?php

namespace App\Console\Commands;

use App\Models\Submission;
use App\Models\SubmissionValues;
use App\Models\FormField;
use App\Models\ScanResult;
use App\Services\FileScanService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class ScanSubmissionFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:scan-files {submission_id? : The ID of the submission to scan} {--all : Scan all submissions} {--force : Force re-scan even if results exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan files in submissions using Pandora. Optionally use --force to re-scan.';

    /**
     * Execute the console command.
     */
    public function handle(FileScanService $scanService): int
    {
        if (!config('services.pandora.enabled', true)) {
            $this->error('Pandora scanning is disabled in the configuration.');
            return Command::FAILURE;
        }
        
        $forceScan = $this->option('force');

        if ($this->option('all')) {
            // Consider chunking for very large numbers of submissions
            $submissions = Submission::all();
            $this->info("Scanning files from all {$submissions->count()} submissions...");
        } else {
            $submissionId = $this->argument('submission_id');
            if (!$submissionId) {
                $this->error('Please provide a submission ID or use the --all option.');
                return Command::INVALID;
            }
            
            $submission = Submission::find($submissionId);
            if (!$submission) {
                $this->error("Submission with ID {$submissionId} not found.");
                return Command::FAILURE;
            }
            $submissions = [$submission]; // Wrap in array for consistent processing
        }
        
        $scannedFilesCount = 0;
        $createdResultsCount = 0;
        $updatedResultsCount = 0;
        $failedScansCount = 0;

        foreach ($submissions as $submission) {
            $this->line("Processing submission ID: {$submission->id}");
            
            $fileValues = SubmissionValues::where('submission_id', $submission->id)
                ->whereHas('field', function ($query) {
                    $query->where('type', 'file');
                })
                ->get();
                
            if ($fileValues->isEmpty()) {
                $this->line("No files found for submission ID: {$submission->id}");
                continue;
            }
            
            $this->info("Found {$fileValues->count()} file entries to process for submission ID: {$submission->id}");
            
            foreach ($fileValues as $value) {
                // Check if a scan result already exists for this submission value
                $existingResult = ScanResult::where('submission_value_id', $value->id)->first();
                
                if ($existingResult && !$forceScan) {
                    $this->line("Skipping file ID {$value->id} (filename: " . basename($value->value) . ") for submission {$submission->id} - already scanned. Use --force to re-scan.");
                    continue;
                }

                $filename = basename($value->value);
                $filePathInStorage = $value->value; // This should be the path relative to the storage disk ('private')
                
                if (!Storage::disk('private')->exists($filePathInStorage)) {
                    $this->warn("File not found in private storage: {$filePathInStorage} for submission value ID {$value->id}. Skipping.");
                    continue;
                }
                
                $fullPath = Storage::disk('private')->path($filePathInStorage);
                $mimeType = Storage::disk('private')->mimeType($filePathInStorage);
                
                $this->info("Scanning: {$filename} (SubmissionValue ID: {$value->id})");
                
                $uploadedFile = new UploadedFile(
                    $fullPath,
                    $filename,
                    $mimeType,
                    null, // No error
                    true  // Mark as test to prevent moving
                );
                
                $scanResultData = $scanService->scanFile($uploadedFile);
                $scannedFilesCount++;
                
                if ($scanResultData['success']) {
                    if ($existingResult) { // This implies --force was used
                        $existingResult->update([
                            'is_malicious' => $scanResultData['is_malicious'],
                            'scan_results' => $scanResultData['scan_results'],
                            'scanner_used' => 'pandora', // Ensure scanner_used is updated if it could change
                            'filename' => $filename, // Ensure filename is updated if it could change
                        ]);
                        $this->info("Updated scan result: " . ($scanResultData['is_malicious'] ? 'MALICIOUS' : 'CLEAN'));
                        $updatedResultsCount++;
                    } else {
                        ScanResult::create([
                            'submission_id' => $submission->id,
                            'submission_value_id' => $value->id,
                            'is_malicious' => $scanResultData['is_malicious'],
                            'scan_results' => $scanResultData['scan_results'],
                            'scanner_used' => 'pandora',
                            'filename' => $filename,
                        ]);
                        $this->info("Created scan result: " . ($scanResultData['is_malicious'] ? 'MALICIOUS' : 'CLEAN'));
                        $createdResultsCount++;
                    }
                } else {
                    $this->error("Scan failed for {$filename}: {$scanResultData['message']}");
                    Log::error("CLI Scan failed for {$filename} (SubmissionValue ID: {$value->id})", [
                        'error' => $scanResultData['message']
                    ]);
                    $failedScansCount++;
                }
            }
        }
        
        $this->info('----------------------------------------');
        $this->info('Scanning Summary:');
        $this->info("Total submissions processed: " . count($submissions));
        $this->info("Total files scanned: {$scannedFilesCount}");
        $this->info("New scan results created: {$createdResultsCount}");
        $this->info("Existing scan results updated (due to --force): {$updatedResultsCount}");
        $this->info("Failed scans: {$failedScansCount}");
        $this->info('Scanning completed.');
        return Command::SUCCESS;
    }
}
