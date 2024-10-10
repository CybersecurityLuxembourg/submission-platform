<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Form extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'status',
    ];

    /**
     * Get the user that owns the form.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the fields associated with the form.
     */
    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class);
    }

    /**
     * Get the submissions associated with the form.
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(FormCategory::class);
    }
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function appointedUsers()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('can_edit')
            ->withTimestamps();
    }
}
