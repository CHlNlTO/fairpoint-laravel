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
            $table->string('account_class', 100)->nullable()->after('account_code');
            $table->string('account_subclass', 100)->nullable()->after('account_class');
            $table->string('account_type', 100)->nullable()->after('account_subclass');
            $table->string('account_subtype', 100)->nullable()->after('account_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_coa_items', function (Blueprint $table) {
            $table->dropColumn(['account_class', 'account_subclass', 'account_type', 'account_subtype']);
        });
    }
};
