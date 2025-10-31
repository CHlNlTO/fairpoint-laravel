<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InitialTablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data for Name: account_classes;
        DB::table('account_classes')->upsert([
            [
                'id' => '019a339f-a6ca-706c-b1d1-66e6cd040d17',
                'code' => '1',
                'name' => 'Assets',
                'normal_balance' => 'debit',
                'is_active' => true,
                'description' => 'Resources owned by the business that provide future economic benefit',
                'hint' => 'Assets increase with debits and decrease with credits',
                'created_at' => '2025-10-31 14:52:44',
                'updated_at' => '2025-10-31 14:52:44',
            ],
            [
                'id' => '019a33a0-3da5-737c-ab0e-55b8bc8f4ea0',
                'code' => '2',
                'name' => 'Liabilities',
                'normal_balance' => 'credit',
                'is_active' => true,
                'description' => 'Obligations the business owes, to be settled in money, goods, or services',
                'hint' => 'Liabilities increase with credits and decrease with debits',
                'created_at' => '2025-10-31 14:52:44',
                'updated_at' => '2025-10-31 14:52:44',
            ],
        ], ['id']);

        // Data for Name: account_subclasses;
        DB::table('account_subclasses')->upsert([
            [
                'id' => '019a33bb-375b-726d-bbd9-2e56aeeace1f',
                'account_class_id' => '019a339f-a6ca-706c-b1d1-66e6cd040d17',
                'code' => '1',
                'name' => 'Current Assets',
                'is_active' => true,
                'description' => 'Assets that are expected to be converted to cash within one year',
                'hint' => 'Include cash, receivables, inventory, and prepaid expenses',
                'created_at' => '2025-10-31 14:52:44',
                'updated_at' => '2025-10-31 14:52:44',
            ],
        ], ['id']);

        // Data for Name: account_types;
        DB::table('account_types')->upsert([
            [
                'id' => '019a390c-86d6-73ed-a04c-2ff6f9f29253',
                'account_subclass_id' => '019a33bb-375b-726d-bbd9-2e56aeeace1f',
                'user_id' => null,
                'business_registration_id' => null,
                'name' => 'Other Current Assets',
                'is_active' => true,
                'is_system_defined' => true,
                'description' => 'Other assets expected to be converted to cash within one year',
                'hint' => 'Include deposits, advances, and other current assets',
                'created_at' => '2025-10-31 14:55:13',
                'updated_at' => '2025-10-31 14:55:13',
                'code' => '1',
            ],
        ], ['id']);

        // Data for Name: account_subtypes;
        DB::table('account_subtypes')->upsert([
            [
                'id' => '019a3914-0ae5-7227-95fa-a9d863bb3718',
                'account_type_id' => '019a390c-86d6-73ed-a04c-2ff6f9f29253',
                'user_id' => null,
                'business_registration_id' => null,
                'name' => 'Other Current Assets',
                'is_active' => true,
                'is_system_defined' => true,
                'description' => 'Other assets expected to be converted to cash within one year',
                'hint' => 'Include deposits, advances, and other current assets',
                'created_at' => '2025-10-31 15:03:26',
                'updated_at' => '2025-10-31 15:03:26',
                'code' => '1',
            ],
        ], ['id']);

        // Data for Name: business_types;
        DB::table('business_types')->upsert([
            [
                'id' => '019a3908-19c5-70c7-a928-a478283d7aa3',
                'name' => 'Freelancing',
                'description' => 'Independent professional or self-employed worker offering services to clients on a per-project or contract basis.',
                'hint' => 'Select this if you personally offer your skills or services directly to clients rather than running a registered company.',
                'is_active' => true,
                'created_at' => '2025-10-31 14:50:23',
                'updated_at' => '2025-10-31 14:50:23',
            ],
        ], ['id']);

        // Data for Name: fiscal_year_periods;
        DB::table('fiscal_year_periods')->upsert([
            [
                'id' => '019a3909-9778-7017-bb78-8dfdccc82ded',
                'name' => 'Calendar Year',
                'start_month' => 1,
                'start_day' => 1,
                'end_month' => 12,
                'end_day' => 31,
                'description' => 'January 1 to December 31',
                'is_default' => true,
                'is_active' => true,
                'created_at' => '2025-10-31 14:52:01',
                'updated_at' => '2025-10-31 14:52:01',
            ],
        ], ['id']);

        // Data for Name: industry_types;
        DB::table('industry_types')->upsert([
            [
                'id' => '019a3908-d4b8-7097-8c51-cc9b0907cd15',
                'name' => 'Services',
                'description' => 'An industry focused on providing professional skills, labor, or expertise to clients rather than producing physical goods.',
                'hint' => 'Select this if your business primarily offers non-tangible products such as consulting, repairs, design, education, or technical support.',
                'is_active' => true,
                'created_at' => '2025-10-31 14:51:11',
                'updated_at' => '2025-10-31 14:51:27',
            ],
        ], ['id']);

        // Data for Name: tax_categories;
        DB::table('tax_categories')->upsert([
            [
                'id' => '019a3905-40fd-735d-8601-a72fcbc8aaf6',
                'name' => 'Income Tax',
                'description' => null,
                'hint' => null,
                'is_active' => true,
                'created_at' => '2025-10-31 14:47:16',
                'updated_at' => '2025-10-31 14:47:16',
            ],
            [
                'id' => '019a3905-c1cf-73d4-939e-731ae276063c',
                'name' => 'Business Tax',
                'description' => null,
                'hint' => null,
                'is_active' => true,
                'created_at' => '2025-10-31 14:47:49',
                'updated_at' => '2025-10-31 14:47:49',
            ],
            [
                'id' => '019a3906-2b15-71e5-8af0-6a4b9fe70a61',
                'name' => 'Additional Tax',
                'description' => null,
                'hint' => null,
                'is_active' => true,
                'created_at' => '2025-10-31 14:48:16',
                'updated_at' => '2025-10-31 14:48:35',
            ],
        ], ['id']);

        // Data for Name: tax_types;
        DB::table('tax_types')->upsert([
            [
                'id' => '019a3907-0d9d-724d-b56e-5f9d9293c29b',
                'name' => 'Graduated Tax Rate',
                'description' => null,
                'hint' => null,
                'is_active' => true,
                'created_at' => '2025-10-31 14:49:14',
                'updated_at' => '2025-10-31 14:49:14',
                'category_id' => '019a3905-40fd-735d-8601-a72fcbc8aaf6',
            ],
        ], ['id']);
    }
}
