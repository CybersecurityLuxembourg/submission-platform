<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\User;
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
    public function publicIndex(Request $request): View|Factory|Application
    {
        $forms = Form::where('status', 'published')
            ->whereIn('visibility', ['public', 'authenticated'])
            ->latest()
            ->paginate(10);

        return view('forms.public-index', compact('forms'));
    }
    public function userIndex(): View|Factory|Application
    {
        $this->authorize('create', Form::class);
        $forms = Auth::user()->forms()->latest()->get();
        return view('forms.user-index', compact('forms'));
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
            'visibility' => 'required|in:public,authenticated,private',
            'categories' => 'required|array|min:1',
            'categories.*.name' => 'required|string|max:255',
            'categories.*.description' => 'nullable|string',
        ]);

        $form = Form::create([
            'title' => $validatedData['title'],
            'description' => $validatedData['description'],
            'visibility' => $validatedData['visibility'],
            'status' => 'draft',
            'user_id' => auth()->id(),
        ]);

        foreach ($validatedData['categories'] as $index => $categoryData) {
            $form->categories()->create([
                'name' => $categoryData['name'],
                'description' => $categoryData['description'],
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
            'visibility' => 'required|in:public,authenticated,private',
        ]);

        $form->update($request->only('title', 'description', 'status', 'visibility'));

        return redirect()->route('forms.user_index')->with('success', 'Form updated successfully.');
    }

    /**
     * @throws AuthorizationException
     */
    public function destroy(Form $form): RedirectResponse
    {
        $this->authorize('delete', $form);

        $form->delete();

        return redirect()->route('forms.user_index')->with('success', 'Form deleted successfully.');
    }

    /**
     * @throws AuthorizationException
     */
    public function preview(Form $form): View|Factory|Application
    {
       $this->authorize('view', $form);

        if ($form->visibility === 'authenticated' && !Auth::check()) {
            abort(403, 'This form is only accessible to authenticated users.');
        }

        if ($form->visibility === 'private' && $form->user_id !== Auth::id()) {
            abort(403, 'You do not have permission to view this form.');
        }

        return view('forms.preview', compact('form'));
    }

    /**
     * Remove a user from the form.
     *
     * @param Form $form
     * @param User $user
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function removeUser(Form $form, User $user): RedirectResponse
    {
        $this->authorize('assignUsers', $form);

        $form->appointedUsers()->detach($user->id);

        return redirect()->route('forms.edit', $form)
            ->with('success', 'User removed successfully.');
    }
}
