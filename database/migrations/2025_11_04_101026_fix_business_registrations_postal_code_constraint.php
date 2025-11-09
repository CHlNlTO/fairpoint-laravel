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
        // Drop the existing constraints with incorrect regex patterns
        DB::statement('ALTER TABLE business_registrations DROP CONSTRAINT IF EXISTS business_registrations_postal_code_format');
        DB::statement('ALTER TABLE business_registrations DROP CONSTRAINT IF EXISTS business_registrations_tin_number_format');

        // Recreate the constraints with correct PostgreSQL regex patterns
        // PostgreSQL uses [0-9] instead of \d for digit matching
        DB::statement("ALTER TABLE business_registrations ADD CONSTRAINT business_registrations_postal_code_format CHECK (((postal_code ~ '^[0-9]{4}$') OR (postal_code IS NULL)))");
        DB::statement("ALTER TABLE business_registrations ADD CONSTRAINT business_registrations_tin_number_format CHECK ((tin_number ~ '^[0-9]{3}-[0-9]{3}-[0-9]{3}-[0-9]{3}$'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the fixed constraints
        DB::statement('ALTER TABLE business_registrations DROP CONSTRAINT IF EXISTS business_registrations_postal_code_format');
        DB::statement('ALTER TABLE business_registrations DROP CONSTRAINT IF EXISTS business_registrations_tin_number_format');

        // Restore the original (incorrect) constraints
        DB::statement("ALTER TABLE business_registrations ADD CONSTRAINT business_registrations_postal_code_format CHECK (((postal_code ~ '^\\\d{4}$') OR (postal_code IS NULL)))");
        DB::statement("ALTER TABLE business_registrations ADD CONSTRAINT business_registrations_tin_number_format CHECK ((tin_number ~ '^\\\d{3}-\\\d{3}-\\\d{3}-\\\d{3}$'))");
    }
};
