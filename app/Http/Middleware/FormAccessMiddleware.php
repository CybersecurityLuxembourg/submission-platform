<?php

namespace App\Http\Middleware;

use App\Models\Form;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FormAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $form = $request->route('form');

        if (!$form instanceof Form) {
            return $next($request);
        }

        // Check form visibility
        switch ($form->visibility) {
            case 'public':
                return $next($request);

            case 'authenticated':
                if (!auth()->check()) {
                    return redirect()->route('login');
                }
                return $next($request);

            case 'private':
                // Check for valid access link token in session
                $hasValidAccessLink = $request->session()->has('form_access_' . $form->id);

                if ($hasValidAccessLink) {
                    return $next($request);
                }

                // Check if user is authenticated and has permission
                if (auth()->check()) {
                    $user = auth()->user();
                    if ($user->isAdmin() ||
                        $user->id === $form->user_id ||
                        $form->appointedUsers->contains($user->id)) {
                        return $next($request);
                    }
                }

                abort(403, 'You do not have permission to access this form.');
        }

        return $next($request);
    }
}
