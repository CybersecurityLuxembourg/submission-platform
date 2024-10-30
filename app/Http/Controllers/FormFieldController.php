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
            'type' => 'required|in:text,textarea,select,checkbox,radio,header,description,file',
            'options' => 'nullable',
            'required' => 'boolean',
            'order' => 'integer',
            'char_limit' => 'nullable|integer|min:1',
        ]);

        // For header and description types, copy the label to content field
        $data = $request->all();
        if (in_array($request->type, ['header', 'description'])) {
            $data['content'] = $request->label;
            $data['required'] = false;  // Headers and descriptions are never required
        }

        $form->fields()->create($data);


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
            'type' => 'required|in:text,textarea,select,checkbox,radio,header,description,file',
            'options' => 'nullable',
            'required' => 'boolean',
            'order' => 'integer',
            'char_limit' => 'nullable|integer|min:1',
            'content' => 'nullable|string',  // For header and description content
        ]);

        // For header and description types, copy the label to content field
        $data = $request->all();
        if (in_array($request->type, ['header', 'description'])) {
            $data['content'] = $request->label;
            $data['required'] = false;  // Headers and descriptions are never required
        }

        $field->update($data);

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
