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
        Schema::create('fiscal_year_periods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->unsignedInteger('start_month');
            $table->unsignedInteger('start_day');
            $table->unsignedInteger('end_month');
            $table->unsignedInteger('end_day');
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();

            $table->unique('name', 'fiscal_year_periods_name_key');
            $table->index('is_active', 'idx_fiscal_year_periods_active');
            $table->index([
                'start_month', 'start_day', 'end_month', 'end_day', 'is_default', 'is_active'
            ], 'idx_fiscal_year_periods_custom_search');
        });

        DB::statement('ALTER TABLE fiscal_year_periods ADD CONSTRAINT fiscal_year_periods_end_day_check CHECK (((end_day >= 1) AND (end_day <= 31)))');
        DB::statement('ALTER TABLE fiscal_year_periods ADD CONSTRAINT fiscal_year_periods_end_month_check CHECK (((end_month >= 1) AND (end_month <= 12)))');
        DB::statement('ALTER TABLE fiscal_year_periods ADD CONSTRAINT fiscal_year_periods_start_day_check CHECK (((start_day >= 1) AND (start_day <= 31)))');
        DB::statement('ALTER TABLE fiscal_year_periods ADD CONSTRAINT fiscal_year_periods_start_month_check CHECK (((start_month >= 1) AND (start_month <= 12)))');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fiscal_year_periods');
    }
};
