<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            // Assets (1000-1999)
            ['code' => '1001', 'name' => 'Cash', 'type' => 'Asset', 'normal_balance' => 'debit', 'description' => 'Cash on hand and in bank'],
            ['code' => '1002', 'name' => 'Accounts Receivable', 'type' => 'Asset', 'normal_balance' => 'debit', 'description' => 'Money owed by customers'],
            ['code' => '1003', 'name' => 'Inventory', 'type' => 'Asset', 'normal_balance' => 'debit', 'description' => 'Products available for sale'],
            ['code' => '1004', 'name' => 'Prepaid Expenses', 'type' => 'Asset', 'normal_balance' => 'debit', 'description' => 'Expenses paid in advance'],
            
            // Liabilities (2000-2999)
            ['code' => '2001', 'name' => 'Accounts Payable', 'type' => 'Liability', 'normal_balance' => 'credit', 'description' => 'Money owed to suppliers'],
            ['code' => '2002', 'name' => 'VAT Payable', 'type' => 'Liability', 'normal_balance' => 'credit', 'description' => 'VAT collected from customers'],
            ['code' => '2003', 'name' => 'Salaries Payable', 'type' => 'Liability', 'normal_balance' => 'credit', 'description' => 'Unpaid salaries'],
            
            // Equity (3000-3999)
            ['code' => '3001', 'name' => 'Retained Earnings', 'type' => 'Equity', 'normal_balance' => 'credit', 'description' => 'Accumulated profits'],
            ['code' => '3002', 'name' => 'Owner\'s Capital', 'type' => 'Equity', 'normal_balance' => 'credit', 'description' => 'Owner investment'],
            
            // Revenue (4000-4999)
            ['code' => '4001', 'name' => 'Sales Revenue', 'type' => 'Revenue', 'normal_balance' => 'credit', 'description' => 'Revenue from product sales'],
            ['code' => '4002', 'name' => 'Service Revenue', 'type' => 'Revenue', 'normal_balance' => 'credit', 'description' => 'Revenue from services'],
            ['code' => '4003', 'name' => 'Other Income', 'type' => 'Revenue', 'normal_balance' => 'credit', 'description' => 'Miscellaneous income'],
            
            // Expenses (5000-5999)
            ['code' => '5001', 'name' => 'Cost of Goods Sold', 'type' => 'Expense', 'normal_balance' => 'debit', 'description' => 'Direct cost of products sold'],
            ['code' => '5002', 'name' => 'Operating Expenses', 'type' => 'Expense', 'normal_balance' => 'debit', 'description' => 'General operating costs'],
            ['code' => '5003', 'name' => 'Rent Expense', 'type' => 'Expense', 'normal_balance' => 'debit', 'description' => 'Monthly rent payments'],
            ['code' => '5004', 'name' => 'Utilities Expense', 'type' => 'Expense', 'normal_balance' => 'debit', 'description' => 'Electricity, water, internet'],
        ];

        foreach ($accounts as $account) {
            DB::table('accounts')->insert(array_merge($account, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
