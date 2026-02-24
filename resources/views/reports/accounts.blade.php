@extends('layouts.app')

@section('title', __('Chart of Accounts - Inventory Management'))

@section('content')
<div class="page-header">
    <h2>{{ __('Chart of Accounts') }}</h2>
    <p>{{ __('Complete list of all accounts with current balances') }}</p>
</div>

<div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; margin-bottom: 1.5rem;">
    <h3 style="color: white; margin-bottom: 1rem;">📘 {{ __('Double-Entry Bookkeeping') }}</h3>
    <div style="line-height: 1.7;">
        <p style="margin-bottom: 0.5rem;">{{ __('This system uses double-entry accounting where every transaction affects at least two accounts.') }}</p>
        <p><strong>{{ __('Debit Balance') }}:</strong> {{ __('Assets & Expenses') }} | <strong>{{ __('Credit Balance') }}:</strong> {{ __('Liabilities, Equity & Revenue') }}</p>
    </div>
</div>

@php
    $accountTypes = [
        'Asset' => 'Assets increase with debits and decrease with credits',
        'Liability' => 'Liabilities increase with credits and decrease with debits',
        'Equity' => 'Equity increases with credits and decreases with debits',
        'Revenue' => 'Revenue increases with credits',
        'Expense' => 'Expenses increase with debits'
    ];
    
    $typeColors = [
        'Asset' => '#48bb78',
        'Liability' => '#f56565',
        'Equity' => '#667eea',
        'Revenue' => '#38b2ac',
        'Expense' => '#ed8936'
    ];
@endphp

@foreach($accountTypes as $type => $description)
    @php
        $typeAccounts = $accounts->where('type', $type);
    @endphp
    
    @if($typeAccounts->count() > 0)
        <div class="card">
            <div class="card-header flex justify-between items-center">
                <h3 style="color: {{ $typeColors[$type] }};">{{ $type }} Accounts</h3>
                <span class="badge" style="background: {{ $typeColors[$type] }}20; color: {{ $typeColors[$type] }};">
                    {{ $typeAccounts->count() }} accounts
                </span>
            </div>
            
            <div style="background: #f7fafc; padding: 0.75rem 1rem; margin-bottom: 1rem; border-radius: 6px; font-size: 0.875rem; color: #718096;">
                {{ $description }}
            </div>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('Account Code') }}</th>
                            <th>{{ __('Account Name') }}</th>
                            <th>{{ __('Description') }}</th>
                            <th class="text-right">{{ __('Balance') }}</th>
                            <th>{{ __('Normal Balance') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($typeAccounts as $account)
                            @php
                                $balance = $account->balance;
                                $isNormalBalance = ($account->normal_balance == 'debit' && $balance >= 0) || 
                                                 ($account->normal_balance == 'credit' && $balance <= 0);
                            @endphp
                            <tr>
                                <td><strong>{{ $account->code }}</strong></td>
                                <td>{{ $account->name }}</td>
                                <td style="color: #718096; font-size: 0.875rem;">{{ $account->description ?? '-' }}</td>
                                <td class="text-right">
                                    <strong style="color: {{ $balance >= 0 ? '#22543d' : '#742a2a' }};">
                                        ৳{{ number_format(abs($balance), 2) }}
                                        @if($balance < 0)
                                            <span style="font-size: 0.75rem; color: #742a2a;">(CR)</span>
                                        @endif
                                    </strong>
                                </td>
                                <td>
                                    <span class="badge" style="background: {{ $account->normal_balance == 'debit' ? '#c6f6d5' : '#fed7d7' }}; color: {{ $account->normal_balance == 'debit' ? '#22543d' : '#742a2a' }};">
                                        {{ ucfirst($account->normal_balance) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                        
                        <tr style="background: #f7fafc; font-weight: 700; border-top: 2px solid #cbd5e0;">
                            <td colspan="3" class="text-right">{{ __('Total') }} {{ $type }}:</td>
                            <td class="text-right" style="color: {{ $typeColors[$type] }};">
                                ৳{{ number_format(abs($typeAccounts->sum('balance')), 2) }}
                            </td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endforeach

<div class="card" style="background: #f7fafc; border: 2px solid #cbd5e0;">
    <h3 style="margin-bottom: 1.5rem; color: #2d3748;">📊 {{ __('Accounting Equation') }}</h3>
    
    @php
        $totalAssets = abs($accounts->where('type', 'Asset')->sum('balance'));
        $totalLiabilities = abs($accounts->where('type', 'Liability')->sum('balance'));
        $totalEquity = abs($accounts->where('type', 'Equity')->sum('balance'));
        $totalRevenue = abs($accounts->where('type', 'Revenue')->sum('balance'));
        $totalExpense = abs($accounts->where('type', 'Expense')->sum('balance'));
        
        $leftSide = $totalAssets;
        $rightSide = $totalLiabilities + $totalEquity + $totalRevenue - $totalExpense;
        $isBalanced = abs($leftSide - $rightSide) < 0.01;
    @endphp
    
    <div style="font-size: 1.25rem; font-weight: 600; text-align: center; margin-bottom: 2rem; color: #2d3748;">
        {{ __('Assets = Liabilities + Equity + (Revenue - Expenses)') }}
    </div>
    
    <div class="grid grid-3" style="margin-bottom: 1.5rem;">
        <div style="background: white; padding: 1.5rem; border-radius: 8px; text-align: center; border: 2px solid #48bb78;">
            <div style="color: #718096; font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('Assets') }}</div>
            <div style="font-size: 1.875rem; font-weight: 700; color: #48bb78;">৳{{ number_format($totalAssets, 2) }}</div>
        </div>
        
        <div style="background: white; padding: 1.5rem; border-radius: 8px; text-align: center;">
            <div style="color: #718096; font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('Liabilities') }}</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: #f56565;">৳{{ number_format($totalLiabilities, 2) }}</div>
        </div>
        
        <div style="background: white; padding: 1.5rem; border-radius: 8px; text-align: center;">
            <div style="color: #718096; font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('Equity') }}</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: #667eea;">৳{{ number_format($totalEquity, 2) }}</div>
        </div>
    </div>
    
    <div class="grid grid-2" style="margin-bottom: 1.5rem;">
        <div style="background: white; padding: 1.5rem; border-radius: 8px; text-align: center;">
            <div style="color: #718096; font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('Revenue') }}</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: #38b2ac;">৳{{ number_format($totalRevenue, 2) }}</div>
        </div>
        
        <div style="background: white; padding: 1.5rem; border-radius: 8px; text-align: center;">
            <div style="color: #718096; font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('Expenses') }}</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: #ed8936;">৳{{ number_format($totalExpense, 2) }}</div>
        </div>
    </div>
    
    <div style="background: {{ $isBalanced ? '#c6f6d5' : '#fed7d7' }}; padding: 1.5rem; border-radius: 8px; text-align: center;">
        @if($isBalanced)
            <div style="color: #22543d; font-size: 1.125rem; font-weight: 600;">
                ✅ {{ __('Accounting Equation is Balanced') }}
            </div>
            <div style="color: #22543d; margin-top: 0.5rem;">
                ৳{{ number_format($leftSide, 2) }} = ৳{{ number_format($rightSide, 2) }}
            </div>
        @else
            <div style="color: #742a2a; font-size: 1.125rem; font-weight: 600;">
                ❌ {{ __('Accounting Equation is NOT Balanced') }}
            </div>
            <div style="color: #742a2a; margin-top: 0.5rem;">
                {{ __('Difference') }}: ৳{{ number_format(abs($leftSide - $rightSide), 2) }}
            </div>
        @endif
    </div>
</div>
@endsection
