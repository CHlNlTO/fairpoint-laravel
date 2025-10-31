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
        Schema::create('business_registrations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->id('user_id');
            $table->string('business_name');
            $table->string('tin_number');
            $table->string('business_email');
            $table->uuid('fiscal_year_period_id');
            $table->uuid('business_type_id')->nullable();

            // Address columns (from yajra/laravel-address)
            $table->unsignedBigInteger('region_id')->nullable();
            $table->unsignedBigInteger('province_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->unsignedBigInteger('barangay_id')->nullable();

            $table->string('street_address', 500)->nullable();
            $table->string('building_name', 200)->nullable();
            $table->string('unit_number', 50)->nullable();
            $table->string('postal_code', 10)->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();

            $table->index('user_id', 'idx_business_registrations_user_id');
            $table->index('tin_number', 'idx_business_registrations_tin_number');
            $table->index('business_email', 'idx_business_registrations_business_email');
            $table->index('is_active', 'idx_business_registrations_active');

            $table->index('fiscal_year_period_id', 'idx_business_registrations_fyp_id');
            $table->index('business_type_id', 'idx_business_registrations_business_type_id');
            $table->index('region_id', 'idx_business_registrations_region_id');
            $table->index('province_id', 'idx_business_registrations_province_id');
            $table->index('city_id', 'idx_business_registrations_city_id');
            $table->index('barangay_id', 'idx_business_registrations_barangay_id');

            $table->foreign('fiscal_year_period_id')->references('id')->on('fiscal_year_periods');
            $table->foreign('business_type_id')->references('id')->on('business_types');
            $table->foreign('region_id')->references('id')->on('regions');
            $table->foreign('province_id')->references('id')->on('provinces');
            $table->foreign('city_id')->references('id')->on('cities');
            $table->foreign('barangay_id')->references('id')->on('barangays');
        });

        DB::statement('ALTER TABLE business_registrations ADD CONSTRAINT business_registrations_user_id_fkey FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE');

        // Checks
        DB::statement("ALTER TABLE business_registrations ADD CONSTRAINT business_registrations_unit_number_length CHECK ((char_length(unit_number) <= 50))");
        DB::statement("ALTER TABLE business_registrations ADD CONSTRAINT business_registrations_building_name_length CHECK ((char_length(building_name) <= 200))");
        DB::statement("ALTER TABLE business_registrations ADD CONSTRAINT business_registrations_postal_code_format CHECK (((postal_code ~ '^\\\d{4}$') OR (postal_code IS NULL)))");
        DB::statement("ALTER TABLE business_registrations ADD CONSTRAINT business_registrations_street_address_length CHECK ((char_length(street_address) <= 500))");
        DB::statement("ALTER TABLE business_registrations ADD CONSTRAINT business_registrations_tin_number_format CHECK ((tin_number ~ '^\\\d{3}-\\\d{3}-\\\d{3}-\\\d{3}$'))");
        DB::statement("ALTER TABLE business_registrations ADD CONSTRAINT business_registrations_business_email_format CHECK ((business_email ~* '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_registrations');
    }
};
