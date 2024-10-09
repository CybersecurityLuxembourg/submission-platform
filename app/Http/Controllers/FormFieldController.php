<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\FormField;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FormFieldController extends Controller
{

    use AuthorizesRequests;
    /**
     * Store a newly created form field in storage.
     */
    public function store(Request $request, Form $form): RedirectResponse
    {
       # $this->authorize('update', $form);

        $request->validate([
            'label' => 'required|max:255',
            'type' => 'required|in:text,textarea,select,checkbox,radio',
            'options' => 'nullable',
            'required' => 'boolean',
            'order' => 'integer',
        ]);

        $form->fields()->create($request->all());

        return redirect()->route('forms.edit', $form)->with('success', 'Field added successfully.');
    }

    /**
     * Update the specified form field in storage.
     */
    public function update(Request $request, Form $form, FormField $field): RedirectResponse
    {
        #$this->authorize('update', $form);

        $request->validate([
            'label' => 'required|max:255',
            'type' => 'required|in:text,textarea,select,checkbox,radio',
            'options' => 'nullable',
            'required' => 'boolean',
            'order' => 'integer',
        ]);

        $field->update($request->all());

        return redirect()->route('forms.edit', $form)->with('success', 'Field updated successfully.');
    }

    /**
     * Remove the specified form field from storage.
     */
    public function destroy(Form $form, FormField $field): RedirectResponse
    {
      #  $this->authorize('update', $form);

        $field->delete();

        return redirect()->route('forms.edit', $form)->with('success', 'Field deleted successfully.');
    }
}
