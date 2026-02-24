@extends('layouts.app')

@section('title', __('Products - Inventory Management'))

@section('content')
<div class="page-header flex justify-between items-center">
    <div>
        <h2>{{ __('Products') }}</h2>
        <p>{{ __('Manage your product inventory') }}</p>
    </div>
    <a href="{{ route('products.create') }}" class="btn btn-primary">➡ {{ __('Add New Product') }}</a>
</div>

<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>{{ __('SKU') }}</th>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Category') }}</th>
                    <th>{{ __('Purchase Price') }}</th>
                    <th>{{ __('Sell Price') }}</th>
                    <th>{{ __('Stock') }}</th>
                    <th>{{ __('Stock Value') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr>
                        <td><strong>{{ $product->sku }}</strong></td>
                        <td>{{ $product->name }}</td>
                        <td><span class="badge badge-info">{{ $product->category }}</span></td>
                        <td>৳{{ number_format($product->purchase_price, 2) }}</td>
                        <td>৳{{ number_format($product->sell_price, 2) }}</td>
                        <td>
                            @if($product->current_stock == 0)
                                <span class="badge badge-danger">{{ $product->current_stock }}</span>
                            @elseif($product->current_stock <= 10)
                                <span class="badge badge-warning">{{ $product->current_stock }}</span>
                            @else
                                <span class="badge badge-success">{{ $product->current_stock }}</span>
                            @endif
                        </td>
                        <td>৳{{ number_format($product->current_stock * $product->purchase_price, 2) }}</td>
                        <td>
                            <div class="flex gap-1">
                                <a href="{{ route('products.edit', $product->id) }}" class="btn btn-secondary" style="padding: 0.375rem 0.75rem; font-size: 0.75rem;">{{ __('Edit') }}</a>
                                <form action="{{ route('products.destroy', $product->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('{{ __('Are you sure you want to delete this product?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" style="padding: 0.375rem 0.75rem; font-size: 0.75rem;">{{ __('Delete') }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">{{ __('No products found.') }} <a href="{{ route('products.create') }}">{{ __('Add your first product') }}</a></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
