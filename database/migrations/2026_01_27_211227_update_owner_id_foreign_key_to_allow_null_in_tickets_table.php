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
        Schema::table('tickets', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign('tickets_ibfk_3');
        });

        Schema::table('tickets', function (Blueprint $table) {
            // Recreate the foreign key constraint allowing null values
            $table->foreign(['owner_id'], 'tickets_ibfk_3')
                ->references(['id'])
                ->on('users')
                ->onUpdate('NO ACTION')
                ->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign('tickets_ibfk_3');
        });

        Schema::table('tickets', function (Blueprint $table) {
            // Recreate the original foreign key constraint (non-nullable)
            // Note: This will fail if there are null values in owner_id
            $table->foreign(['owner_id'], 'tickets_ibfk_3')
                ->references(['id'])
                ->on('users')
                ->onUpdate('NO ACTION')
                ->onDelete('NO ACTION');
        });
    }
};
