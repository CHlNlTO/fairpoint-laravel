<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_government_registrations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('business_registration_id');
            $table->uuid('government_agency_id');
            $table->text('registration_number')->nullable();
            $table->date('registration_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('status')->default('registered');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();

            $table->foreign('business_registration_id', 'business_government_registrations_business_id_fkey')
                ->references('id')
                ->on('business_registrations')
                ->onDelete('cascade');

            $table->foreign('government_agency_id', 'business_government_registrations_agency_id_fkey')
                ->references('id')
                ->on('government_agencies');

            $table->unique(['business_registration_id', 'government_agency_id'], 'business_government_registrations_unique_business_agency');

            $table->index('business_registration_id', 'idx_business_gov_reg_business_id');
            $table->index('government_agency_id', 'idx_business_gov_reg_agency_id');
            $table->index('status', 'idx_business_gov_reg_status');
            $table->index('is_active', 'idx_business_gov_reg_active');
        });

        DB::statement("ALTER TABLE business_government_registrations ALTER COLUMN id SET DEFAULT gen_random_uuid()");
        DB::statement("ALTER TABLE business_government_registrations ADD CONSTRAINT business_government_registrations_registration_date_check CHECK ((registration_date <= expiry_date) OR (expiry_date IS NULL))");
        DB::statement("ALTER TABLE business_government_registrations ADD CONSTRAINT business_government_registrations_registration_number_length CHECK ((char_length(registration_number) <= 100))");
        DB::statement("ALTER TABLE business_government_registrations ADD CONSTRAINT business_government_registrations_status_check CHECK (status = ANY(ARRAY['registered'::text, 'pending'::text, 'expired'::text, 'cancelled'::text]))");
        DB::statement("ALTER TABLE business_government_registrations ADD CONSTRAINT business_government_registrations_notes_length CHECK ((char_length(notes) <= 1000))");
    }

    public function down(): void
    {
        Schema::dropIfExists('business_government_registrations');
    }
};
