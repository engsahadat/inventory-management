<?php

namespace App\Http\Controllers;

use App\Services\AccountingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\StockMovement;

class ProductController extends Controller
{
    /**
     * Display list of products
     */
    public function index()
    {
        try {
            $products = DB::table('products')
                ->orderBy('name')
                ->get();
            
            return view('products.index', compact('products'));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error loading products: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Show form to create new product
     */
    public function create()
    {
        try {
            return view('products.create');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error loading create product form: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Store a new product
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'sku' => 'nullable|string|max:100|unique:products,sku',
                'category' => 'required|string|max:100',
                'purchase_price' => 'required|numeric|min:0',
                'sell_price' => 'required|numeric|min:0',
                'opening_stock' => 'required|integer|min:0',
                'description' => 'nullable|string',
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
        
        DB::beginTransaction();
        
        try {
            // Create product
            $productId = DB::table('products')->insertGetId([
                'name' => $request->name,
                'sku' => $request->sku,
                'category' => $request->category,
                'purchase_price' => $request->purchase_price,
                'sell_price' => $request->sell_price,
                'current_stock' => $request->opening_stock,
                'description' => $request->description,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            
            // Record stock movement
            if ($request->opening_stock > 0) {
                DB::table('stock_movements')->insert([
                    'product_id' => $productId,
                    'type' => 'opening_stock',
                    'quantity' => $request->opening_stock,
                    'reference_type' => 'opening',
                    'reference_id' => $productId,
                    'notes' => 'Opening stock for ' . $request->name,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
                
                // Create journal entry for opening stock using AccountingService
                $openingValue = $request->opening_stock * $request->purchase_price;
                
                AccountingService::createJournalEntry(
                    date: Carbon::today()->toDateString(),
                    type: 'opening_stock',
                    description: 'Opening stock for ' . $request->name . ' (' . $request->sku . ')',
                    lines: [
                        [
                            'account_code' => '1003', // Inventory
                            'type' => 'debit',
                            'amount' => $openingValue
                        ],
                        [
                            'account_code' => '3001', // Retained Earnings
                            'type' => 'credit',
                            'amount' => $openingValue
                        ]
                    ]
                );
            }
            
            DB::commit();
            
            // Return JSON for AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Product created successfully!',
                    'redirect' => route('products.index')
                ], 200);
            }
            
            return redirect()->route('products.index')->with('success', 'Product created successfully!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Return JSON error for AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Error creating product: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->withErrors(['error' => 'Error creating product: ' . $e->getMessage()])->withInput();
        }
    }
    
    /**
     * Show edit form for product
     */
    public function edit($id)
    {
        try {
            $product = DB::table('products')->where('id', $id)->first();
            
            if (!$product) {
                return redirect()->route('products.index')->with('error', 'Product not found');
            }
            
            return view('products.edit', compact('product'));
        } catch (\Exception $e) {
            return redirect()->route('products.index')->withErrors(['error' => 'Error loading edit product form: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Update product
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'sku' => 'nullable|string|max:100|unique:products,sku,' . $id,
                'category' => 'required|string|max:100',
                'purchase_price' => 'required|numeric|min:0',
                'sell_price' => 'required|numeric|min:0',
                'description' => 'nullable|string',
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
        
        try {
            DB::table('products')->where('id', $id)->update([
                'name' => $request->name,
                'sku' => $request->sku,
                'category' => $request->category,
                'purchase_price' => $request->purchase_price,
                'sell_price' => $request->sell_price,
                'description' => $request->description,
                'updated_at' => Carbon::now(),
            ]);
            
            // Return JSON for AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Product updated successfully!',
                    'redirect' => route('products.index')
                ], 200);
            }
            
            return redirect()->route('products.index')->with('success', 'Product updated successfully!');
        } catch (\Exception $e) {
            // Return JSON error for AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Error updating product: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->withErrors(['error' => 'Error updating product: ' . $e->getMessage()])->withInput();
        }
    }
    
    /**
     * Delete product
     */
    public function destroy($id)
    {
        try {
            DB::table('products')->where('id', $id)->delete();
            
            return redirect()->route('products.index')->with('success', 'Product deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->route('products.index')->withErrors(['error' => 'Error deleting product: ' . $e->getMessage()]);
        }
    }
}
