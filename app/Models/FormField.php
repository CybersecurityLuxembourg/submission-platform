<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormField extends Model
{
    use HasFactory;

    protected $fillable = ['form_id', 'form_category_id', 'type', 'order', 'label', 'options', 'required', 'content', 'char_limit'];

    /**
     * Get the form that owns the field.
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    /**
     * Get the submission values associated with the form field.
     */
    public function submissionValues(): HasMany
    {
        return $this->hasMany(SubmissionValues::class);
    }

    /**
     * Accessor to get options as an array.
     */
    public function getOptionsArrayAttribute(): array
    {
        return $this->options ? explode(',', $this->options) : [];
    }
}
