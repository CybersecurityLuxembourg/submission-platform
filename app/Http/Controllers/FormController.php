<?php

namespace App\Http\Controllers;

use App\Models\Form;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class FormController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the user's forms.
     */
    public function index(): View|Factory|Application
    {
        $forms = Auth::user()->forms()->latest()->get();
        return view('forms.index', compact('forms'));
    }

    /**
     * Show the form for creating a new form.
     */
    public function create(): View|Factory|Application
    {
        return view('forms.create');
    }

    /**
     * Store a newly created form in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'categories' => 'required|array|min:1',
            'categories.*.name' => 'required|string|max:255',
            'categories.*.description' => 'nullable|string',
            'categories.*.percentage_start' => 'required|integer|min:0|max:100',
            'categories.*.percentage_end' => 'required|integer|min:0|max:100|gt:categories.*.percentage_start',
        ]);

        $form = Form::create([
            'title' => $validatedData['title'],
            'description' => $validatedData['description'],
            'status' => 'draft', // You might want to add a status field to your form creation page
            'user_id' => auth()->id(),
        ]);

        foreach ($validatedData['categories'] as $index => $categoryData) {
            $form->categories()->create([
                'name' => $categoryData['name'],
                'description' => $categoryData['description'],
                'percentage_start' => $categoryData['percentage_start'],
                'percentage_end' => $categoryData['percentage_end'],
                'order' => $index + 1,
            ]);
        }

        return redirect()->route('forms.edit', $form)->with('success', 'Form created successfully.');
    }

    /**
     * Show the form for editing the specified form.
     */
    public function edit(Form $form): View|Factory|Application
    {
       # $this->authorize('update', $form);

        return view('forms.edit', compact('form'));
    }

    /**
     * Update the specified form in storage.
     */
    public function update(Request $request, Form $form): RedirectResponse
    {
       # $this->authorize('update', $form);

        $request->validate([
            'title' => 'required|max:255',
            'description' => 'nullable',
            'status' => 'required|in:draft,published,archived',
        ]);

        $form->update($request->only('title', 'description', 'status'));

        return redirect()->route('forms.index')->with('success', 'Form updated successfully.');
    }

    /**
     * Remove the specified form from storage.
     */
    public function destroy(Form $form): RedirectResponse
    {
      #  $this->authorize('delete', $form);

        $form->delete();

        return redirect()->route('forms.index')->with('success', 'Form deleted successfully.');
    }

    /**
     * Preview the form as it would appear to applicants.
     */
    public function preview(Form $form): View|Factory|Application
    {
        return view('forms.preview', compact('form'));
    }
}
