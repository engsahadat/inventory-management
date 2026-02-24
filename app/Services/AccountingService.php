<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * AccountingService - Handle double-entry bookkeeping journal entries
 */
class AccountingService
{
    /**
     * Create a new journal entry with multiple lines
     * 
     * @param string $date Date of the entry (YYYY-MM-DD)
     * @param string $type Type of entry (sale, opening_stock, etc.)
     * @param string $description Description of the transaction
     * @param array $lines Array of journal lines [['account_code' => '1001', 'type' => 'debit', 'amount' => 100]]
     * @return int Journal Entry ID
     */
    public static function createJournalEntry($date, $type, $description, $lines)
    {
        // Validate that debits equal credits
        $totalDebit = 0;
        $totalCredit = 0;
        
        foreach ($lines as $line) {
            if ($line['type'] === 'debit') {
                $totalDebit += $line['amount'];
            } else {
                $totalCredit += $line['amount'];
            }
        }
        
        if (round($totalDebit, 2) != round($totalCredit, 2)) {
            throw new \Exception("Debits ($totalDebit) must equal Credits ($totalCredit)");
        }
        
        // Create journal entry
        $journalEntryId = DB::table('journal_entries')->insertGetId([
            'entry_date' => $date,
            'type' => $type,
            'description' => $description,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        
        // Create journal lines
        foreach ($lines as $line) {
            // Get account ID from account code
            $account = DB::table('accounts')->where('code', $line['account_code'])->first();
            
            if (!$account) {
                throw new \Exception("Account code {$line['account_code']} not found");
            }
            
            DB::table('journal_lines')->insert([
                'journal_entry_id' => $journalEntryId,
                'account_id' => $account->id,
                'type' => $line['type'],
                'amount' => $line['amount'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
        
        return $journalEntryId;
    }
    
    /**
     * Create journal entry for a sale transaction
     * 
     * @param array $saleData Sale data including subtotal, discount, vat, paid, due, invoice_number, sale_date
     * @param array $saleItems Array of sale items [['product_id' => x, 'quantity' => y]]
     * @return int Journal Entry ID
     */
    public static function createSaleJournalEntry($saleData, $saleItems)
    {
        // Calculate total amount
        $netRevenue = $saleData['subtotal'] - $saleData['discount_amount'];
        $totalAmount = $netRevenue + $saleData['vat_amount'];
        
        // Journal Entry 1: Record the sale (revenue side)
        $saleLines = [];
        
        // Calculate actual cash received (can't be more than total amount)
        // If customer pays more, the change is not recorded as we give it back
        $cashReceived = min($saleData['paid_amount'], $totalAmount);
        $amountDue = max(0, $totalAmount - $saleData['paid_amount']);
        
        // 1. Record Cash Received (if any)
        if ($cashReceived > 0) {
            $saleLines[] = [
                'account_code' => '1001', // Cash
                'type' => 'debit',
                'amount' => $cashReceived
            ];
        }
        
        // 2. Record Accounts Receivable (if any amount is due)
        if ($amountDue > 0) {
            $saleLines[] = [
                'account_code' => '1002', // Accounts Receivable
                'type' => 'debit',
                'amount' => $amountDue
            ];
        }
        
        // 3. Record Sales Revenue (after discount, before VAT)
        $saleLines[] = [
            'account_code' => '4001', // Sales Revenue
            'type' => 'credit',
            'amount' => $netRevenue
        ];
        
        // 4. Record VAT Payable (if any)
        if ($saleData['vat_amount'] > 0) {
            $saleLines[] = [
                'account_code' => '2002', // VAT Payable
                'type' => 'credit',
                'amount' => $saleData['vat_amount']
            ];
        }
        
        // Create the sale journal entry
        $journalEntryId = self::createJournalEntry(
            $saleData['sale_date'],
            'sale',
            'Sale to ' . $saleData['customer_name'] . ' - Invoice ' . $saleData['invoice_number'],
            $saleLines
        );
        
        // Journal Entry 2: Record Cost of Goods Sold (cost side)
        $totalCost = 0;
        foreach ($saleItems as $item) {
            $product = DB::table('products')->where('id', $item['product_id'])->first();
            $cost = $product->purchase_price * $item['quantity'];
            $totalCost += $cost;
        }
        
        if ($totalCost > 0) {
            $cogsLines = [];
            
            // DR COGS
            $cogsLines[] = [
                'account_code' => '5001', // Cost of Goods Sold
                'type' => 'debit',
                'amount' => $totalCost
            ];
            
            // CR Inventory
            $cogsLines[] = [
                'account_code' => '1003', // Inventory
                'type' => 'credit',
                'amount' => $totalCost
            ];
            
            // Create the COGS journal entry
            self::createJournalEntry(
                $saleData['sale_date'],
                'cogs',
                'Cost of Goods Sold - Invoice ' . $saleData['invoice_number'],
                $cogsLines
            );
        }
        
        return $journalEntryId;
    }
    
    /**
     * Get account balance
     * 
     * @param int $accountId Account ID
     * @param string|null $startDate Start date for balance
     * @param string|null $endDate End date for balance
     * @return float Account balance
     */
    public static function getAccountBalance($accountId, $startDate = null, $endDate = null)
    {
        $query = DB::table('journal_lines')
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_lines.account_id', $accountId);
        
        if ($startDate) {
            $query->where('journal_entries.entry_date', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('journal_entries.entry_date', '<=', $endDate);
        }
        
        $result = $query->selectRaw('
            SUM(CASE WHEN journal_lines.type = "debit" THEN journal_lines.amount ELSE 0 END) as total_debit,
            SUM(CASE WHEN journal_lines.type = "credit" THEN journal_lines.amount ELSE 0 END) as total_credit
        ')->first();
        
        $totalDebit = $result->total_debit ?? 0;
        $totalCredit = $result->total_credit ?? 0;
        
        // Get account type to determine normal balance
        $account = DB::table('accounts')->where('id', $accountId)->first();
        
        // Assets, Expenses = Debit balance (Debit - Credit)
        // Liabilities, Equity, Revenue = Credit balance (Credit - Debit)
        if (in_array($account->type, ['Asset', 'Expense'])) {
            return $totalDebit - $totalCredit;
        } else {
            return $totalCredit - $totalDebit;
        }
    }
    
    /**
     * Get all accounts with their balances
     * 
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public static function getAccountsWithBalances($startDate = null, $endDate = null)
    {
        $accounts = DB::table('accounts')->orderBy('code')->get();
        
        $result = [];
        foreach ($accounts as $account) {
            $balance = self::getAccountBalance($account->id, $startDate, $endDate);
            
            $result[] = [
                'id' => $account->id,
                'code' => $account->code,
                'name' => $account->name,
                'type' => $account->type,
                'balance' => $balance
            ];
        }
        
        return $result;
    }
}
