<?php

namespace App\Livewire;

use App\Models\Form;
use App\Models\Submission;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

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

        $this->processCheckboxValues();
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
            $field = $this->form->fields->find($value->form_field_id);
            
            if ($field && $field->type === 'checkbox') {
                // Convert stored comma-separated values back to array format for checkboxes
                $options = array_map('trim', explode(',', $field->options));
                $selectedValues = array_map('trim', explode(',', $value->value));
                
                $this->fieldValues[$field->id] = [];
                
                foreach ($options as $index => $option) {
                    $this->fieldValues[$field->id][$index] = in_array($option, $selectedValues);
                }
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

            $this->handleFileUploads();
            
            // Process checkbox values before saving the draft
            $this->processCheckboxValues();
            
            $this->saveValues();

            DB::commit();

            if ($showNotification) {
                $this->dispatch('success', 'Draft saved');
            }
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Draft save failed', [
                'error' => $e->getMessage(),
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
     * Handle form submission
     * @throws Exception
     */
    public function submit(): void
    {
        $this->validate($this->rules(), [], $this->fieldLabels());

        try {
            DB::beginTransaction();

            // Create new submission for non-authenticated users or if no draft exists
            if (!$this->submission || !$this->submission->exists) {
                $this->submission = new Submission([
                    'form_id' => $this->form->id,
                    'status' => 'submitted',
                    'last_activity' => now(),
                ]);

                if (auth()->check()) {
                    $this->submission->user_id = auth()->id();
                }

                $this->submission->save();
            } else {
                // Update existing submission (e.g., from draft)
                $this->submission->update([
                    'status' => 'submitted',
                    'last_activity' => now(),
                ]);
            }

            $this->handleFileUploads();
            
            // Process checkbox values before final submission
            $this->processCheckboxValues();
            
            $this->saveValues();

            DB::commit();

            $this->redirect(route('forms.user_index'));
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Submission failed', [
                'error' => $e->getMessage(),
                'submission_id' => $this->submission?->id ?? null,
                'authenticated' => auth()->check()
            ]);
            throw $e;
        }
    }

    /**
     * Handle permanent file storage after submission
     */
    protected function handleFileUploads(): void
    {
        foreach ($this->fieldValues as $fieldId => $value) {
            if (str_starts_with($value, 'temp-submissions/')) {
                $newPath = "submissions/{$this->submission->id}/" . basename($value);
                Storage::disk('private')->move($value, $newPath);

                $this->submission->values()->updateOrCreate(
                    ['form_field_id' => $fieldId],
                    ['value' => $newPath]
                );

                $this->fieldValues[$fieldId] = $newPath;
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
                if ($field->required) {
                    $rules["fieldValues.{$field->id}"] = 'required';
                }

                if (!empty($field->char_limit)) {
                    $rules["fieldValues.{$field->id}"] = "nullable|string|max:{$field->char_limit}";
                }

                if ($field->type === 'file') {
                    $rules["tempFiles.field_{$field->id}"] = sprintf(
                        'nullable|file|max:%d|mimes:%s',
                        self::MAX_FILE_SIZE,
                        implode(',', self::ALLOWED_FILE_TYPES)
                    );
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
}
