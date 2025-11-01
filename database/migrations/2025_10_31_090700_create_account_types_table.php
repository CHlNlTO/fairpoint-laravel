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
        Schema::create('account_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('account_subclass_id');
            $table->bigInteger('user_id')->nullable();
            $table->uuid('business_registration_id')->nullable();
            $table->string('name', 100);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system_defined')->default(true);
            $table->string('description', 500)->nullable();
            $table->string('hint', 200)->nullable();
            $table->timestampsTz();
            $table->unsignedInteger('code');

            $table->unique(['account_subclass_id', 'code'], 'account_types_code_unique_per_subclass');

            $table->index('account_subclass_id', 'idx_account_types_subclass_id');
            $table->index('user_id', 'idx_account_types_user_id');
            $table->index('business_registration_id', 'idx_account_types_business_id');
            $table->index('is_active', 'idx_account_types_active');
            $table->index('is_system_defined', 'idx_account_types_system_defined');
            $table->index('code', 'idx_account_types_code');

            $table->foreign('account_subclass_id')
                ->references('id')->on('account_subclasses')
                ->onDelete('cascade');

            $table->foreign('business_registration_id')
                ->references('id')->on('business_registrations')
                ->onDelete('cascade');
        });

        DB::statement('ALTER TABLE account_types ADD CONSTRAINT account_types_user_id_fkey FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE account_types ADD CONSTRAINT account_types_user_or_business_check CHECK (( (is_system_defined = true AND user_id IS NULL AND business_registration_id IS NULL) OR (is_system_defined = false AND (user_id IS NOT NULL OR business_registration_id IS NOT NULL)) ))');
        DB::statement('ALTER TABLE account_types ADD CONSTRAINT account_types_code_range CHECK (code >= 1 AND code <= 99)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_types');
    }
};
