<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SubmissionValues extends Model
{
    use HasFactory;

    protected $fillable = [
        'submission_id',
        'form_field_id',
        'value',
    ];

    /**
     * Get the submission that owns the submission value.
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    /**
     * Get the form field that owns the submission value.
     */
    public function field(): BelongsTo
    {
        return $this->belongsTo(FormField::class, 'form_field_id');
    }

    /**
     * Get the scan result associated with this submission value (if it's a file).
     */
    public function scanResult(): HasOne
    {
        return $this->hasOne(ScanResult::class, 'submission_value_id');
    }
}
