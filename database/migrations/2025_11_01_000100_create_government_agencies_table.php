<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('government_agencies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 10)->unique();
            $table->string('name');
            $table->text('full_name');
            $table->text('description')->nullable();
            $table->string('website_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();

            $table->index('is_active', 'idx_government_agencies_active');
        });

        DB::statement("ALTER TABLE government_agencies ADD CONSTRAINT government_agencies_code_check CHECK (code ~ '^[A-Z0-9]{2,10}$')");
        DB::statement("ALTER TABLE government_agencies ALTER COLUMN id SET DEFAULT gen_random_uuid()");
    }

    public function down(): void
    {
        Schema::dropIfExists('government_agencies');
    }
};
