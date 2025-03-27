<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'token_id',
        'method',
        'endpoint',
        'ip_address',
        'request_data',
        'response_code',
        'execution_time'
    ];

    protected $casts = [
        'request_data' => 'array',
        'execution_time' => 'float'
    ];

    /**
     * Get the user that performed the API request.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the token used for the API request.
     */
    public function token()
    {
        return $this->belongsTo(ApiToken::class);
    }
} 