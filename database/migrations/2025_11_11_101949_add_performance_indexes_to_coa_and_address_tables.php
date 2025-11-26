<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes to COA relationship tables for faster lookups
        if (Schema::hasTable('coa_item_business_types')) {
            Schema::table('coa_item_business_types', function (Blueprint $table) {
                if (!$this->hasIndex('coa_item_business_types', 'idx_coa_item_bt_business_type_id')) {
                    $table->index('business_type_id', 'idx_coa_item_bt_business_type_id');
                }
                if (!$this->hasIndex('coa_item_business_types', 'idx_coa_item_bt_account_item_id')) {
                    $table->index('account_item_id', 'idx_coa_item_bt_account_item_id');
                }
            });
        }

        if (Schema::hasTable('coa_item_industry_types')) {
            Schema::table('coa_item_industry_types', function (Blueprint $table) {
                if (!$this->hasIndex('coa_item_industry_types', 'idx_coa_item_it_industry_type_id')) {
                    $table->index('industry_type_id', 'idx_coa_item_it_industry_type_id');
                }
                if (!$this->hasIndex('coa_item_industry_types', 'idx_coa_item_it_account_item_id')) {
                    $table->index('account_item_id', 'idx_coa_item_it_account_item_id');
                }
            });
        }

        if (Schema::hasTable('coa_item_tax_types')) {
            Schema::table('coa_item_tax_types', function (Blueprint $table) {
                if (!$this->hasIndex('coa_item_tax_types', 'idx_coa_item_tt_tax_type_id')) {
                    $table->index('tax_type_id', 'idx_coa_item_tt_tax_type_id');
                }
                if (!$this->hasIndex('coa_item_tax_types', 'idx_coa_item_tt_account_item_id')) {
                    $table->index('account_item_id', 'idx_coa_item_tt_account_item_id');
                }
            });
        }

        // Add indexes to business registration relationship tables for faster inserts/queries
        if (Schema::hasTable('business_registration_industry_types')) {
            Schema::table('business_registration_industry_types', function (Blueprint $table) {
                if (!$this->hasIndex('business_registration_industry_types', 'idx_br_it_business_registration_id')) {
                    $table->index('business_registration_id', 'idx_br_it_business_registration_id');
                }
                if (!$this->hasIndex('business_registration_industry_types', 'idx_br_it_industry_type_id')) {
                    $table->index('industry_type_id', 'idx_br_it_industry_type_id');
                }
            });
        }

        if (Schema::hasTable('business_registration_tax_types')) {
            Schema::table('business_registration_tax_types', function (Blueprint $table) {
                if (!$this->hasIndex('business_registration_tax_types', 'idx_br_tt_business_registration_id')) {
                    $table->index('business_registration_id', 'idx_br_tt_business_registration_id');
                }
                if (!$this->hasIndex('business_registration_tax_types', 'idx_br_tt_tax_type_id')) {
                    $table->index('tax_type_id', 'idx_br_tt_tax_type_id');
                }
            });
        }

        if (Schema::hasTable('business_government_registrations')) {
            Schema::table('business_government_registrations', function (Blueprint $table) {
                if (!$this->hasIndex('business_government_registrations', 'idx_bgr_business_registration_id')) {
                    $table->index('business_registration_id', 'idx_bgr_business_registration_id');
                }
                if (!$this->hasIndex('business_government_registrations', 'idx_bgr_government_agency_id')) {
                    $table->index('government_agency_id', 'idx_bgr_government_agency_id');
                }
                if (!$this->hasIndex('business_government_registrations', 'idx_bgr_is_active')) {
                    $table->index('is_active', 'idx_bgr_is_active');
                }
            });
        }

        // Add composite indexes for common query patterns
        if (Schema::hasTable('business_registrations')) {
            Schema::table('business_registrations', function (Blueprint $table) {
                if (!$this->hasIndex('business_registrations', 'idx_br_user_active')) {
                    $table->index(['user_id', 'is_active'], 'idx_br_user_active');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes from COA relationship tables
        if (Schema::hasTable('coa_item_business_types')) {
            Schema::table('coa_item_business_types', function (Blueprint $table) {
                $table->dropIndex('idx_coa_item_bt_business_type_id');
                $table->dropIndex('idx_coa_item_bt_account_item_id');
            });
        }

        if (Schema::hasTable('coa_item_industry_types')) {
            Schema::table('coa_item_industry_types', function (Blueprint $table) {
                $table->dropIndex('idx_coa_item_it_industry_type_id');
                $table->dropIndex('idx_coa_item_it_account_item_id');
            });
        }

        if (Schema::hasTable('coa_item_tax_types')) {
            Schema::table('coa_item_tax_types', function (Blueprint $table) {
                $table->dropIndex('idx_coa_item_tt_tax_type_id');
                $table->dropIndex('idx_coa_item_tt_account_item_id');
            });
        }

        // Drop indexes from business registration relationship tables
        if (Schema::hasTable('business_registration_industry_types')) {
            Schema::table('business_registration_industry_types', function (Blueprint $table) {
                $table->dropIndex('idx_br_it_business_registration_id');
                $table->dropIndex('idx_br_it_industry_type_id');
            });
        }

        if (Schema::hasTable('business_registration_tax_types')) {
            Schema::table('business_registration_tax_types', function (Blueprint $table) {
                $table->dropIndex('idx_br_tt_business_registration_id');
                $table->dropIndex('idx_br_tt_tax_type_id');
            });
        }

        if (Schema::hasTable('business_government_registrations')) {
            Schema::table('business_government_registrations', function (Blueprint $table) {
                $table->dropIndex('idx_bgr_business_registration_id');
                $table->dropIndex('idx_bgr_government_agency_id');
                $table->dropIndex('idx_bgr_is_active');
            });
        }

        // Drop composite indexes
        if (Schema::hasTable('business_registrations')) {
            Schema::table('business_registrations', function (Blueprint $table) {
                $table->dropIndex('idx_br_user_active');
            });
        }
    }

    /**
     * Check if an index exists on a table (PostgreSQL).
     */
    private function hasIndex(string $tableName, string $indexName): bool
    {
        $result = DB::selectOne(
            "SELECT EXISTS (
                SELECT 1
                FROM pg_indexes
                WHERE tablename = ? AND indexname = ?
            ) as exists",
            [$tableName, $indexName]
        );

        return $result->exists;
    }
};
