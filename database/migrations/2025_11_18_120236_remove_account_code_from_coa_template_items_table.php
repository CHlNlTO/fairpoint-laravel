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
        Schema::table('coa_template_items', function (Blueprint $table) {
            // Drop unique constraint first
            $table->dropUnique('coa_template_items_unique_account_code');
            // Drop index
            $table->dropIndex('idx_coa_template_items_account_code');
            // Drop check constraint
            DB::statement('ALTER TABLE coa_template_items DROP CONSTRAINT IF EXISTS coa_template_items_account_code_format');
        });

        Schema::table('coa_template_items', function (Blueprint $table) {
            $table->dropColumn('account_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coa_template_items', function (Blueprint $table) {
            $table->char('account_code', 6)->after('id');
        });

        Schema::table('coa_template_items', function (Blueprint $table) {
            $table->unique('account_code', 'coa_template_items_unique_account_code');
            $table->index('account_code', 'idx_coa_template_items_account_code');
        });

        DB::statement("ALTER TABLE coa_template_items ADD CONSTRAINT coa_template_items_account_code_format CHECK ((account_code ~ '^[0-9]{6}$'))");
    }
};
