@extends('layouts.app')

@section('title', __('Sales - Inventory Management'))

@section('content')
<div class="page-header flex justify-between items-center">
    <div>
        <h2>{{ __('Sales') }}</h2>
        <p>{{ __('View and manage sales transactions') }}</p>
    </div>
    <a href="{{ route('sales.create') }}" class="btn btn-primary">🛒 {{ __('Create New Sale') }}</a>
</div>

<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>{{ __('Invoice #') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Customer') }}</th>
                    <th>{{ __('Subtotal') }}</th>
                    <th>{{ __('Discount') }}</th>
                    <th>{{ __('VAT') }}</th>
                    <th>{{ __('Total') }}</th>
                    <th>{{ __('Paid') }}</th>
                    <th>{{ __('Due') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                    <tr>
                        <td><strong>{{ $sale->invoice_number }}</strong></td>
                        <td>{{ date('d M Y', strtotime($sale->sale_date)) }}</td>
                        <td>{{ $sale->customer_name }}</td>
                        <td>৳{{ number_format($sale->subtotal, 2) }}</td>
                        <td>৳{{ number_format($sale->discount_amount, 2) }}</td>
                        <td>৳{{ number_format($sale->vat_amount, 2) }}</td>
                        <td><strong>৳{{ number_format($sale->total_amount, 2) }}</strong></td>
                        <td>৳{{ number_format($sale->paid_amount, 2) }}</td>
                        <td>
                            @if($sale->due_amount <= 0)
                                <span class="badge badge-success">৳0.00</span>
                            @else
                                <span class="badge badge-warning">৳{{ number_format($sale->due_amount, 2) }}</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-secondary" style="padding: 0.375rem 0.75rem; font-size: 0.75rem;">{{ __('View') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center">{{ __('No sales found.') }} <a href="{{ route('sales.create') }}">{{ __('Create your first sale') }}</a></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
