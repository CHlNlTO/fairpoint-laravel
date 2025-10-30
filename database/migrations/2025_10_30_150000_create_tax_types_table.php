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
        Schema::create('tax_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->string('description', 500)->nullable();
            $table->string('hint', 200)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();

            $table->unique('name', 'tax_types_name_unique');
            $table->index('is_active', 'idx_tax_types_active');
            $table->index('name', 'idx_tax_types_name');
        });

        // Enforce the same constraints and trigger behavior as specified
        DB::statement('ALTER TABLE tax_types ADD CONSTRAINT tax_types_description_length CHECK ((char_length(description) <= 500))');
        DB::statement('ALTER TABLE tax_types ADD CONSTRAINT tax_types_hint_length CHECK ((char_length(hint) <= 200))');
        DB::statement('ALTER TABLE tax_types ADD CONSTRAINT tax_types_name_length CHECK ((char_length(name) <= 100))');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_types');
    }
};
