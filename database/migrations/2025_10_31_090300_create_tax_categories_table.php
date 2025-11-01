<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->string('description', 500)->nullable();
            $table->string('hint', 200)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();

            $table->unique('name', 'tax_categories_name_unique');
            $table->index('is_active', 'idx_tax_categories_active');
            $table->index('name', 'idx_tax_categories_name');
        });

        DB::statement('ALTER TABLE tax_categories ADD CONSTRAINT tax_categories_description_length CHECK ((char_length(description) <= 500))');
        DB::statement('ALTER TABLE tax_categories ADD CONSTRAINT tax_categories_hint_length CHECK ((char_length(hint) <= 200))');
        DB::statement('ALTER TABLE tax_categories ADD CONSTRAINT tax_categories_name_length CHECK ((char_length(name) <= 100))');
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_categories');
    }
};
