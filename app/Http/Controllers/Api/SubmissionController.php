<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubmissionResource;
use App\Models\Form;
use App\Models\FormField;
use App\Models\Submission;
use App\Models\SubmissionValues;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SubmissionController extends Controller
{
    /**
     * Display a listing of submissions for a form.
     *
     * @param Request $request
     * @param Form $form
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request, Form $form)
    {
        // Check if user owns or has access to the form
        if ($form->user_id !== auth()->id() && 
            !$form->appointedUsers()->where('user_id', auth()->id())->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $query = $form->submissions();
        
        // Apply filters if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        
        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        
        $submissions = $query->latest()->paginate($request->per_page ?? 15);
        
        // Load values relationship for each submission
        $submissions->load('values.field');
        
        return SubmissionResource::collection($submissions);
    }

    /**
     * Store a newly created submission.
     *
     * @param Request $request
     * @param Form $form
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, Form $form)
    {
        // Check if form is published
        if ($form->status !== 'published') {
            return response()->json(['message' => 'Form is not available for submissions'], 403);
        }
        
        // Check if form is accessible
        if (!$form->canAccess(auth()->user())) {
            return response()->json(['message' => 'Unauthorized access to this form'], 403);
        }
        
        // Get all form fields for validation
        $formFields = $form->fields()->with('category')->get();
        
        // Build validation rules based on form fields
        $rules = [];
        foreach ($formFields as $field) {
            $fieldName = 'values.' . $field->id;
            $fieldRules = [];
            
            // Add required rule if the field is required
            if ($field->required) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }
            
            // Add type-specific validation
            switch ($field->type) {
                case 'email':
                    $fieldRules[] = 'email';
                    break;
                case 'number':
                    $fieldRules[] = 'numeric';
                    break;
                case 'checkbox':
                    $fieldRules[] = 'boolean';
                    break;
                case 'select':
                case 'radio':
                    if ($field->options) {
                        $options = explode(',', $field->options);
                        $fieldRules[] = 'in:' . implode(',', $options);
                    }
                    break;
                default:
                    $fieldRules[] = 'string';
            }
            
            $rules[$fieldName] = implode('|', $fieldRules);
        }
        
        // Validate the submission
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            // Use a transaction to ensure data integrity
            return DB::transaction(function () use ($request, $form) {
                // Create the submission
                $submission = Submission::create([
                    'form_id' => $form->id,
                    'ip_address' => $request->ip(),
                    'status' => 'submitted'
                ]);
                
                // Create submission values
                if ($request->has('values')) {
                    foreach ($request->values as $fieldId => $value) {
                        SubmissionValues::create([
                            'submission_id' => $submission->id,
                            'form_field_id' => $fieldId,
                            'value' => $value
                        ]);
                    }
                }
                
                // Load values for the response
                $submission->load('values.field');
                
                return response()->json([
                    'message' => 'Submission created successfully',
                    'data' => new SubmissionResource($submission)
                ], 201);
            });
        } catch (\Exception $e) {
            Log::error('API Submission creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Submission creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified submission.
     *
     * @param Form $form
     * @param Submission $submission
     * @return SubmissionResource|\Illuminate\Http\JsonResponse
     */
    public function show(Form $form, Submission $submission)
    {
        // Check if submission belongs to the specified form
        if ($submission->form_id !== $form->id) {
            return response()->json(['message' => 'Submission not found for this form'], 404);
        }
        
        // Check if user owns or has access to the form
        if ($form->user_id !== auth()->id() && 
            !$form->appointedUsers()->where('user_id', auth()->id())->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        // Load values relationship with fields
        $submission->load('values.field');
        
        return new SubmissionResource($submission);
    }

    /**
     * Update the submission status.
     *
     * @param Request $request
     * @param Form $form
     * @param Submission $submission
     * @return SubmissionResource|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Form $form, Submission $submission)
    {
        // Check if submission belongs to the specified form
        if ($submission->form_id !== $form->id) {
            return response()->json(['message' => 'Submission not found for this form'], 404);
        }
        
        // Check if user owns or has access to the form
        if ($form->user_id !== auth()->id() && 
            !$form->appointedUsers()->where('user_id', auth()->id())->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:submitted,approved,rejected'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $submission->update([
            'status' => $request->status
        ]);
        
        return new SubmissionResource($submission);
    }

    /**
     * Remove the specified submission.
     *
     * @param Form $form
     * @param Submission $submission
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Form $form, Submission $submission)
    {
        // Check if submission belongs to the specified form
        if ($submission->form_id !== $form->id) {
            return response()->json(['message' => 'Submission not found for this form'], 404);
        }
        
        // Check if user owns the form
        if ($form->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        // Delete the submission and its values
        DB::transaction(function () use ($submission) {
            $submission->values()->delete();
            $submission->delete();
        });
        
        return response()->json(['message' => 'Submission deleted successfully'], 200);
    }
} 