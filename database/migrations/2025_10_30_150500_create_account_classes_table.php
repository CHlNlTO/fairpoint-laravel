<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_classes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedInteger('code');
            $table->string('name', 100);
            $table->enum('normal_balance', ['debit', 'credit']);
            $table->boolean('is_active')->default(true);
            $table->string('description', 500)->nullable();
            $table->string('hint', 200)->nullable();
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();

            $table->unique('code');
            $table->index('code', 'idx_account_classes_code');
            $table->index('is_active', 'idx_account_classes_active');
        });

        DB::statement("ALTER TABLE account_classes ADD CONSTRAINT account_classes_code_check CHECK (code >= 1)");
    }

    public function down(): void
    {
        Schema::dropIfExists('account_classes');
    }
};
