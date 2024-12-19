<?php

namespace App\Livewire;

use App\Models\Form;
use App\Models\Submission;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SubmissionForm extends Component
{
    use WithFileUploads;

    public Form $form;
    public ?Submission $submission = null;
    public array $fieldValues = [];
    public array $tempFiles = [];
    public int $currentStep = 1;
    public $totalSteps;
    public $steps = [];
    public bool $isEditMode = false;
    public $autoSaveInterval = 30000; // 30 seconds

    protected $listeners = [
        'autosaveDraft',
        'updateSubmissionStatus'
    ];

    public function mount(Form $form, ?Submission $submission = null, bool $isEditMode = false): void
    {
        $this->form = $form->load([
            'categories' => fn($query) => $query->orderBy('order'),
            'categories.fields' => fn($query) => $query->orderBy('order')
        ]);

        $this->totalSteps = $this->form->categories->count();
        $this->isEditMode = $isEditMode;

        // Prepare steps data for progress bar
        $this->steps = $this->form->categories->map(function($category) {
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
    }

    public function getCurrentStepDataProperty()
    {
        return $this->steps[$this->currentStep - 1] ?? [
            'name' => 'Step ' . $this->currentStep,
            'description' => ''
        ];
    }

    protected function loadSubmissionValues(): void
    {
        if (!$this->submission) {
            return;
        }
        $this->submission->load('values');
        foreach ($this->submission->values as $value) {
            $this->fieldValues[$value->form_field_id] = $value->value;
        }
    }

    protected function loadOrCreateDraft(): void
    {
        if (!auth()->check()) {
            return;
        }

        $this->submission = Submission::where([
            'form_id' => $this->form->id,
            'user_id' => auth()->id(),
        ])->whereIn('status', ['draft', 'ongoing'])
            ->with('values')
            ->first();

        if ($this->submission) {
            $this->loadSubmissionValues();
        }
    }

    public function autosaveDraft(): void
    {
        $this->saveDraft(false);
    }

    public function saveAsDraft(): void
    {
        $this->saveDraft(true);
    }

    /**
     * @throws Exception
     */
    protected function saveDraft($showNotification = true): void
    {
        if (!auth()->check()) {
            $this->dispatch('error', 'You must be logged in to save drafts.');
            return;
        }

        try {
            \DB::beginTransaction();

            logger()->debug('Starting saveDraft', [
                'submission_exists' => isset($this->submission),
                'submission_is_null' => is_null($this->submission),
                'form_id' => $this->form->id,
                'user_id' => auth()->id(),
                'field_values' => $this->fieldValues
            ]);

            // Changed condition to explicitly check for null
            if (is_null($this->submission)) {
                $this->submission = new Submission([
                    'form_id' => $this->form->id,
                    'user_id' => auth()->id(),
                    'status' => 'draft',
                    'last_activity' => now(),
                ]);
                $this->submission->save();

                logger()->debug('Created new submission', [
                    'new_submission_id' => $this->submission->id
                ]);
            } else if ($this->submission->id) {
                // Only try to update if we have a valid ID
                $this->submission = Submission::find($this->submission->id);
                if ($this->submission) {
                    $this->submission->update([
                        'status' => 'draft',
                        'last_activity' => now(),
                    ]);

                    logger()->debug('Updated existing submission', [
                        'updated_submission_id' => $this->submission->id
                    ]);
                } else {
                    // If we can't find the submission, create a new one
                    $this->submission = new Submission([
                        'form_id' => $this->form->id,
                        'user_id' => auth()->id(),
                        'status' => 'draft',
                        'last_activity' => now(),
                    ]);
                    $this->submission->save();
                }
            } else {
                // If we have a submission object but no ID, create a new one
                $this->submission = new Submission([
                    'form_id' => $this->form->id,
                    'user_id' => auth()->id(),
                    'status' => 'draft',
                    'last_activity' => now(),
                ]);
                $this->submission->save();
            }

            // Ensure we have a valid submission before proceeding
            if (!$this->submission->id) {
                throw new \RuntimeException('Failed to create/load submission');
            }

            $this->saveValues();

            \DB::commit();

            if ($showNotification) {
                $this->dispatch('draft-saved');
            }

        } catch (\Exception $e) {
            \DB::rollBack();
            logger()->error('Failed to save draft', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'submission_state' => [
                    'exists' => isset($this->submission),
                    'is_null' => is_null($this->submission),
                    'id' => $this->submission->id ?? null
                ]
            ]);
            $this->dispatch('error', 'Failed to save draft: ' . $e->getMessage());
            throw $e;
        }
    }

        /**
     * @throws Exception
     */
    protected function saveValues(): void
    {
        logger()->debug('Starting saveValues', [
            'submission_exists' => isset($this->submission),
            'submission_id' => $this->submission->id ?? null,
            'field_values_count' => count($this->fieldValues)
        ]);

        if (!$this->submission || !$this->submission->id) {
            throw new \RuntimeException('Cannot save values without a valid submission');
        }

        foreach ($this->fieldValues as $fieldId => $value) {
            try {
                $result = $this->submission->values()->updateOrCreate(
                    [
                        'form_field_id' => $fieldId,
                        'submission_id' => $this->submission->id
                    ],
                    ['value' => $value]
                );

                logger()->debug('Saved field value', [
                    'field_id' => $fieldId,
                    'submission_id' => $this->submission->id,
                    'success' => true
                ]);
            } catch (\Exception $e) {
                logger()->error('Failed to save field value', [
                    'field_id' => $fieldId,
                    'submission_id' => $this->submission->id,
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
        }
    }

    public function hydrate(): void
    {
        if ($this->submission) {
            logger()->debug('Hydrating component', [
                'submission_id' => $this->submission->id,
                'field_values_count' => count($this->fieldValues)
            ]);
        }
    }

    public function nextStep(): void
    {
        $this->validateCurrentStep();
        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep++;
            $this->saveDraft(false);
        }
    }

    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function validateCurrentStep(): void
    {
        $this->validate($this->rules());
    }


    public function submit(): void  // Changed return type to void
    {
        $this->validateCurrentStep();
        $this->validateAllSteps();

        if ($this->submission) {
            $this->handleFileUploads();

            $this->submission->update([
                'status' => 'submitted',
                'last_activity' => now(),
                'status_metadata' => [
                    'submitted_at' => now(),
                    'submission_ip' => request()->ip(),
                    'completion_time' => $this->submission->created_at->diffInMinutes(now()),
                    'edited' => $this->isEditMode,
                ]
            ]);

            $this->redirect(route('submissions.thankyou')); // Changed to Livewire's redirect
        }
    }

    public function validateAllSteps(): void
    {
        $rules = [];
        foreach ($this->form->categories as $category) {
            foreach ($category->fields as $field) {
                if ($field->required) {
                    $rules["fieldValues.{$field->id}"] = 'required';
                    if ($field->type === 'file') {
                        if (!isset($this->fieldValues[$field->id]) || empty($this->fieldValues[$field->id])) {
                            $rules["tempFiles.field_{$field->id}"] = 'required|file|max:10240|mimes:jpeg,png,pdf,doc,docx,xls,xlsx';
                        }
                    }
                }
            }
        }

        $this->validate($rules);
    }

    public function updatedTempFiles($value, $key): void
    {
        try {
            // Ensure we have a submission context before proceeding
            if (!$this->submission) {
                logger()->debug('Creating new draft submission for file upload');

                // Create new draft submission without checking for existing ones
                $this->submission = Submission::create([
                    'form_id' => $this->form->id,
                    'user_id' => auth()->id(),
                    'status' => 'draft',
                    'last_activity' => now(),
                ]);
                logger()->debug('Created new draft submission', ['submission_id' => $this->submission->id]);
            }

            $fieldId = str_replace('field_', '', $key);

            // Rest of the file handling code remains the same
            $path = $value->store("temp-submissions/{$this->submission->id}", 'private');

            // Delete old file if it exists
            if (isset($this->fieldValues[$fieldId])) {
                $oldPath = $this->fieldValues[$fieldId];
                if (Storage::disk('private')->exists($oldPath)) {
                    Storage::disk('private')->delete($oldPath);
                }
            }

            // Update the field value with the new path
            $this->fieldValues[$fieldId] = $path;

            // Update the value immediately
            $this->submission->values()->updateOrCreate(
                ['form_field_id' => $fieldId],
                ['value' => $path]
            );

            $this->dispatch('success', 'File uploaded successfully');

        } catch (\Exception $e) {
            logger()->error('File upload failed', [
                'error' => $e->getMessage(),
                'field_id' => $fieldId ?? null,
                'submission_state' => [
                    'exists' => isset($this->submission),
                    'id' => $this->submission->id ?? null
                ]
            ]);
            $this->dispatch('error', 'File upload failed: ' . $e->getMessage());
        }
    }

    public function deleteFile($fieldId): void
    {
        if (!isset($this->fieldValues[$fieldId])) {
            return;
        }

        $value = $this->fieldValues[$fieldId];

        if (Storage::disk('private')->exists($value)) {
            Storage::disk('private')->delete($value);
        }

        unset($this->fieldValues[$fieldId]);

        if ($this->submission) {
            $this->submission->values()
                ->where('form_field_id', $fieldId)
                ->delete();
        }

        $this->dispatch('success', 'File deleted successfully');
    }

    protected function handleFileUploads(): void
    {
        if (!$this->submission) {
            return;
        }

        foreach ($this->fieldValues as $fieldId => $value) {
            // Only process paths that start with temp-submissions
            if (str_starts_with($value, 'temp-submissions/') && Storage::disk('private')->exists($value)) {
                $newPath = "submissions/{$this->submission->id}/" . basename($value);

                // Create directory if it doesn't exist
                Storage::disk('private')->makeDirectory("submissions/{$this->submission->id}");

                // Move file from temp to permanent location
                Storage::disk('private')->move($value, $newPath);

                $this->submission->values()
                    ->updateOrCreate(
                        ['form_field_id' => $fieldId],
                        ['value' => $newPath]
                    );

                $this->fieldValues[$fieldId] = $newPath;
            }
        }
    }


    public function rules(): array
    {
        $currentCategory = $this->form->categories[$this->currentStep - 1] ?? null;
        if (!$currentCategory) {
            return [];
        }

        $rules = [];
        foreach ($currentCategory->fields as $field) {
            if ($field->required) {
                $rules["fieldValues.{$field->id}"] = 'required';
                if ($field->type === 'file') {
                    if (!isset($this->fieldValues[$field->id]) || empty($this->fieldValues[$field->id])) {
                        $rules["tempFiles.field_{$field->id}"] = 'required|file|max:10240|mimes:jpeg,png,pdf,doc,docx,xls,xlsx';
                    }
                }
            }
        }

        return $rules;
    }
    public function render(): View|Factory|Application
    {
        return view('livewire.submission-form');
    }
}
