<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\User;
use App\Models\FormAccessLink;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FormAccessController extends Controller
{
    use AuthorizesRequests;

    /**
     * @throws AuthorizationException
     */
    public function assignUsers(Request $request, Form $form): RedirectResponse
    {
        $this->authorize('assignUsers', $form);

        $validatedData = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'can_edit' => 'boolean',
        ]);

        $form->appointedUsers()->syncWithPivotValues($validatedData['user_ids'], [
            'can_edit' => $validatedData['can_edit'] ?? false,
        ]);

        return redirect()->route('forms.edit', $form)->with('success', 'Users assigned successfully.');
    }

    /**
     * @throws AuthorizationException
     */
    public function createAccessLink(Request $request, Form $form): RedirectResponse
    {
        $this->authorize('assignUsers', $form);

        $validatedData = $request->validate([
            'expires_at' => 'nullable|date|after:now',
        ]);

        $accessLink = $form->accessLinks()->create([
            'token' => Str::random(32),
            'expires_at' => $validatedData['expires_at'] ?? null,
        ]);

        return redirect()->route('forms.edit', $form)->with('success', 'Access link created successfully.');
    }

    /**
     * @throws AuthorizationException
     */
    public function deleteAccessLink(FormAccessLink $accessLink): RedirectResponse
    {
        $this->authorize('appointUsers', $accessLink->form);

        $accessLink->delete();

        return redirect()->route('forms.edit', $accessLink->form)->with('success', 'Access link deleted successfully.');
    }

    public function accessForm(Request $request, $token): RedirectResponse
    {
        $accessLink = FormAccessLink::where('token', $token)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->firstOrFail();

        // Grant access to the form
        $request->session()->put('form_access_' . $accessLink->form_id, true);

        // Redirect to the form submission page instead of the show route
        return redirect()->route('submissions.create', $accessLink->form);
    }




}
