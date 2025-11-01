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
        Schema::create('industry_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->string('description', 500)->nullable();
            $table->string('hint', 200)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();

            $table->unique('name', 'industry_types_name_unique');
            $table->index('is_active', 'idx_industry_types_active');
            $table->index('name', 'idx_industry_types_name');
        });

        DB::statement('ALTER TABLE industry_types ADD CONSTRAINT industry_types_description_length CHECK ((char_length(description) <= 500))');
        DB::statement('ALTER TABLE industry_types ADD CONSTRAINT industry_types_hint_length CHECK ((char_length(hint) <= 200))');
        DB::statement('ALTER TABLE industry_types ADD CONSTRAINT industry_types_name_length CHECK ((char_length(name) <= 100))');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('industry_types');
    }
};
