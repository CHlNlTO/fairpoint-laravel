<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('business_coa_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('business_id');
            $table->uuid('coa_item_id');
            $table->char('account_code', 6);
            $table->enum('normal_balance', ['debit', 'credit'])->default('debit');
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();

            $table->foreign('business_id')
                ->references('id')
                ->on('business_registrations')
                ->onDelete('cascade');

            $table->foreign('coa_item_id')
                ->references('id')
                ->on('coa_template_items')
                ->onDelete('cascade');

            $table->index('business_id');
            $table->index('coa_item_id');
            $table->unique(['business_id', 'account_code'], 'business_coa_items_unique_code_per_business');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_coa_items');
    }
};
