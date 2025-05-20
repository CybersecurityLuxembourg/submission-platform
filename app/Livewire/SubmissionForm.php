<?php

namespace App\Livewire;

use App\Models\Form;
use App\Models\Submission;
use App\Models\ScanResult;
use App\Services\FileScanService;
use App\Models\SubmissionValues;
use App\Models\FormField;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Http\UploadedFile as IlluminateUploadedFile;

class SubmissionForm extends Component
{
    use WithFileUploads;

    /**
     * The form being submitted
     */
    public Form $form;

    /**
     * The current submission instance
     */
    public ?Submission $submission = null;

    /**
     * Array of field values indexed by field ID
     * @var array<int, mixed>
     */
    public array $fieldValues = [];

    /**
     * Array of temporary file uploads
     * @var array<string, mixed>
     */
    public array $tempFiles = [];

    /**
     * Current step in the multi-step form
     */
    public int $currentStep = 1;

    /**
     * Total number of steps in the form
     */
    public int $totalSteps;

    /**
     * Array of step data
     * @var array<int, array{name: string, description: string}>
     */
    public array $steps = [];

    /**
     * Whether the form is in edit mode
     */
    public bool $isEditMode = false;

    /**
     * Auto-save interval in milliseconds
     */
    public int $autoSaveInterval = 30000;

    /**
     * Maximum file size in KB
     */
    protected const MAX_FILE_SIZE = 10240;

    /**
     * Allowed file types
     * @var array<string>
     */
    protected const ALLOWED_FILE_TYPES = ['jpeg','jpg','webp','svg','png', 'pdf', 'doc', 'docx', 'xls', 'xlsx','md'];

    /**
     * Event listeners
     * @var array<string>
     */
    protected $listeners = [
        'autosaveDraft',
        'updateSubmissionStatus'
    ];

    /**
     * Validation messages
     * @var array<string, string>
     */
    protected $messages = [
        'fieldValues.*.required' => 'This field is required.',
        'tempFiles.*.max' => 'The file must not be larger than 10MB.',
        'tempFiles.*.mimes' => 'The file must be a valid document type (jpeg, png, pdf, doc, docx, xls, xlsx).',
    ];

    /**
     * Initialize the component
     */
    public function mount(Form $form, ?Submission $submission = null, bool $isEditMode = false): void
    {
        Log::info('Mounting SubmissionForm', [
            'form_id' => $form->id,
            'submission_id' => $submission?->id,
            'is_edit_mode' => $isEditMode
        ]);

        $this->form = $form->load([
            'categories' => fn($query) => $query->orderBy('order'),
            'categories.fields' => fn($query) => $query->orderBy('order')
        ]);

        $this->totalSteps = $this->form->categories->count();
        $this->isEditMode = $isEditMode;

        $this->steps = $this->form->categories->map(function ($category) {
            return [
                'name' => $category->name,
                'description' => $category->description
            ];
        })->toArray();

        if ($submission) {
            $this->submission = $submission;
            $this->loadSubmissionValues();
        } else {
            $this->loadOrCreateDraft();
        }
        
        // We don't need to process checkbox values here as it would convert arrays back to strings
        // which breaks the UI state for checkboxes
    }

    /**
     * Get data for the current step
     * @return array{name: string, description: string}
     */
    public function getCurrentStepDataProperty(): array
    {
        return $this->steps[$this->currentStep - 1] ?? [
            'name' => 'Step ' . $this->currentStep,
            'description' => ''
        ];
    }

    /**
     * Get field labels for validation
     * @return array<string, string>
     */
    protected function fieldLabels(): array
    {
        $labels = [];
        foreach ($this->form->categories as $category) {
            foreach ($category->fields as $field) {
                $labels["fieldValues.{$field->id}"] = $field->label;
                $labels["tempFiles.field_{$field->id}"] = $field->label;
            }
        }
        return $labels;
    }

    /**
     * Load values from an existing submission
     */
    protected function loadSubmissionValues(): void
    {
        if (!$this->submission) {
            return;
        }

        $this->submission->load('values');
        
        foreach ($this->submission->values as $value) {
            $field = null;
            
            // Find the field across all categories
            foreach ($this->form->categories as $category) {
                $foundField = $category->fields->firstWhere('id', $value->form_field_id);
                if ($foundField) {
                    $field = $foundField;
                    break;
                }
            }
            
            if (!$field) {
                // Field not found, skip this value
                Log::warning('Field not found when loading submission values', [
                    'form_field_id' => $value->form_field_id,
                    'submission_id' => $this->submission->id
                ]);
                continue;
            }
            
            if ($field->type === 'checkbox') {
                // Log the values for debugging
                Log::info('Loading checkbox values', [
                    'field_id' => $field->id,
                    'field_options' => $field->options,
                    'stored_value' => $value->value
                ]);
                
                // Convert stored comma-separated values back to array format for checkboxes
                $options = array_map('trim', explode(',', $field->options));
                
                // Handle empty values
                if (empty($value->value)) {
                    $this->fieldValues[$field->id] = array_fill(0, count($options), false);
                    continue;
                }
                
                $selectedValues = array_map('trim', explode(',', $value->value));
                
                $this->fieldValues[$field->id] = [];
                
                foreach ($options as $index => $option) {
                    $this->fieldValues[$field->id][$index] = in_array($option, $selectedValues);
                }
                
                // Log the resulting array for debugging
                Log::info('Checkbox array created', [
                    'field_id' => $field->id,
                    'result' => $this->fieldValues[$field->id]
                ]);
            } else {
                $this->fieldValues[$value->form_field_id] = $value->value;
            }
        }
    }

    /**
     * Load existing draft or create new one
     */
    protected function loadOrCreateDraft(): void
    {
        if (!auth()->check()) {
            return;
        }

        $this->submission = Submission::where([
            'form_id' => $this->form->id,
            'user_id' => auth()->id(),
        ])->whereIn('status', ['draft', 'ongoing'])
            ->orderBy('last_activity', 'desc')
            ->first();

        if ($this->submission) {
            $this->loadSubmissionValues();
        } else {
            $this->submission = new Submission([
                'form_id' => $this->form->id,
                'user_id' => auth()->id(),
                'status' => 'draft',
                'last_activity' => now(),
            ]);
        }
    }

    /**
     * Handle file upload updates
     */
    public function updatedTempFiles($value, $key): void
    {
        $fieldId = str_replace('field_', '', $key);

        try {
            $path = $value->store('temp-submissions', 'private');
            $this->fieldValues[$fieldId] = $path;
            $this->dispatch('success', 'File uploaded successfully');
        } catch (Exception $e) {
            Log::error('File upload failed', [
                'error' => $e->getMessage(),
                'field_id' => $fieldId
            ]);
            $this->dispatch('error', 'Failed to upload file: ' . $e->getMessage());
        }
    }

    /**
     * Delete uploaded file
     */
    public function deleteFile(int $fieldId): void
    {
        if (isset($this->fieldValues[$fieldId])) {
            try {
                Storage::disk('private')->delete($this->fieldValues[$fieldId]);
                unset($this->fieldValues[$fieldId]);
                $this->dispatch('success', 'File deleted successfully');
            } catch (Exception $e) {
                Log::error('File deletion failed', [
                    'error' => $e->getMessage(),
                    'field_id' => $fieldId
                ]);
                $this->dispatch('error', 'Failed to delete file: ' . $e->getMessage());
            }
        }
    }

    /**
     * Auto-save draft
     * @throws Exception
     */
    public function autosaveDraft(): void
    {
        if (!auth()->check()) {
            return;
        }
        $this->saveDraft(true);
    }

    /**
     * Save as draft
     * @throws Exception
     */
    public function saveAsDraft(): void
    {
        if (!auth()->check()) {
            return;
        }
        $this->saveDraft(true);
    }

    /**
     * Save the current submission as a draft
     *
     * @param bool $showNotification Whether to show a success notification
     */
    protected function saveDraft(bool $showNotification = true): void
    {
        try {
            DB::beginTransaction();

            if (!$this->submission->exists) {
                $this->submission->form_id = $this->form->id;
                $this->submission->status = 'draft';
                $this->submission->last_activity = now();

                if (auth()->check()) {
                    $this->submission->user_id = auth()->id();
                }

                $this->submission->save();
            } else {
                $this->submission->update([
                    'last_activity' => now(),
                ]);
            }
            
            // Deep clone the fieldValues to avoid reference issues
            $originalFieldValues = [];
            foreach ($this->fieldValues as $key => $value) {
                if (is_array($value)) {
                    $originalFieldValues[$key] = array_merge([], $value);
                } else {
                    $originalFieldValues[$key] = $value;
                }
            }
            
            // Save checkbox values as they are (they're already arrays in the UI)
            foreach ($this->fieldValues as $fieldId => $value) {
                if (is_array($value)) {
                    // This is likely a checkbox field, preserve the array values in the session
                    // but store a string representation in the database
                    
                    // Get the field to access its options
                    $field = null;
                    foreach ($this->form->categories as $category) {
                        $foundField = $category->fields->firstWhere('id', $fieldId);
                        if ($foundField && $foundField->type === 'checkbox') {
                            $field = $foundField;
                            break;
                        }
                    }
                    
                    if ($field) {
                        // Convert the checkbox array to a readable string for storage
                        $selectedOptions = [];
                        $options = explode(',', $field->options);
                        
                        foreach ($value as $index => $isChecked) {
                            if ($isChecked && isset($options[$index])) {
                                $selectedOptions[] = trim($options[$index]);
                            }
                        }
                        
                        $valueForStorage = !empty($selectedOptions) ? implode(', ', $selectedOptions) : null;
                        
                        // Store the string representation in the database
                        $this->submission->values()->updateOrCreate(
                            ['form_field_id' => $fieldId],
                            ['value' => $valueForStorage]
                        );
                    }
                } else {
                    // For non-array values, store as is
                    $this->submission->values()->updateOrCreate(
                        ['form_field_id' => $fieldId],
                        ['value' => $value]
                    );
                }
            }
            
            // Handle any file uploads
            $this->handleFileUploads();
            
            // Keep the array representation in the UI
            $this->fieldValues = $originalFieldValues;

            DB::commit();

            if ($showNotification) {
                $this->dispatch('success', 'Draft saved');
            }
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Draft save failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Process and format any checkbox values before saving
     */
    protected function processCheckboxValues(): void
    {
        $this->form->categories->each(function ($category) {
            $category->fields->where('type', 'checkbox')->each(function ($field) {
                if (isset($this->fieldValues[$field->id]) && is_array($this->fieldValues[$field->id])) {
                    // Convert checkbox array to a comma-separated string of selected values
                    $selectedOptions = [];
                    foreach ($this->fieldValues[$field->id] as $index => $value) {
                        if ($value) {
                            $options = explode(',', $field->options);
                            $selectedOptions[] = trim($options[$index]);
                        }
                    }
                    $this->fieldValues[$field->id] = !empty($selectedOptions) ? implode(', ', $selectedOptions) : null;
                }
            });
        });
    }

    /**
     * Save form field values to the database
     */
    protected function saveValues(): void
    {
        $this->processCheckboxValues();
        
        foreach ($this->fieldValues as $fieldId => $value) {
            $this->submission->values()->updateOrCreate(
                ['form_field_id' => $fieldId],
                ['value' => $value]
            );
        }
    }

    /**
     * Move to next step
     */
    public function nextStep(): void
    {
        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep++;
        }
    }

    /**
     * Move to previous step
     */
    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    /**
     * Submit the form
     */
    public function submit(): void
    {
        try {
            // Validate all form data
            $this->validate($this->getValidationRules());
            
            DB::beginTransaction();
            
            if (!$this->submission) {
                $this->submission = new Submission([
                    'form_id' => $this->form->id,
                    'user_id' => auth()->id(),
                    'status' => 'submitted',
                    'submitted_at' => now(),
                ]);
                $this->submission->save();
            } else {
                $this->submission->status = 'submitted';
                $this->submission->submitted_at = now();
                $this->submission->save();
            }
            
            // Store submission values
            foreach ($this->fieldValues as $fieldId => $value) {
                if (is_array($value)) {
                    // This is likely a checkbox field
                    // Get the field to access its options
                    $field = null;
                    foreach ($this->form->categories as $category) {
                        $foundField = $category->fields->firstWhere('id', $fieldId);
                        if ($foundField && $foundField->type === 'checkbox') {
                            $field = $foundField;
                            break;
                        }
                    }
                    
                    if ($field) {
                        // Convert the checkbox array to a readable string for storage
                        $selectedOptions = [];
                        $options = explode(',', $field->options);
                        
                        foreach ($value as $index => $isChecked) {
                            if ($isChecked && isset($options[$index])) {
                                $selectedOptions[] = trim($options[$index]);
                            }
                        }
                        
                        $valueForStorage = !empty($selectedOptions) ? implode(', ', $selectedOptions) : null;
                        
                        // Store the string representation in the database
                        $this->submission->values()->updateOrCreate(
                            ['form_field_id' => $fieldId],
                            ['value' => $valueForStorage]
                        );
                    }
                } else {
                    // For non-array values, store as is
                    $this->submission->values()->updateOrCreate(
                        ['form_field_id' => $fieldId],
                        ['value' => $value]
                    );
                }
            }
            
            // Handle file uploads
            $this->handleFileUploads();

            DB::commit();

            $this->redirect(route('submissions.thankyou'));
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Submission failed', [
                'error' => $e->getMessage(),
                'submission_id' => $this->submission?->id ?? null,
                'authenticated' => auth()->check(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Handle permanent file storage after submission
     */
    protected function handleFileUploads(?FileScanService $scanService = null): void
    {
        // If no scan service was provided, try to resolve it from the container
        if (!$scanService) {
            $scanService = app(FileScanService::class);
        }
        
        foreach ($this->fieldValues as $fieldId => $value) {
            // Skip if value is not a string (e.g., arrays from checkboxes)
            if (!is_string($value)) {
                continue;
            }

            // Check if this field is actually a file type and the value looks like a temp path
            $fieldModel = FormField::find($fieldId);
            if (!$fieldModel || $fieldModel->type !== 'file') {
                continue;
            }
            
            if (str_starts_with($value, 'temp-submissions/')) {
                $newPath = "submissions/{$this->submission->id}/" . basename($value);
                Storage::disk('private')->move($value, $newPath);

                // Update the submission value with the new path
                $submissionValue = $this->submission->values()->updateOrCreate(
                    ['form_field_id' => $fieldId],
                    ['value' => $newPath]
                );

                $this->fieldValues[$fieldId] = $newPath; // Update component state

                // After successfully moving the file, scan it
                if (config('services.pandora.enabled', true)) {
                    $fullStoragePath = Storage::disk('private')->path($newPath);
                    $originalName = basename($newPath); // Or get original name if stored elsewhere
                    $mimeType = Storage::disk('private')->mimeType($newPath);

                    // Create an Illuminate\Http\UploadedFile instance for the scan service
                    $uploadedFileForScan = new IlluminateUploadedFile(
                        $fullStoragePath,
                        $originalName,
                        $mimeType,
                        null, // Error code, null for no error
                        true // Set to true to indicate this is a test file (prevents move attempts)
                    );

                    Log::info('Scanning file after upload', [
                        'submission_id' => $this->submission->id,
                        'submission_value_id' => $submissionValue->id,
                        'filename' => $originalName,
                        'path' => $newPath
                    ]);

                    $scanResultData = $scanService->scanFile($uploadedFileForScan);

                    if ($scanResultData['success']) {
                        // Store scan results
                        ScanResult::create([
                            'submission_id' => $this->submission->id,
                            'submission_value_id' => $submissionValue->id,
                            'is_malicious' => $scanResultData['is_malicious'],
                            'scan_results' => $scanResultData['scan_results'],
                            'scanner_used' => 'pandora',
                            'filename' => $originalName,
                        ]);

                        // If the file is malicious and we're configured to block, we need to handle this
                        // Since this happens after the file is stored, we'll need to delete it and notify the user
                        if ($scanResultData['is_malicious'] && config('services.pandora.block_malicious', true)) {
                            Log::warning('Detected malicious file after upload, removing', [
                                'submission_id' => $this->submission->id,
                                'filename' => $originalName,
                            ]);
                            
                            // Remove the file
                            Storage::disk('private')->delete($newPath);
                            
                            // You might want to update the submission value to indicate the file was removed
                            $submissionValue->update(['value' => '[REMOVED-MALICIOUS]: ' . $originalName]);
                            
                            // In a real implementation, you might want to:
                            // 1. Notify the user via email
                            // 2. Add a system message to the submission
                            // 3. Flag the submission for review
                        }
                    }
                }
            }
        }
    }

    /**
     * Get all validation rules
     * @return array<string, string>
     */
    public function rules(): array
    {
        $rules = [];

        foreach ($this->form->categories as $category) {
            foreach ($category->fields as $field) {
                $fieldRules = [];

                // Add required rule if field is required
                if ($field->required) {
                    $fieldRules[] = 'required';
                } else {
                    $fieldRules[] = 'nullable';
                }

                // Add type-specific rules
                switch ($field->type) {
                    case 'text':
                    case 'textarea':
                        $fieldRules[] = 'string';
                        if (!empty($field->char_limit)) {
                            $fieldRules[] = "max:{$field->char_limit}";
                        }
                        break;

                    case 'select':
                    case 'radio':
                        $fieldRules[] = 'string';
                        if (!empty($field->options)) {
                            $fieldRules[] = 'in:' . implode(',', array_keys($field->options));
                        }
                        break;

                    case 'checkbox':
                        $fieldRules[] = 'array';
                        if (!empty($field->options)) {
                            $fieldRules[] = 'in:' . implode(',', array_keys($field->options));
                        }
                        break;

                    case 'file':
                        $fieldRules[] = 'file';
                        $fieldRules[] = 'max:' . self::MAX_FILE_SIZE;
                        $fieldRules[] = 'mimes:' . implode(',', self::ALLOWED_FILE_TYPES);
                        break;
                }

                // Add rules for field values
                $rules["fieldValues.{$field->id}"] = $fieldRules;

                // Add rules for file uploads
                if ($field->type === 'file') {
                    $rules["tempFiles.field_{$field->id}"] = $fieldRules;
                }
            }
        }

        return $rules;
    }

    /**
     * Render the component
     */
    public function render(): View|Factory|Application
    {
        return view('livewire.submission-form');
    }

    /**
     * Debug method to log checkbox state
     */
    public function debugCheckboxes(): void
    {
        Log::info('Current fieldValues state', [
            'fieldValues' => $this->fieldValues
        ]);
        
        // Find all checkbox fields
        $checkboxFields = [];
        foreach ($this->form->categories as $category) {
            foreach ($category->fields->where('type', 'checkbox') as $field) {
                $checkboxFields[$field->id] = [
                    'label' => $field->label,
                    'options' => $field->options,
                    'value' => $this->fieldValues[$field->id] ?? null
                ];
            }
        }
        
        Log::info('Checkbox fields', [
            'checkboxFields' => $checkboxFields
        ]);
    }
}
