<?php

namespace App\Livewire;

use App\Models\Form;
use App\Models\Submission;
use Illuminate\Contracts\View\Factory;use Illuminate\Contracts\View\View;use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class SubmissionForm extends Component
{
    use WithFileUploads;

    public Form $form;
    public ?Submission $draftSubmission = null;
    public array $fieldValues = [];
    public array $tempFiles = [];
    public int $currentStep = 1;
    public $totalSteps;

    protected $listeners = ['saveAndContinue'];

    public function mount(Form $form): void
    {
        $this->form = $form->load([
            'categories' => fn($query) => $query->orderBy('order'),
            'categories.fields' => fn($query) => $query->orderBy('order')
        ]);

        $this->totalSteps = $this->form->categories->count();

        // Load existing draft if it exists
        $this->loadDraft();
    }

    public function loadDraft(): void
    {
        $this->draftSubmission = Submission::where([
            'form_id' => $this->form->id,
            'user_id' => auth()->id(),
            'status' => 'draft'
        ])->with('values')->first();

        if ($this->draftSubmission) {
            foreach ($this->draftSubmission->values as $value) {
                $this->fieldValues[$value->form_field_id] = $value->value;
            }
        }
    }

    public function updatedFieldValues($value, $key)
    {
        $this->saveDraft();
    }

    public function updatedTempFiles($value, $key)
    {
        if (!$value) return;

        $fieldId = str_replace('field_', '', $key);
        $path = $value->store('temp-submissions', 'private');
        $this->fieldValues[$fieldId] = $path;
        $this->saveDraft();
    }

    public function saveDraft()
    {
        if (!$this->draftSubmission) {
            $this->draftSubmission = $this->form->submissions()->create([
                'user_id' => auth()->id(),
                'status' => 'draft',
                'last_edited_at' => now(),
            ]);
        } else {
            $this->draftSubmission->update(['last_edited_at' => now()]);
        }

        foreach ($this->fieldValues as $fieldId => $value) {
            $this->draftSubmission->values()->updateOrCreate(
                ['form_field_id' => $fieldId],
                ['value' => $value]
            );
        }

        $this->emit('draftSaved');
    }

    public function nextStep(): void
    {
        $this->validateCurrentStep();
        $this->currentStep++;
        $this->saveDraft();
    }

    public function previousStep(): void
    {
        $this->currentStep--;
    }

    public function validateCurrentStep(): void
    {
        $currentCategory = $this->form->categories[$this->currentStep - 1];
        $rules = [];

        foreach ($currentCategory->fields as $field) {
            if ($field->required) {
                $rules["fieldValues.{$field->id}"] = 'required';
                if ($field->type === 'file') {
                    $rules["tempFiles.field_{$field->id}"] = 'required|file|max:10240|mimes:jpeg,png,pdf,doc,docx,xls,xlsx';
                }
            }
        }

        $this->validate($rules);
    }

    public function submit(): RedirectResponse
    {
        $this->validateCurrentStep();

        if ($this->draftSubmission) {
            // Move any temporary files to permanent storage
            foreach ($this->fieldValues as $fieldId => $value) {
                if (Storage::disk('private')->exists("temp-submissions/$value")) {
                    $newPath = "submissions/{$this->draftSubmission->id}/" . basename($value);
                    Storage::disk('private')->move("temp-submissions/$value", $newPath);
                    $this->draftSubmission->values()
                        ->where('form_field_id', $fieldId)
                        ->update(['value' => $newPath]);
                }
            }

            // Update submission status
            $this->draftSubmission->update([
                'status' => 'submitted',
                'last_edited_at' => now()
            ]);
        }

        return redirect()->route('submissions.thankyou');
    }

    public function render():View|Factory|Application
    {
        return view('livewire.forms.submission-form');
    }
}
