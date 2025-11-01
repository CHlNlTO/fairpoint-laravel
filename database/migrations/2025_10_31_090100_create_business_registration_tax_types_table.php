<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_registration_tax_types', function (Blueprint $table) {
            $table->uuid('business_registration_id');
            $table->uuid('tax_type_id');

            $table->primary(['business_registration_id', 'tax_type_id'], 'br_tax_types_pkey');
            $table->index('tax_type_id', 'idx_br_tax_types_tax_type_id');

            $table->foreign('business_registration_id')
                ->references('id')->on('business_registrations')
                ->onDelete('cascade');

            $table->foreign('tax_type_id')
                ->references('id')->on('tax_types')
                ->onDelete('cascade');

            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_registration_tax_types');
    }
};
