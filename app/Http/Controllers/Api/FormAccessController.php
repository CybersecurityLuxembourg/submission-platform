<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FormAccessLinkResource;
use App\Models\Form;
use App\Models\FormAccessLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class FormAccessController extends Controller
{
    /**
     * Display a listing of form access links.
     *
     * @param Form $form
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\JsonResponse
     */
    public function index(Form $form)
    {
        // Check if user owns or has edit access to the form
        if ($form->user_id !== auth()->id() && 
            !$form->appointedUsers()->where('user_id', auth()->id())->where('can_edit', true)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $accessLinks = $form->accessLinks()->latest()->get();
        
        return FormAccessLinkResource::collection($accessLinks);
    }

    /**
     * Store a newly created access link.
     *
     * @param Request $request
     * @param Form $form
     * @return FormAccessLinkResource|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request, Form $form)
    {
        // Check if user owns or has edit access to the form
        if ($form->user_id !== auth()->id() && 
            !$form->appointedUsers()->where('user_id', auth()->id())->where('can_edit', true)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'expires_at' => 'nullable|date|after:now',
            'max_submissions' => 'nullable|integer|min:1'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Generate a unique token
        $token = Str::random(32);
        
        $accessLink = FormAccessLink::create([
            'form_id' => $form->id,
            'token' => $token,
            'name' => $request->name,
            'expires_at' => $request->expires_at,
            'max_submissions' => $request->max_submissions,
            'submission_count' => 0
        ]);
        
        return new FormAccessLinkResource($accessLink);
    }

    /**
     * Display the specified access link.
     *
     * @param Form $form
     * @param FormAccessLink $accessLink
     * @return FormAccessLinkResource|\Illuminate\Http\JsonResponse
     */
    public function show(Form $form, FormAccessLink $accessLink)
    {
        // Check if access link belongs to the specified form
        if ($accessLink->form_id !== $form->id) {
            return response()->json(['message' => 'Access link not found for this form'], 404);
        }
        
        // Check if user owns or has edit access to the form
        if ($form->user_id !== auth()->id() && 
            !$form->appointedUsers()->where('user_id', auth()->id())->where('can_edit', true)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        return new FormAccessLinkResource($accessLink);
    }

    /**
     * Update the specified access link.
     *
     * @param Request $request
     * @param Form $form
     * @param FormAccessLink $accessLink
     * @return FormAccessLinkResource|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Form $form, FormAccessLink $accessLink)
    {
        // Check if access link belongs to the specified form
        if ($accessLink->form_id !== $form->id) {
            return response()->json(['message' => 'Access link not found for this form'], 404);
        }
        
        // Check if user owns or has edit access to the form
        if ($form->user_id !== auth()->id() && 
            !$form->appointedUsers()->where('user_id', auth()->id())->where('can_edit', true)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'expires_at' => 'nullable|date|after:now',
            'max_submissions' => 'nullable|integer|min:1'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $accessLink->update($request->only(['name', 'expires_at', 'max_submissions']));
        
        return new FormAccessLinkResource($accessLink);
    }

    /**
     * Remove the specified access link.
     *
     * @param Form $form
     * @param FormAccessLink $accessLink
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Form $form, FormAccessLink $accessLink)
    {
        // Check if access link belongs to the specified form
        if ($accessLink->form_id !== $form->id) {
            return response()->json(['message' => 'Access link not found for this form'], 404);
        }
        
        // Check if user owns the form
        if ($form->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $accessLink->delete();
        
        return response()->json(['message' => 'Access link deleted successfully'], 200);
    }
} 