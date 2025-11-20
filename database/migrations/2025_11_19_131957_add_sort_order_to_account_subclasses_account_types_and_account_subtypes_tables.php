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
        // Add sortOrder to account_subclasses
        Schema::table('account_subclasses', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(1)->after('name');
        });

        DB::statement('ALTER TABLE account_subclasses ADD CONSTRAINT account_subclasses_sort_order_check CHECK (sort_order >= 1)');

        // Add sortOrder to account_types
        Schema::table('account_types', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(1)->after('name');
        });

        DB::statement('ALTER TABLE account_types ADD CONSTRAINT account_types_sort_order_check CHECK (sort_order >= 1)');

        // Add sortOrder to account_subtypes
        Schema::table('account_subtypes', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('name');
        });

        DB::statement('ALTER TABLE account_subtypes ADD CONSTRAINT account_subtypes_sort_order_check CHECK (sort_order >= 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove sortOrder from account_subtypes
        Schema::table('account_subtypes', function (Blueprint $table) {
            DB::statement('ALTER TABLE account_subtypes DROP CONSTRAINT IF EXISTS account_subtypes_sort_order_check');
            $table->dropColumn('sort_order');
        });

        // Remove sortOrder from account_types
        Schema::table('account_types', function (Blueprint $table) {
            DB::statement('ALTER TABLE account_types DROP CONSTRAINT IF EXISTS account_types_sort_order_check');
            $table->dropColumn('sort_order');
        });

        // Remove sortOrder from account_subclasses
        Schema::table('account_subclasses', function (Blueprint $table) {
            DB::statement('ALTER TABLE account_subclasses DROP CONSTRAINT IF EXISTS account_subclasses_sort_order_check');
            $table->dropColumn('sort_order');
        });
    }
};
