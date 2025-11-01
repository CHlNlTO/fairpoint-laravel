<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coa_item_tax_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('account_item_id');
            $table->uuid('tax_type_id');
            $table->timestampsTz();

            $table->foreign('account_item_id', 'coa_item_tax_types_account_item_id_fkey')
                ->references('id')
                ->on('coa_template_items')
                ->onDelete('cascade');

            $table->foreign('tax_type_id', 'coa_item_tax_types_tax_type_id_fkey')
                ->references('id')
                ->on('tax_types')
                ->onDelete('cascade');

            $table->unique(['account_item_id', 'tax_type_id'], 'coa_item_tax_types_unique');

            $table->index('account_item_id', 'idx_coa_item_tax_types_account_item_id');
            $table->index('tax_type_id', 'idx_coa_item_tax_types_tax_type_id');
        });

        DB::statement("ALTER TABLE coa_item_tax_types ALTER COLUMN id SET DEFAULT gen_random_uuid()");
    }

    public function down(): void
    {
        Schema::dropIfExists('coa_item_tax_types');
    }
};
