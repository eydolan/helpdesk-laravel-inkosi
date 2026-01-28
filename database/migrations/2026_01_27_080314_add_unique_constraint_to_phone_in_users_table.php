<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Ensure email has unique constraint (should already exist, but adding if not)
            // Only add if constraint doesn't exist
            if (!$this->hasIndex('users', 'users_email_unique')) {
                $table->unique('email', 'users_email_unique');
            }
            // Add unique constraint to phone column (only on non-null values)
            // Note: This will fail if there are duplicate phone numbers. Handle duplicates first if needed.
            if (!$this->hasIndex('users', 'users_phone_unique')) {
                $table->unique('phone', 'users_phone_unique');
            }
        });
    }

    /**
     * Check if an index exists on a table
     */
    protected function hasIndex(string $table, string $index): bool
    {
        try {
            $connection = Schema::getConnection();
            $databaseName = $connection->getDatabaseName();
            $result = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$index]);
            return count($result) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_email_unique');
            $table->dropUnique('users_phone_unique');
        });
    }
};
