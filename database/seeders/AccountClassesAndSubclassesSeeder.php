<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountClassesAndSubclassesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('account_classes')->upsert([
            [
                'id' => '019a339f-a6ca-706c-b1d1-66e6cd040d17',
                'code' => 1,
                'name' => 'Assets',
                'normal_balance' => 'debit',
                'is_active' => true,
                'description' => 'Resources owned by the business that provide future economic benefit',
                'hint' => 'Assets increase with debits and decrease with credits',
            ],
            [
                'id' => '019a33a0-3da5-737c-ab0e-55b8bc8f4ea0',
                'code' => 2,
                'name' => 'Liabilities',
                'normal_balance' => 'credit',
                'is_active' => true,
                'description' => 'Obligations the business owes, to be settled in money, goods, or services',
                'hint' => 'Liabilities increase with credits and decrease with debits',
            ],
        ], ['id'], ['code', 'name', 'normal_balance', 'is_active', 'description', 'hint']);

        DB::table('account_subclasses')->upsert([
            [
                'id' => '019a33bb-375b-726d-bbd9-2e56aeeace1f',
                'account_class_id' => '019a339f-a6ca-706c-b1d1-66e6cd040d17',
                'code' => 1,
                'name' => 'Current Assets',
                'is_active' => true,
                'description' => 'Assets that are expected to be converted to cash within one year',
                'hint' => 'Include cash, receivables, inventory, and prepaid expenses',
            ],
        ], ['id'], ['account_class_id', 'code', 'name', 'is_active', 'description', 'hint']);
    }
}
