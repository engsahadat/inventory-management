@extends('layouts.app')

@section('title', __('Dashboard - Inventory Management'))

@section('content')
<div class="page-header">
    <h2>{{ __('Dashboard') }}</h2>
    <p>{{ __('Overview of your inventory and sales') }}</p>
</div>

<div class="grid grid-4">
    <div class="stat-card" style="border-left-color: #667eea;">
        <div class="stat-label">{{ __('Total Products') }}</div>
        <div class="stat-value">{{ $totalProducts }}</div>
    </div>
    
    <div class="stat-card" style="border-left-color: #48bb78;">
        <div class="stat-label">{{ __('Total Stock Value') }}</div>
        <div class="stat-value">৳{{ number_format($totalStockValue, 2) }}</div>
    </div>
    
    <div class="stat-card" style="border-left-color: #ed8936;">
        <div class="stat-label">{{ __('Today\'s Sales') }}</div>
        <div class="stat-value">৳{{ number_format($todaySales, 2) }}</div>
    </div>
    
    <div class="stat-card" style="border-left-color: #9f7aea;">
        <div class="stat-label">{{ __('Total Sales') }}</div>
        <div class="stat-value">৳{{ number_format($totalSales, 2) }}</div>
    </div>
</div>

<div class="grid grid-2">
    <div class="card">
        <div class="card-header">
            <h3>{{ __('Recent Sales') }}</h3>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Invoice #') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Total') }}</th>
                        <th>{{ __('Paid') }}</th>
                        <th>{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentSales as $sale)
                        <tr>
                            <td><strong>{{ $sale->invoice_number }}</strong></td>
                            <td>{{ date('d M Y', strtotime($sale->sale_date)) }}</td>
                            <td>৳{{ number_format($sale->total_amount, 2) }}</td>
                            <td>৳{{ number_format($sale->paid_amount, 2) }}</td>
                            <td>
                                @if($sale->due_amount <= 0)
                                    <span class="badge badge-success">{{ __('Paid') }}</span>
                                @else
                                    <span class="badge badge-warning">{{ __('Due') }}: ৳{{ number_format($sale->due_amount, 2) }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">{{ __('No sales yet') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            <a href="{{ route('sales.index') }}" class="btn btn-secondary">{{ __('View All Sales') }}</a>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3>{{ __('Low Stock Alert') }}</h3>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Product') }}</th>
                        <th>{{ __('SKU') }}</th>
                        <th>{{ __('Stock') }}</th>
                        <th>{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lowStockProducts as $product)
                        <tr>
                            <td><strong>{{ $product->name }}</strong></td>
                            <td>{{ $product->sku }}</td>
                            <td>{{ $product->current_stock }}</td>
                            <td>
                                @if($product->current_stock == 0)
                                    <span class="badge badge-danger">{{ __('Out of Stock') }}</span>
                                @else
                                    <span class="badge badge-warning">{{ __('Low Stock') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">{{ __('All products are in stock') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            <a href="{{ route('products.index') }}" class="btn btn-secondary">{{ __('Manage Products') }}</a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header flex justify-between items-center">
        <h3>{{ __('Quick Actions') }}</h3>
    </div>
    <div class="grid grid-3">
        <a href="{{ route('products.create') }}" class="btn btn-primary" style="text-align: center;">➡ {{ __('Add New Product') }}</a>
        <a href="{{ route('sales.create') }}" class="btn btn-success" style="text-align: center;">🛒 {{ __('Create Sale') }}</a>
        <a href="{{ route('reports.financial') }}" class="btn btn-secondary" style="text-align: center;">📊 {{ __('View Reports') }}</a>
    </div>
</div>
@endsection
