<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\Submission;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubmissionExportController extends Controller
{
    use AuthorizesRequests;
    /**
     * Export single submission to PDF
     *
     * @throws AuthorizationException
     */
    public function exportSubmissionPdf(Form $form, Submission $submission): Response
    {

        // Ensure the submission belongs to the form
        if ($submission->form_id !== $form->id) {
            abort(404);
        }
        $this->authorize('export', $submission);

        // Load the submission with its values
        $submission->load(['values', 'values.field']);

        // Prepare submission data
        $submissionData = $this->prepareSubmissionData($submission);

        $pdf = PDF::loadView('submissions.pdf', [
            'form' => $form,
            'submission' => $submissionData,
        ]);

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="submission-' . $submission->id . '.pdf"',
        ]);
    }

    /**
     * Export all submissions for a form to CSV
     *
     * @throws AuthorizationException
     */
    public function exportFormCsv(Form $form): StreamedResponse
    {
        $this->authorize('exportAllSubmissions', $form);

        // Load the form with its categories and fields
        $form->load([
            'categories' => function ($query) {
                $query->orderBy('order');
            },
            'categories.fields' => function ($query) {
                $query->orderBy('order');
            },
        ]);

        // Load all submissions with their values
        $submissions = $form->submissions()
            ->with(['values', 'user'])
            ->latest()
            ->get();

        // Create CSV content
        $headers = ['Submission ID', 'Submitted By', 'Submitted At'];
        $fieldIds = [];

        // Build headers from form structure
        foreach ($form->categories as $category) {
            foreach ($category->fields as $field) {
                $headers[] = $category->name . ' - ' . $field->label;
                $fieldIds[] = $field->id;
            }
        }

        $callback = function() use ($submissions, $headers, $fieldIds) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);

            foreach ($submissions as $submission) {
                $row = [
                    $submission->id,
                    $submission->user ? $submission->user->name : 'Anonymous',
                    $submission->created_at->format('Y-m-d H:i:s'),
                ];

                $values = $submission->values->keyBy('form_field_id');

                foreach ($fieldIds as $fieldId) {
                    $value = $values->get($fieldId);
                    if ($value) {
                        $displayValue = match ($value->field->type) {
                            'file' => $value->value ? route('submissions.download', ['submission' => $submission->id, 'filename' => basename($value->value)]) : '',
                            'checkbox' => $value->value ? 'Yes' : 'No',
                            default => $value->value,
                        };
                        $row[] = $displayValue;
                    } else {
                        $row[] = '';
                    }
                }

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $form->title . '-submissions.csv"',
        ]);
    }

    /**
     * Export user's own submissions to CSV
     */
    public function exportUserSubmissionsCsv(): StreamedResponse
    {
        $user = auth()->user();

        if (!$user) {
            abort(403);
        }

        $submissions = Submission::where('user_id', $user->id)
            ->with(['form', 'values', 'values.field'])
            ->latest()
            ->get();

        $callback = function() use ($submissions) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Submission ID', 'Form Title', 'Submitted At', 'Status']);

            foreach ($submissions as $submission) {
                fputcsv($file, [
                    $submission->id,
                    $submission->form->title,
                    $submission->created_at->format('Y-m-d H:i:s'),
                    'Submitted'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="my-submissions.csv"',
        ]);
    }

    private function prepareSubmissionData(Submission $submission): array
    {
        $submissionValues = $submission->values->keyBy('form_field_id');

        $categories = $submission->form->categories->map(function ($category) use ($submissionValues, $submission) {
            $fields = $category->fields->map(function ($field) use ($submissionValues, $submission) {
                $value = $submissionValues->get($field->id);

                $displayValue = null;
                if ($value) {
                    $displayValue = match ($field->type) {
                        'file' => $value->value ? route('submissions.download', ['submission' => $submission->id, 'filename' => basename($value->value)]) : null,
                        'checkbox' => $value->value ? 'Yes' : 'No',
                        'radio', 'select' => $value->value,
                        default => $value->value,
                    };
                }

                return [
                    'label' => $field->label,
                    'type' => $field->type,
                    'displayValue' => $displayValue,
                ];
            });

            return [
                'name' => $category->name,
                'description' => $category->description,
                'fields' => $fields,
            ];
        });

        return [
            'id' => $submission->id,
            'created_at' => $submission->created_at->format('Y-m-d H:i:s'),
            'categories' => $categories,
        ];
    }
}
