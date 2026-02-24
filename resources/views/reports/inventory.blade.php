@extends('layouts.app')

@section('title', __('Inventory Report - Inventory Management'))

@section('content')
<div class="page-header">
    <h2>{{ __('Inventory Report') }}</h2>
    <p>{{ __('Current stock levels and values') }}</p>
</div>

<div class="grid grid-3">
    <div class="stat-card" style="border-left-color: #667eea;">
        <div class="stat-label">{{ __('Total Products') }}</div>
        <div class="stat-value">{{ $products->count() }}</div>
    </div>
    
    <div class="stat-card" style="border-left-color: #48bb78;">
        <div class="stat-label">{{ __('Total Stock Quantity') }}</div>
        <div class="stat-value">{{ $products->sum('stock_quantity') }}</div>
    </div>
    
    <div class="stat-card" style="border-left-color: #ed8936;">
        <div class="stat-label">{{ __('Total Stock Value') }}</div>
        <div class="stat-value">৳{{ number_format($products->sum(function($p) { return $p->stock_quantity * $p->purchase_price; }), 2) }}</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>{{ __('Inventory Details') }}</h3>
    </div>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>{{ __('SKU') }}</th>
                    <th>{{ __('Product Name') }}</th>
                    <th>{{ __('Category') }}</th>
                    <th class="text-right">{{ __('Purchase Price') }}</th>
                    <th class="text-right">{{ __('Sell Price') }}</th>
                    <th class="text-right">{{ __('Stock Qty') }}</th>
                    <th class="text-right">{{ __('Stock Value') }}</th>
                    <th class="text-right">{{ __('Potential Revenue') }}</th>
                    <th>{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    @php
                        $stockValue = $product->current_stock * $product->purchase_price;
                        $potentialRevenue = $product->current_stock * $product->sell_price;
                        $potentialProfit = $potentialRevenue - $stockValue;
                    @endphp
                    <tr>
                        <td><strong>{{ $product->sku }}</strong></td>
                        <td>{{ $product->name }}</td>
                        <td><span class="badge badge-info">{{ $product->category }}</span></td>
                        <td class="text-right">৳{{ number_format($product->purchase_price, 2) }}</td>
                        <td class="text-right">৳{{ number_format($product->sell_price, 2) }}</td>
                        <td class="text-right">
                            @if($product->stock_quantity == 0)
                                <span class="badge badge-danger">{{ $product->stock_quantity }}</span>
                            @elseif($product->stock_quantity <= 10)
                                <span class="badge badge-warning">{{ $product->stock_quantity }}</span>
                            @else
                                <strong>{{ $product->stock_quantity }}</strong>
                            @endif
                        </td>
                        <td class="text-right"><strong>৳{{ number_format($stockValue, 2) }}</strong></td>
                        <td class="text-right" style="color: #48bb78;">৳{{ number_format($potentialRevenue, 2) }}</td>
                        <td>
                            @if($product->current_stock == 0)
                                <span class="badge badge-danger">{{ __('Out of Stock') }}</span>
                            @elseif($product->current_stock <= 10)
                                <span class="badge badge-warning">{{ __('Low Stock') }}</span>
                            @else
                                <span class="badge badge-success">{{ __('In Stock') }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">{{ __('No products in inventory. Add your first product') }}</td>
                    </tr>
                @endforelse
                
                @if($products->count() > 0)
                    <tr style="background: #f7fafc; font-weight: 700; border-top: 2px solid #cbd5e0;">
                        <td colspan="5" class="text-right">{{ __('Total:') }}</td>
                        <td class="text-right">{{ $products->sum('current_stock') }}</td>
                        <td class="text-right">৳{{ number_format($products->sum(function($p) { return $p->current_stock * $p->purchase_price; }), 2) }}</td>
                        <td class="text-right" style="color: #48bb78;">৳{{ number_format($products->sum(function($p) { return $p->current_stock * $p->sell_price; }), 2) }}</td>
                        <td></td>
                    </tr>
                    <tr style="background: #c6f6d5; font-weight: 700;">
                        <td colspan="7" class="text-right">{{ __('Potential Gross Profit') }}:</td>
                        <td class="text-right" style="color: #22543d;">
                            ৳{{ number_format($products->sum(function($p) { return $p->current_stock * ($p->sell_price - $p->purchase_price); }), 2) }}
                        </td>
                        <td></td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

<div class="grid grid-2">
    <div class="card">
        <div class="card-header">
            <h3>{{ __('Stock Status Breakdown') }}</h3>
        </div>
        
        @php
            $outOfStock = $products->where('current_stock', 0)->count();
            $lowStock = $products->where('current_stock', '>', 0)->where('current_stock', '<=', 10)->count();
            $inStock = $products->where('current_stock', '>', 10)->count();
        @endphp
        
        <div style="padding: 1rem 0;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; padding: 1rem; background: #fed7d7; border-radius: 6px;">
                <span style="font-weight: 600; color: #742a2a;">{{ __('Out of Stock') }}</span>
                <span style="font-weight: 700; font-size: 1.25rem; color: #742a2a;">{{ $outOfStock }}</span>
            </div>
            
            <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; padding: 1rem; background: #feebc8; border-radius: 6px;">
                <span style="font-weight: 600; color: #744210;">{{ __('Low Stock (≤ 10)') }}</span>
                <span style="font-weight: 700; font-size: 1.25rem; color: #744210;">{{ $lowStock }}</span>
            </div>
            
            <div style="display: flex; justify-content: space-between; padding: 1rem; background: #c6f6d5; border-radius: 6px;">
                <span style="font-weight: 600; color: #22543d;">{{ __('In Stock (> 10)') }}</span>
                <span style="font-weight: 700; font-size: 1.25rem; color: #22543d;">{{ $inStock }}</span>
            </div>
        </div>
    </div>
    
    <div class="card" style="background: #f7fafc;">
        <h3 style="margin-bottom: 1rem; color: #4a5568;">{{ __('📊 Inventory Insights') }}</h3>
        <div style="color: #718096; line-height: 1.7;">
            <p style="margin-bottom: 1rem;"><strong>{{ __('Stock Value') }}:</strong> {{ __('Total value of inventory at purchase price (Assets on balance sheet)') }}</p>
            <p style="margin-bottom: 1rem;"><strong>{{ __('Potential Revenue') }}:</strong> {{ __('Total revenue if all stock is sold at current selling price') }}</p>
            <p style="margin-bottom: 1rem;"><strong>{{ __('Potential Profit') }}:</strong> {{ __('Expected gross profit if all current stock is sold') }}</p>
            <p style="margin-bottom: 1.5rem;"><strong>{{ __('Status Alerts') }}:</strong> {{ __('Monitor stock levels to avoid stockouts and ensure smooth operations') }}</p>
            
            <a href="{{ route('products.create') }}" class="btn btn-primary" style="width: 100%; text-align: center;">{{ __('Add New Product') }}</a>
        </div>
    </div>
</div>
@endsection
