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
        Schema::table('business_coa_items', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['coa_item_id']);
        });

        Schema::table('business_coa_items', function (Blueprint $table) {
            // Make coa_item_id nullable to support user-added items
            $table->uuid('coa_item_id')->nullable()->change();
        });

        Schema::table('business_coa_items', function (Blueprint $table) {
            // Re-add the foreign key constraint as nullable
            $table->foreign('coa_item_id')
                ->references('id')
                ->on('coa_template_items')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_coa_items', function (Blueprint $table) {
            // Drop the nullable foreign key
            $table->dropForeign(['coa_item_id']);

            // Make it non-nullable again (this will fail if there are null values)
            $table->uuid('coa_item_id')->nullable(false)->change();

            // Re-add the foreign key constraint
            $table->foreign('coa_item_id')
                ->references('id')
                ->on('coa_template_items')
                ->onDelete('cascade');
        });
    }
};
