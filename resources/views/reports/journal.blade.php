@extends('layouts.app')

@section('title', __('Journal Entries - Inventory Management'))

@section('content')
<div class="page-header">
    <h2>{{ __('Journal Entries') }}</h2>
    <p>{{ __('All accounting transactions in the system') }}</p>
</div>

<div class="card" style="background: #f7fafc; border: 2px solid #cbd5e0;">
    <h3 style="margin-bottom: 1rem; color: #4a5568;">📚 {{ __('About Journal Entries') }}</h3>
    <div style="color: #718096; line-height: 1.7;">
        <p style="margin-bottom: 0.5rem;">{{ __('This report shows all journal entries created by the system using double-entry bookkeeping. Each entry must have equal debits and credits.') }}</p>
        <p><strong>{{ __('Common Entry Types') }}:</strong> {{ __('Opening Stock, Sales Transactions, Inventory Adjustments') }}</p>
    </div>
</div>

@forelse($journalData as $item)
    <div class="card">
        <div class="card-header flex justify-between items-center">
            <div>
                <h3>{{ __('Journal Entry #') }}{{ $item['entry']->id }}</h3>
                <p style="color: #718096; font-size: 0.875rem; margin-top: 0.25rem;">
                    {{ date('d F Y', strtotime($item['entry']->entry_date)) }} | {{ $item['entry']->description }}
                </p>
            </div>
            <span class="badge badge-info">{{ ucfirst($item['entry']->type) }}</span>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Account Name') }}</th>
                        <th>{{ __('Account Code') }}</th>
                        <th>{{ __('Account Type') }}</th>
                        <th class="text-right">{{ __('Debit') }}</th>
                        <th class="text-right">{{ __('Credit') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($item['lines'] as $line)
                        <tr>
                            <td><strong>{{ $line->account_name }}</strong></td>
                            <td>{{ $line->code }}</td>
                            <td>
                                @php
                                    $accountType = $line->account_type ?? 'Unknown';
                                    $badgeClass = match($accountType) {
                                        'Asset' => 'badge-success',
                                        'Liability' => 'badge-warning',
                                        'Equity' => 'badge-info',
                                        'Revenue' => 'badge-primary',
                                        'Expense' => 'badge-danger',
                                        default => 'badge-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $accountType }}</span>
                            </td>
                            <td class="text-right">
                                @if($line->type == 'debit')
                                    <strong style="color: #22543d;">৳{{ number_format($line->amount, 2) }}</strong>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-right">
                                @if($line->type == 'credit')
                                    <strong style="color: #742a2a;">৳{{ number_format($line->amount, 2) }}</strong>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    @php
                        $totalDebit = collect($item['lines'])->where('type', 'debit')->sum('amount');
                        $totalCredit = collect($item['lines'])->where('type', 'credit')->sum('amount');
                    @endphp
                    <tr style="background: #f7fafc; font-weight: 700; border-top: 2px solid #cbd5e0;">
                        <td colspan="3" class="text-right">{{ __('Total') }}:</td>
                        <td class="text-right" style="color: #22543d;">
                            ৳{{ number_format($totalDebit, 2) }}
                        </td>
                        <td class="text-right" style="color: #742a2a;">
                            ৳{{ number_format($totalCredit, 2) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        @php
            $isBalanced = abs($totalDebit - $totalCredit) < 0.01;
        @endphp
        
        @if($isBalanced)
            <div class="alert alert-success" style="margin-top: 1rem; margin-bottom: 0;">
                ✅ {{ __('Entry is balanced (Debit = Credit)') }}
            </div>
        @else
            <div class="alert alert-error" style="margin-top: 1rem; margin-bottom: 0;">
                ❌ {{ __('Entry is NOT balanced! Difference: ৳') }}{{ number_format(abs($totalDebit - $totalCredit), 2) }}
            </div>
        @endif
    </div>
@empty
    <div class="card">
        <div class="text-center" style="padding: 2rem;">
            <p style="color: #718096; font-size: 1.125rem;">{{ __('No journal entries found.') }}</p>
            <p style="color: #cbd5e0; margin-top: 0.5rem;">{{ __('Journal entries will be created automatically when you add products with opening stock or create sales.') }}</p>
            <div style="margin-top: 1.5rem;">
                <a href="{{ route('products.create') }}" class="btn btn-primary">{{ __('Add Product') }}</a>
                <a href="{{ route('sales.create') }}" class="btn btn-success">{{ __('Create Sale') }}</a>
            </div>
        </div>
    </div>
@endforelse
@endsection
