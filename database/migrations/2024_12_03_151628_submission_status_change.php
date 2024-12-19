<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            // Drop the existing status column
            $table->dropColumn('status');

            // Add new status column with more states
            $table->enum('status', [
                'draft',             // User has started but not completed
                'ongoing',           // User is actively working on it
                'submitted',         // User has completed and submitted
                'under_review',      // Being reviewed by evaluators
                'completed'          // Review process complete
            ])->default('draft');

            // Add last_activity tracking
            $table->timestamp('last_activity')->nullable();

            // Add metadata column for additional status-related info
            $table->json('status_metadata')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn(['status', 'last_activity', 'status_metadata']);
            $table->enum('status', ['draft', 'submitted'])->default('draft');
        });
    }
};
