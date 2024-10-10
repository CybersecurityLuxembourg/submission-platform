<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Submission extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_id',
        'user_id'
    ];

    /**
     * Get the form that owns the submission.
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    /**
     * Get the submission values associated with the submission.
     */
    public function values(): HasMany
    {
        return $this->hasMany(SubmissionValues::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
