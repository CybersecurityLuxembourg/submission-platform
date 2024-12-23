<?php

namespace App\Livewire;

use App\Models\Form;
use App\Models\Submission;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
    public array $steps = [];
    public bool $isEditMode = false;
    public int $autoSaveInterval = 30000; // 30 seconds

    protected $listeners = [
        'autosaveDraft',
        'updateSubmissionStatus'
    ];

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
            ->orderBy('last_activity', 'desc')
            ->first();

        if ($this->submission) {
            $this->loadSubmissionValues();
        } else {
            // Initialize a new draft if none exists
            $this->submission = new Submission();
            $this->submission->form_id = $this->form->id;
            $this->submission->user_id = auth()->id();
            $this->submission->status = 'draft';
            $this->submission->last_activity = now();
        }
    }

    /**
     * @throws Exception
     */
    public function autosaveDraft(): void
    {
        $this->saveDraft(false);
    }

    /**
     * @throws Exception
     */
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
            DB::beginTransaction();

            // Log initial state
            Log::info('Starting draft save process', [
                'form_id' => $this->form->id,
                'user_id' => auth()->id(),
                'has_existing_submission' => isset($this->submission) && $this->submission->exists
            ]);

            // Create or update submission
            if (!$this->submission->exists) {
                // Validate required data
                if (!$this->form->id) {
                    throw new Exception('Form ID is missing');
                }

                if (!auth()->id()) {
                    throw new Exception('User ID is missing');
                }

                // Ensure required fields are set
                $this->submission->form_id = $this->form->id;
                $this->submission->user_id = auth()->id();
                $this->submission->status = 'draft';
                $this->submission->last_activity = now();


                // Save new submission
                $this->submission->save();

                Log::info('New draft created', [
                    'submission_id' => $this->submission->id,
                    'form_id' => $this->form->id,
                    'user_id' => auth()->id()
                ]);

            } else {
                // Update existing submission
                $this->submission->update([
                    'status' => 'draft',
                    'last_activity' => now()
                ]);

                Log::info('Existing draft updated', [
                    'submission_id' => $this->submission->id,
                    'form_id' => $this->form->id,
                    'user_id' => auth()->id()
                ]);
            }

            DB::commit();

            if ($showNotification) {
                $this->dispatch('success', 'Draft saved successfully.');
            }

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Draft save failed', [
                'error' => $e->getMessage(),
                'form_id' => $this->form->id,
                'user_id' => auth()->id()
            ]);

            throw new Exception('Failed to save draft: ' . $e->getMessage());
        }
    }

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
    }

    protected function saveValues(): void
    {
        foreach ($this->fieldValues as $fieldId => $value) {
            $this->submission->values()->updateOrCreate(
                ['form_field_id' => $fieldId],
                ['value' => $value]
            );
        }
    }

    public function nextStep(): void
    {
        if ($this->currentStep < $this->totalSteps) {
            //$this->validate($this->rulesForCurrentStep(), [], $this->fieldLabels());
            $this->currentStep++;
        }
    }

    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    protected function rulesForCurrentStep(): array
    {
        $rules = [];
        $currentCategory = $this->form->categories[$this->currentStep - 1] ?? null;

        if ($currentCategory) {
            foreach ($currentCategory->fields as $field) {
                if ($field->required) {
                    $rules["fieldValues.{$field->id}"] = 'required';
                }

                if (!empty($field->char_limit)) {
                    $rules["fieldValues.{$field->id}"] = "nullable|string|max:{$field->char_limit}";
                }

                if ($field->type === 'file') {
                    $rules["tempFiles.field_{$field->id}"] = 'nullable|file|max:10240|mimes:jpeg,png,pdf,doc,docx,xls,xlsx';
                }
            }
        }

        return $rules;
    }

    public function submit(): void
    {
        $this->validate($this->rules(), [], $this->fieldLabels());

        $this->submission->update([
            'status' => 'submitted',
            'last_activity' => now(),
        ]);

        $this->handleFileUploads();

        $this->redirect(route('submissions.thankyou'));
    }

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
                    $rules["tempFiles.field_{$field->id}"] = 'nullable|file|max:10240|mimes:jpeg,png,pdf,doc,docx,xls,xlsx';
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
