<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Sale;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            // Get overview statistics
            $totalProducts = DB::table('products')->count();
            $totalStockValue = DB::table('products')
                ->selectRaw('SUM(current_stock * purchase_price) as value')
                ->first()->value ?? 0;
            
            $todaySales = DB::table('sales')
                ->whereDate('sale_date', today())
                ->sum('total_amount') ?? 0;
            
            $totalSales = DB::table('sales')->sum('total_amount') ?? 0;
            
            $totalDue = DB::table('sales')->sum('due_amount') ?? 0;
            
            // Recent sales
            $recentSales = DB::table('sales')
                ->orderBy('sale_date', 'desc')
                ->orderBy('id', 'desc')
                ->limit(5)
                ->get();
            
            // Low stock products
            $lowStockProducts = DB::table('products')
                ->where('current_stock', '<=', 10)
                ->orderBy('current_stock')
                ->limit(5)
                ->get();
            
            return view('dashboard', compact(
                'totalProducts',
                'totalStockValue',
                'todaySales',
                'totalSales',
                'totalDue',
                'recentSales',
                'lowStockProducts'
            ));
        } catch (\Exception $e) {
            // Return view with empty data and error
            return view('dashboard', [
                'totalProducts' => 0,
                'totalStockValue' => 0,
                'todaySales' => 0,
                'totalSales' => 0,
                'totalDue' => 0,
                'recentSales' => collect(),
                'lowStockProducts' => collect(),
                'error' => 'Error loading dashboard: ' . $e->getMessage()
            ]);
        }
    }
}
