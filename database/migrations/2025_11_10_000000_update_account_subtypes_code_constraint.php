<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Allow account subtype codes to start at 0 while retaining the upper bound of 99.
        DB::statement('ALTER TABLE account_subtypes DROP CONSTRAINT IF EXISTS account_subtypes_code_range');
        DB::statement('ALTER TABLE account_subtypes ADD CONSTRAINT account_subtypes_code_range CHECK (code >= 0 AND code <= 99)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to the original constraint which required the code to start at 1.
        DB::statement('ALTER TABLE account_subtypes DROP CONSTRAINT IF EXISTS account_subtypes_code_range');
        DB::statement('ALTER TABLE account_subtypes ADD CONSTRAINT account_subtypes_code_range CHECK (code >= 1 AND code <= 99)');
    }
};
