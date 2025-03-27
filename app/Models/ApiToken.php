<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ApiToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'token',
        'abilities',
        'allowed_ips',
        'last_used_at',
        'expires_at'
    ];

    protected $casts = [
        'abilities' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime'
    ];

    /**
     * Get the user that owns the token.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the token can perform a given ability.
     *
     * @param string $ability
     * @return bool
     */
    public function can($ability)
    {
        return in_array('*', $this->abilities) || 
               in_array($ability, $this->abilities);
    }

    /**
     * Check if the token is expired.
     *
     * @return bool
     */
    public function isExpired()
    {
        return $this->expires_at !== null && now()->gte($this->expires_at);
    }

    /**
     * Check if request IP is allowed for this token.
     *
     * @param string $ip
     * @return bool
     */
    public function isValidIp($ip)
    {
        // If no allowed IPs are set, allow all
        if (empty($this->allowed_ips)) {
            return true;
        }

        $allowedIps = explode(',', $this->allowed_ips);
        return in_array($ip, array_map('trim', $allowedIps));
    }

    /**
     * Update the last used timestamp.
     *
     * @return bool
     */
    public function markAsUsed()
    {
        $this->last_used_at = now();
        return $this->save();
    }

    /**
     * Get the API token from the request attribute.
     *
     * @param \Illuminate\Http\Request $request
     * @return ApiToken|null
     */
    public static function fromRequest($request)
    {
        return $request->attributes->get('api_token');
    }
} 