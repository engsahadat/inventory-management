@extends('layouts.app')

@section('title', 'Sale Details - Inventory Management')

@section('content')
<div class="page-header flex justify-between items-center">
    <div>
        <h2>{{ __('Sale Details') }}</h2>
        <p>Invoice #{{ $sale->invoice_number }}</p>
    </div>
    <a href="{{ route('sales.index') }}" class="btn btn-secondary">← {{ __('Back to Sales') }}</a>
</div>

<div class="grid grid-2">
    <div class="card">
        <h3 style="margin-bottom: 1.5rem;">{{ __('Sale Information') }}</h3>
        
        <div style="margin-bottom: 1rem;">
            <div style="color: #718096; font-size: 0.875rem; margin-bottom: 0.25rem;">Invoice Number</div>
            <div style="font-weight: 600; font-size: 1.125rem;">{{ $sale->invoice_number }}</div>
        </div>
        
        <div style="margin-bottom: 1rem;">
            <div style="color: #718096; font-size: 0.875rem; margin-bottom: 0.25rem;">Customer Name</div>
            <div style="font-weight: 600;">{{ $sale->customer_name }}</div>
        </div>
        
        <div style="margin-bottom: 1rem;">
            <div style="color: #718096; font-size: 0.875rem; margin-bottom: 0.25rem;">Sale Date</div>
            <div style="font-weight: 600;">{{ date('d F Y', strtotime($sale->sale_date)) }}</div>
        </div>
        
        @if($sale->journal_entry_id)
        <div style="margin-bottom: 1rem;">
            <div style="color: #718096; font-size: 0.875rem; margin-bottom: 0.25rem;">Journal Entry</div>
            <div><a href="{{ route('reports.journal') }}" class="badge badge-info">Journal #{{ $sale->journal_entry_id }}</a></div>
        </div>
        @endif
    </div>
    
    <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
        <h3 style="margin-bottom: 1.5rem; color: white;">{{ __('Payment Summary') }}</h3>
        
        <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem; padding-bottom: 0.75rem; border-bottom: 1px solid rgba(255,255,255,0.2);">
            <span>{{ __('Subtotal:') }}</span>
            <span style="font-weight: 600;">৳{{ number_format($sale->subtotal, 2) }}</span>
        </div>
        
        <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem; padding-bottom: 0.75rem; border-bottom: 1px solid rgba(255,255,255,0.2);">
            <span>{{ __('Discount:') }}</span>
            <span style="font-weight: 600;">-৳{{ number_format($sale->discount_amount, 2) }}</span>
        </div>
        
        <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem; padding-bottom: 0.75rem; border-bottom: 1px solid rgba(255,255,255,0.2);">
            <span>{{ __('VAT') }} ({{ $sale->vat_percentage }}%):</span>
            <span style="font-weight: 600;">৳{{ number_format($sale->vat_amount, 2) }}</span>
        </div>
        
        <div style="display: flex; justify-content: space-between; margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 2px solid rgba(255,255,255,0.3);">
            <span style="font-size: 1.25rem; font-weight: 700;">{{ __('Total Amount:') }}</span>
            <span style="font-size: 1.5rem; font-weight: 700;">৳{{ number_format($sale->total_amount, 2) }}</span>
        </div>
        
        <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
            <span>{{ __('Paid Amount:') }}</span>
            <span style="font-weight: 600;">৳{{ number_format($sale->paid_amount, 2) }}</span>
        </div>
        
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 1.125rem; font-weight: 600;">{{ __('Due Amount:') }}</span>
            <span style="font-size: 1.5rem; font-weight: 700; padding: 0.5rem 1rem; background: rgba(255,255,255,0.2); border-radius: 9999px;">
                ৳{{ number_format($sale->due_amount, 2) }}
            </span>
        </div>
    </div>
</div>

<div class="card">
    <h3 style="margin-bottom: 1.5rem;">{{ __('Sale Items') }}</h3>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>{{ __('Product') }}</th>
                    <th>{{ __('SKU') }}</th>
                    <th class="text-right">{{ __('Quantity') }}</th>
                    <th class="text-right">{{ __('Unit Price') }}</th>
                    <th class="text-right">{{ __('Total') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($saleItems as $item)
                    <tr>
                        <td><strong>{{ $item->product_name }}</strong></td>
                        <td>{{ $item->sku }}</td>
                        <td class="text-right">{{ $item->quantity }}</td>
                        <td class="text-right">৳{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right"><strong>৳{{ number_format($item->total_price, 2) }}</strong></td>
                    </tr>
                @endforeach
                <tr style="background: #f7fafc; font-weight: 600;">
                    <td colspan="4" class="text-right">{{ __('Subtotal:') }}</td>
                    <td class="text-right">৳{{ number_format($sale->subtotal, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@if(count($allJournalLines) > 0)
<div class="card">
    <h3 style="margin-bottom: 1.5rem;">{{ __('Accounting Journal Entries') }}</h3>
    
    @foreach($allJournalLines as $journalData)
        <div style="margin-bottom: 2rem; @if(!$loop->last) padding-bottom: 2rem; border-bottom: 2px solid #e2e8f0; @endif">
            <div style="margin-bottom: 1rem; padding: 0.75rem; background: #f7fafc; border-radius: 6px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <span class="badge badge-info">{{ __('Entry') }} #{{ $journalData['entry']->id }}</span>
                        <span style="margin-left: 1rem; color: #718096;">{{ $journalData['entry']->description }}</span>
                    </div>
                    <span style="color: #718096; font-size: 0.875rem;">{{ date('d M Y', strtotime($journalData['entry']->entry_date)) }}</span>
                </div>
            </div>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('Account') }}</th>
                            <th>{{ __('Account Type') }}</th>
                            <th class="text-right">{{ __('Debit') }}</th>
                            <th class="text-right">{{ __('Credit') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($journalData['lines'] as $line)
                            <tr>
                                <td><strong>{{ $line->account_name }}</strong></td>
                                <td><span class="badge badge-info">{{ $line->code }}</span></td>
                                <td class="text-right">
                                    @if($line->type == 'debit')
                                        <strong>৳{{ number_format($line->amount, 2) }}</strong>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if($line->type == 'credit')
                                        <strong>৳{{ number_format($line->amount, 2) }}</strong>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        @php
                            $totalDebit = collect($journalData['lines'])->where('type', 'debit')->sum('amount');
                            $totalCredit = collect($journalData['lines'])->where('type', 'credit')->sum('amount');
                        @endphp
                        <tr style="background: {{ abs($totalDebit - $totalCredit) < 0.01 ? '#c6f6d5' : '#fed7d7' }}; font-weight: 700; border-top: 2px solid #cbd5e0;">
                            <td colspan="2" class="text-right">
                                <strong>{{ __('Total:') }}</strong>
                                @if(abs($totalDebit - $totalCredit) < 0.01)
                                    <span style="color: #22543d; margin-left: 0.5rem;">✓ {{ __('Balanced') }}</span>
                                @else
                                    <span style="color: #742a2a; margin-left: 0.5rem;">⚠️ {{ __('Unbalanced') }}</span>
                                @endif
                            </td>
                            <td class="text-right">৳{{ number_format($totalDebit, 2) }}</td>
                            <td class="text-right">৳{{ number_format($totalCredit, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
    
    @php
        // Calculate grand totals across all entries
        $grandTotalDebit = 0;
        $grandTotalCredit = 0;
        foreach($allJournalLines as $journalData) {
            $grandTotalDebit += collect($journalData['lines'])->where('type', 'debit')->sum('amount');
            $grandTotalCredit += collect($journalData['lines'])->where('type', 'credit')->sum('amount');
        }
    @endphp
    
    @if(count($allJournalLines) > 1)
    <div style="margin-top: 1.5rem; padding: 1rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 6px; color: white;">
        <div style="display: flex; justify-content: space-between; font-weight: 700; font-size: 1.125rem;">
            <span>{{ __('Grand Total (All Entries):') }}</span>
            <div>
                <span style="margin-right: 2rem;">{{ __('DR:') }} ৳{{ number_format($grandTotalDebit, 2) }}</span>
                <span>{{ __('CR:') }} ৳{{ number_format($grandTotalCredit, 2) }}</span>
                @if(abs($grandTotalDebit - $grandTotalCredit) < 0.01)
                    <span style="margin-left: 1rem; padding: 0.25rem 0.75rem; background: rgba(255,255,255,0.2); border-radius: 9999px;">✓ {{ __('Balanced') }}</span>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
@endif
@endsection
