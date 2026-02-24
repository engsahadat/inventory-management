<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\AccountingService;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Account;

class ReportController extends Controller
{
    /**
     * Display financial report
     */
    public function financial(Request $request)
    {
        try {
            // Get date filters
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            
            // If no dates provided, use current month
            if (!$startDate) {
                $startDate = date('Y-m-01');
            }
            if (!$endDate) {
                $endDate = date('Y-m-d');
            }
            
            // Get sales data
            $salesQuery = DB::table('sales')
                ->whereBetween('sale_date', [$startDate, $endDate]);
            
            $totalSales = $salesQuery->sum('total_amount');
            $totalRevenue = $salesQuery->sum(DB::raw('subtotal - discount_amount'));
            $totalVAT = $salesQuery->sum('vat_amount');
            $totalDiscount = $salesQuery->sum('discount_amount');
            $totalPaid = $salesQuery->sum('paid_amount');
            $totalDue = $salesQuery->sum('due_amount');
            
            // Get COGS (Cost of Goods Sold) - Account code 5001
            $cogsAccount = DB::table('accounts')->where('code', '5001')->first();
            $totalCOGS = $cogsAccount ? AccountingService::getAccountBalance($cogsAccount->id, $startDate, $endDate) : 0;
            
            // Get expenses (excluding COGS which is already counted)
            $expenses = DB::table('accounts')
                ->where('type', 'Expense')
                ->where('code', '!=', '5001') // Exclude COGS
                ->get();
            
            $expenseData = [];
            $totalExpenses = 0;
            foreach ($expenses as $expense) {
                $balance = AccountingService::getAccountBalance($expense->id, $startDate, $endDate);
                if ($balance != 0) {
                    $expenseData[] = [
                        'name' => $expense->name,
                        'amount' => $balance
                    ];
                    $totalExpenses += $balance;
                }
            }
            
            // Calculate gross profit and net profit
            $grossProfit = $totalRevenue - $totalCOGS;
            $netProfit = $grossProfit - $totalExpenses;
            
            // Get sales by date with revenue and COGS
            // Revenue: sum of (subtotal - discount_amount) per date from sales table
            // COGS: sum of (quantity * purchase_price) per date from sale_items + products
            $salesByDate = [];
            
            // Get revenue by date
            $revenueByDate = DB::table('sales')
                ->selectRaw('DATE(sale_date) as date, SUM(subtotal - discount_amount) as revenue')
                ->whereBetween('sale_date', [$startDate, $endDate])
                ->groupBy('date')
                ->pluck('revenue', 'date');
            
            // Get COGS by date
            $cogsByDate = DB::table('sale_items')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->selectRaw('DATE(sales.sale_date) as date, SUM(sale_items.quantity * products.purchase_price) as cogs')
                ->whereBetween('sales.sale_date', [$startDate, $endDate])
                ->groupBy('date')
                ->pluck('cogs', 'date');
            
            // Combine revenue and COGS by date
            $allDates = array_unique(array_merge(array_keys($revenueByDate->toArray()), array_keys($cogsByDate->toArray())));
            foreach ($allDates as $date) {
                $salesByDate[$date] = (object) [
                    'revenue' => $revenueByDate[$date] ?? 0,
                    'cogs' => $cogsByDate[$date] ?? 0,
                ];
            }
            
            // Sort by date descending
            krsort($salesByDate);
            
            // Create summary array for the view
            $summary = [
                'total_sales' => $totalSales,
                'total_revenue' => $totalRevenue,
                'total_vat' => $totalVAT,
                'total_discounts' => $totalDiscount,
                'total_paid' => $totalPaid,
                'total_due' => $totalDue,
                'total_cogs' => $totalCOGS,
                'total_expenses' => $totalExpenses,
                'gross_profit' => $grossProfit,
                'net_profit' => $netProfit,
            ];
            
            return view('reports.financial', compact(
                'startDate', 
                'endDate', 
                'summary',
                'expenseData',
                'salesByDate'
            ));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error loading financial report: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Display journal entries
     */
    public function journal(Request $request)
    {
        try {
            // Get date filters
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            
            // If no dates provided, use current month
            if (!$startDate) {
                $startDate = date('Y-m-01');
            }
            if (!$endDate) {
                $endDate = date('Y-m-d');
            }
            
            // Get journal entries
            $journalEntries = DB::table('journal_entries')
                ->whereBetween('entry_date', [$startDate, $endDate])
                ->orderBy('entry_date', 'desc')
                ->orderBy('id', 'desc')
                ->get();
            
            // Get journal lines for each entry
            $journalData = [];
            foreach ($journalEntries as $entry) {
                $lines = DB::table('journal_lines')
                    ->join('accounts', 'journal_lines.account_id', '=', 'accounts.id')
                    ->where('journal_lines.journal_entry_id', $entry->id)
                    ->select('journal_lines.*', 'accounts.code', 'accounts.name as account_name', 'accounts.type as account_type')
                    ->get();
                
                $journalData[] = [
                    'entry' => $entry,
                    'lines' => $lines
                ];
            }
            
            return view('reports.journal', compact('journalData', 'startDate', 'endDate'));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error loading journal entries: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Display inventory report
     */
    public function inventory()
    {
        try {
            $products = DB::table('products')
                ->select(
                    'products.*',
                    DB::raw('(products.current_stock * products.purchase_price) as stock_value')
                )
                ->orderBy('name')
                ->get();
            
            $totalStockValue = $products->sum('stock_value');
            $totalStockQuantity = $products->sum('current_stock');
            
            return view('reports.inventory', compact('products', 'totalStockValue', 'totalStockQuantity'));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error loading inventory report: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Display chart of accounts
     */
    public function accounts()
    {
        try {
            $accounts = AccountingService::getAccountsWithBalances();
            
            return view('reports.accounts', compact('accounts'));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error loading chart of accounts: ' . $e->getMessage()]);
        }
    }
}
