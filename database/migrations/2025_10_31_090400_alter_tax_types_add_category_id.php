<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tax_types', function (Blueprint $table) {
            $table->uuid('category_id')->nullable()->after('name');
            $table->index('category_id', 'idx_tax_types_category_id');
            $table->foreign('category_id')->references('id')->on('tax_categories');
        });
    }

    public function down(): void
    {
        Schema::table('tax_types', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropIndex('idx_tax_types_category_id');
            $table->dropColumn('category_id');
        });
    }
};
