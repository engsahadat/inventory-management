<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\AccountingService;


class SaleController extends Controller
{
    /**
     * Display list of sales
     */
    public function index()
    {
        try {
            $sales = DB::table('sales')
                ->orderBy('sale_date', 'desc')
                ->orderBy('id', 'desc')
                ->get();
            
            return view('sales.index', compact('sales'));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error loading sales: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Show form to create new sale
     */
    public function create()
    {
        try {
            $products = DB::table('products')
                ->where('current_stock', '>', 0)
                ->orderBy('name')
                ->get();
            
            // Generate invoice number
            $lastSale = DB::table('sales')->orderBy('id', 'desc')->first();
            $nextNumber = $lastSale ? (intval(substr($lastSale->invoice_number, 4)) + 1) : 1;
            $invoiceNumber = 'INV-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
            
            return view('sales.create', compact('products', 'invoiceNumber'));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error loading create sale form: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Store a new sale
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'sale_date' => 'required|date',
                'customer_name' => 'nullable|string|max:255',
                'products' => 'required|array|min:1',
                'products.*.product_id' => 'required|exists:products,id',
                'products.*.quantity' => 'required|integer|min:1',
                'discount_amount' => 'required|numeric|min:0',
                'vat_percentage' => 'required|numeric|min:0|max:100',
                'paid_amount' => 'required|numeric|min:0',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }
        
        // Validate stock availability
        foreach ($request->products as $item) {
            $product = DB::table('products')->where('id', $item['product_id'])->first();
            if ($product->current_stock < $item['quantity']) {
                $errorMsg = "Insufficient stock for {$product->name}. Available: {$product->current_stock}";
                
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'message' => $errorMsg,
                        'errors' => ['products' => [$errorMsg]]
                    ], 422);
                }
                
                return back()->withErrors(['products' => $errorMsg])->withInput();
            }
        }
        
        // Calculate totals
        $subtotal = 0;
        $saleItems = [];
        
        foreach ($request->products as $item) {
            $product = DB::table('products')->where('id', $item['product_id'])->first();
            $itemTotal = $product->sell_price * $item['quantity'];
            $subtotal += $itemTotal;
            
            $saleItems[] = [
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $product->sell_price,
                'total' => $itemTotal,
            ];
        }
        
        $discount = $request->discount_amount;
        $amountAfterDiscount = $subtotal - $discount;
        $vatAmount = ($amountAfterDiscount * $request->vat_percentage) / 100;
        $total = $amountAfterDiscount + $vatAmount;
        $paid = $request->paid_amount;
        $due = $total - $paid;
        
        // Generate invoice number if not provided
        $invoiceNumber = $request->invoice_number;
        if (!$invoiceNumber) {
            $lastSale = DB::table('sales')->orderBy('id', 'desc')->first();
            $nextNumber = $lastSale ? (intval(substr($lastSale->invoice_number, 4)) + 1) : 1;
            $invoiceNumber = 'INV-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
        }
        
        // Start transaction
        DB::beginTransaction();
        
        try {
            // Create sale record
            $saleId = DB::table('sales')->insertGetId([
                'invoice_number' => $invoiceNumber,
                'sale_date' => $request->sale_date,
                'customer_name' => $request->customer_name,
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'vat_percentage' => $request->vat_percentage,
                'vat_amount' => $vatAmount,
                'total_amount' => $total,
                'paid_amount' => $paid,
                'due_amount' => $due,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            
            // Create sale items and update stock
            foreach ($saleItems as $item) {
                DB::table('sale_items')->insert([
                    'sale_id' => $saleId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
                
                // Update product stock
                DB::table('products')
                    ->where('id', $item['product_id'])
                    ->decrement('current_stock', $item['quantity']);
                
                // Record stock movement
                DB::table('stock_movements')->insert([
                    'product_id' => $item['product_id'],
                    'type' => 'sale',
                    'quantity' => -$item['quantity'], // Negative for sale
                    'reference_type' => 'sale',
                    'reference_id' => $saleId,
                    'notes' => 'Sale - ' . $invoiceNumber,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
            
            // Create journal entry using AccountingService
            $saleData = [
                'invoice_number' => $invoiceNumber,
                'sale_date' => $request->sale_date,
                'customer_name' => $request->customer_name,
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'vat_amount' => $vatAmount,
                'paid_amount' => $paid,
                'due_amount' => $due,
            ];
            
            $journalEntryId = AccountingService::createSaleJournalEntry($saleData, $saleItems);
            
            // Link journal entry to sale
            DB::table('sales')->where('id', $saleId)->update([
                'journal_entry_id' => $journalEntryId,
            ]);
            
            DB::commit();
            
            // Return JSON for AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Sale created successfully!',
                    'redirect' => route('sales.show', $saleId)
                ], 200);
            }
            
            return redirect()->route('sales.show', $saleId)->with('success', 'Sale created successfully!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Return JSON error for AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Error creating sale: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->withErrors(['error' => 'Error creating sale: ' . $e->getMessage()])->withInput();
        }
    }
    
    /**
     * Display sale details
     */
    public function show($id)
    {
        try {
            $sale = DB::table('sales')->where('id', $id)->first();
            
            if (!$sale) {
                return redirect()->route('sales.index')->with('error', 'Sale not found');
            }
            
            $saleItems = DB::table('sale_items')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->where('sale_items.sale_id', $id)
                ->select('sale_items.*', 'products.name as product_name', 'products.sku')
                ->get();
            
            // Get ALL journal entries related to this sale (revenue + COGS)
            // Search by invoice number in description
            $journalEntries = DB::table('journal_entries')
                ->where('description', 'like', '%' . $sale->invoice_number . '%')
                ->orderBy('id')
                ->get();
            
            $allJournalLines = [];
            foreach ($journalEntries as $entry) {
                $lines = DB::table('journal_lines')
                    ->join('accounts', 'journal_lines.account_id', '=', 'accounts.id')
                    ->where('journal_lines.journal_entry_id', $entry->id)
                    ->select('journal_lines.*', 'accounts.code', 'accounts.name as account_name')
                    ->get();
                
                $allJournalLines[] = [
                    'entry' => $entry,
                    'lines' => $lines
                ];
            }
            
            return view('sales.show', compact('sale', 'saleItems', 'journalEntries', 'allJournalLines'));
        } catch (\Exception $e) {
            return redirect()->route('sales.index')->withErrors(['error' => 'Error loading sale details: ' . $e->getMessage()]);
        }
    }
}
