<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('api_settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('label');
            $table->text('value')->nullable();
            $table->string('type'); // text, number, select, toggle, textarea
            $table->json('attributes')->nullable(); // for select options, input hints, etc.
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default API security settings
        DB::table('api_settings')->insert([
            [
                'key' => 'rate_limit_api_authenticated',
                'label' => 'API Rate Limit (Authenticated)',
                'value' => '120',
                'type' => 'number',
                'description' => 'Requests per minute for authenticated API tokens',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'rate_limit_api_unauthenticated',
                'label' => 'API Rate Limit (Unauthenticated)',
                'value' => '20',
                'type' => 'number',
                'description' => 'Requests per minute for unauthenticated requests (by IP)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'rate_limit_auth_attempts',
                'label' => 'Authentication Attempts Limit',
                'value' => '5',
                'type' => 'number',
                'description' => 'Maximum authentication attempts per minute per IP',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'rate_limit_submissions_read',
                'label' => 'Submissions Read Rate Limit',
                'value' => '200',
                'type' => 'number',
                'description' => 'GET requests per minute for submissions endpoint',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'rate_limit_submissions_write',
                'label' => 'Submissions Write Rate Limit',
                'value' => '60',
                'type' => 'number',
                'description' => 'POST/PUT/PATCH requests per minute for submissions',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'rate_limit_submissions_daily',
                'label' => 'Submissions Daily Limit',
                'value' => '1000',
                'type' => 'number',
                'description' => 'Maximum submissions per day per token',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'api_docs_allowed_domains',
                'label' => 'API Docs Allowed Domains',
                'value' => 'lhc.lu,circl.lu,nc3.lu',
                'type' => 'textarea',
                'description' => 'Comma-separated list of email domains allowed to access API documentation',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'cors_allowed_origins',
                'label' => 'CORS Allowed Origins',
                'value' => 'http://localhost,http://localhost:5173',
                'type' => 'textarea',
                'description' => 'Comma-separated list of origins allowed for CORS requests',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'sanctum_token_prefix',
                'label' => 'Sanctum Token Prefix',
                'value' => '',
                'type' => 'text',
                'description' => 'Optional prefix for new Sanctum tokens (helps with secret scanning)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'api_logging_enabled',
                'label' => 'API Request Logging',
                'value' => '1',
                'type' => 'toggle',
                'description' => 'Enable logging of all API requests for audit purposes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_settings');
    }
};
