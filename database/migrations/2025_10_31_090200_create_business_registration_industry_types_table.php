<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_registration_industry_types', function (Blueprint $table) {
            $table->uuid('business_registration_id');
            $table->uuid('industry_type_id');

            $table->primary(['business_registration_id', 'industry_type_id'], 'br_industry_types_pkey');
            $table->index('industry_type_id', 'idx_br_industry_types_industry_type_id');

            $table->foreign('business_registration_id')
                ->references('id')->on('business_registrations')
                ->onDelete('cascade');

            $table->foreign('industry_type_id')
                ->references('id')->on('industry_types')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_registration_industry_types');
    }
};
