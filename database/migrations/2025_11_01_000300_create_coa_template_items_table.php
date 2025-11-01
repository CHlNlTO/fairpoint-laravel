<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coa_template_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->char('account_code', 6);
            $table->text('account_name');
            $table->uuid('account_subtype_id');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->string('normal_balance')->default('debit');
            $table->timestampsTz();

            $table->foreign('account_subtype_id', 'coa_template_items_account_subtype_id_fkey')
                ->references('id')
                ->on('account_subtypes');

            $table->unique('account_code', 'coa_template_items_unique_account_code');

            $table->index('account_subtype_id', 'idx_coa_template_items_account_subtype_id');
            $table->index('account_code', 'idx_coa_template_items_account_code');
            $table->index('is_active', 'idx_coa_template_items_active');
            $table->index('is_default', 'idx_coa_template_items_default');
            $table->index('normal_balance', 'idx_coa_template_items_normal_balance');
        });

        DB::statement("ALTER TABLE coa_template_items ALTER COLUMN id SET DEFAULT gen_random_uuid()");
        DB::statement("ALTER TABLE coa_template_items ADD CONSTRAINT coa_template_items_normal_balance_check CHECK (normal_balance = ANY(ARRAY['debit'::text, 'credit'::text]))");
        DB::statement("ALTER TABLE coa_template_items ADD CONSTRAINT coa_template_items_account_name_length CHECK ((char_length(account_name) <= 200))");
        DB::statement("ALTER TABLE coa_template_items ADD CONSTRAINT coa_template_items_account_code_format CHECK ((account_code ~ '^[0-9]{6}$'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('coa_template_items');
    }
};
