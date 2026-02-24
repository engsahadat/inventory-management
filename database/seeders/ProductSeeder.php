<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Services\AccountingService;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample product
        $productId = DB::table('products')->insertGetId([
            'sku' => 'PRD001',
            'name' => 'Sample Product',
            'category' => 'Electronics',
            'purchase_price' => 100.00,
            'sell_price' => 200.00,
            'current_stock' => 50,
            'description' => 'Sample product for demonstration',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Record stock movement
        DB::table('stock_movements')->insert([
            'product_id' => $productId,
            'type' => 'opening_stock',
            'quantity' => 50,
            'reference_type' => 'opening',
            'reference_id' => $productId,
            'notes' => 'Opening stock for Sample Product',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create journal entry for opening stock
        $accountingService = new AccountingService();
        $journalEntry = $accountingService->createJournalEntry(
            date: now()->toDateString(),
            type: 'opening_stock',
            description: 'Opening stock for Sample Product (PRD001)',
            lines: [
                [
                    'account_code' => '1003', // Inventory
                    'type' => 'debit',
                    'amount' => 5000.00 // 50 units * 100 purchase price
                ],
                [
                    'account_code' => '3001', // Retained Earnings
                    'type' => 'credit',
                    'amount' => 5000.00
                ]
            ]
        );
    }
}
