<?php

namespace App\Http\Controllers;

use App\Models\Form;
use Illuminate\Auth\Access\AuthorizationException;
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

    public function userIndex(): View|Factory|Application
    {
        $forms = Auth::user()->forms()->latest()->get();
        return view('forms.index', compact('forms'));
    }

    /**
     * @throws AuthorizationException
     */
    public function create(): View|Factory|Application
    {
        $this->authorize('create', Form::class);
        return view('forms.create');
    }

    /**
     * @throws AuthorizationException
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Form::class);

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
            'status' => 'draft',
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
     * @throws AuthorizationException
     */
    public function edit(Form $form): View|Factory|Application
    {
        $this->authorize('update', $form);
        return view('forms.edit', compact('form'));
    }

    /**
     * @throws AuthorizationException
     */
    public function update(Request $request, Form $form): RedirectResponse
    {
        $this->authorize('update', $form);

        $request->validate([
            'title' => 'required|max:255',
            'description' => 'nullable',
            'status' => 'required|in:draft,published,archived',
        ]);

        $form->update($request->only('title', 'description', 'status'));

        return redirect()->route('forms.index')->with('success', 'Form updated successfully.');
    }

    /**
     * @throws AuthorizationException
     */
    public function destroy(Form $form): RedirectResponse
    {
        $this->authorize('delete', $form);

        $form->delete();

        return redirect()->route('forms.index')->with('success', 'Form deleted successfully.');
    }

    /**
     * @throws AuthorizationException
     */
    public function preview(Form $form): View|Factory|Application
    {
        $this->authorize('view', $form);
        return view('forms.preview', compact('form'));
    }
}
