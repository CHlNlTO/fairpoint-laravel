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
        // Remove code column from account_subclasses
        Schema::table('account_subclasses', function (Blueprint $table) {
            // Drop unique constraint first
            $table->dropUnique('account_subclasses_unique_code_per_class');
            // Drop index
            $table->dropIndex('idx_account_subclasses_code');
            // Drop check constraint
            DB::statement('ALTER TABLE account_subclasses DROP CONSTRAINT IF EXISTS account_subclasses_code_check');
        });

        Schema::table('account_subclasses', function (Blueprint $table) {
            $table->dropColumn('code');
        });

        // Remove code column from account_types
        Schema::table('account_types', function (Blueprint $table) {
            // Drop unique constraint first
            $table->dropUnique('account_types_code_unique_per_subclass');
            // Drop index
            $table->dropIndex('idx_account_types_code');
            // Drop check constraint
            DB::statement('ALTER TABLE account_types DROP CONSTRAINT IF EXISTS account_types_code_range');
        });

        Schema::table('account_types', function (Blueprint $table) {
            $table->dropColumn('code');
        });

        // Remove code column from account_subtypes
        Schema::table('account_subtypes', function (Blueprint $table) {
            // Drop unique constraint first
            $table->dropUnique('account_subtypes_code_unique_per_type');
            // Drop index
            $table->dropIndex('idx_account_subtypes_code');
            // Drop check constraint
            DB::statement('ALTER TABLE account_subtypes DROP CONSTRAINT IF EXISTS account_subtypes_code_range');
        });

        Schema::table('account_subtypes', function (Blueprint $table) {
            $table->dropColumn('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore code column in account_subclasses
        Schema::table('account_subclasses', function (Blueprint $table) {
            $table->unsignedInteger('code')->after('account_class_id');
        });

        Schema::table('account_subclasses', function (Blueprint $table) {
            $table->unique(['account_class_id', 'code'], 'account_subclasses_unique_code_per_class');
            $table->index('code', 'idx_account_subclasses_code');
        });

        DB::statement("ALTER TABLE account_subclasses ADD CONSTRAINT account_subclasses_code_check CHECK (code >= 1 AND code <= 9)");

        // Restore code column in account_types
        Schema::table('account_types', function (Blueprint $table) {
            $table->unsignedInteger('code')->after('hint');
        });

        Schema::table('account_types', function (Blueprint $table) {
            $table->unique(['account_subclass_id', 'code'], 'account_types_code_unique_per_subclass');
            $table->index('code', 'idx_account_types_code');
        });

        DB::statement('ALTER TABLE account_types ADD CONSTRAINT account_types_code_range CHECK (code >= 1 AND code <= 99)');

        // Restore code column in account_subtypes
        Schema::table('account_subtypes', function (Blueprint $table) {
            $table->unsignedInteger('code')->after('hint');
        });

        Schema::table('account_subtypes', function (Blueprint $table) {
            $table->unique(['account_type_id', 'code'], 'account_subtypes_code_unique_per_type');
            $table->index('code', 'idx_account_subtypes_code');
        });

        DB::statement('ALTER TABLE account_subtypes ADD CONSTRAINT account_subtypes_code_range CHECK (code >= 1 AND code <= 99)');
    }
};
