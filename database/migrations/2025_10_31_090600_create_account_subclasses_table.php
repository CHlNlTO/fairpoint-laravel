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
        Schema::create('account_subclasses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('account_class_id');
            $table->unsignedInteger('code');
            $table->string('name', 100);
            $table->boolean('is_active')->default(true);
            $table->string('description', 500)->nullable();
            $table->string('hint', 200)->nullable();
            $table->timestampsTz();

            $table->foreign('account_class_id')
                ->references('id')->on('account_classes')
                ->onDelete('cascade');

            $table->unique(['account_class_id', 'code'], 'account_subclasses_unique_code_per_class');
            $table->index('account_class_id', 'idx_account_subclasses_class_id');
            $table->index('code', 'idx_account_subclasses_code');
            $table->index('is_active', 'idx_account_subclasses_active');
        });

        DB::statement("ALTER TABLE account_subclasses ADD CONSTRAINT account_subclasses_code_check CHECK (code >= 1 AND code <= 9)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_subclasses');
    }
};
