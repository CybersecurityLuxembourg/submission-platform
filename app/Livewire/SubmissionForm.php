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

    public function mount(Form $form, ?Submission $submission = null, bool $isEditMode = false): void
    {
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

    protected function saveDraft($showNotification = true): void
    {
        if (!auth()->check()) {
            $this->dispatch('error', 'You must be logged in to save drafts.');
            return;
        }

        try {
            \DB::beginTransaction();

            if (!$this->submission) {
                $this->submission = Submission::create([
                    'form_id' => $this->form->id,
                    'user_id' => auth()->id(),
                    'status' => 'draft',
                    'last_activity' => now(),
                ]);
            } else {
                $this->submission->update([
                    'status' => 'draft',
                    'last_activity' => now(),
                ]);
            }

            $this->saveValues();
            \DB::commit();

            if ($showNotification) {
                $this->dispatch('draft-saved');
            }
        } catch (Exception $e) {
            \DB::rollBack();
            $this->dispatch('error', 'Failed to save draft: ' . $e->getMessage());
            throw $e;
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
