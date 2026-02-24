@extends('layouts.app')

@section('title', __('Financial Report - Inventory Management'))

@section('content')
<div class="page-header">
    <h2>{{ __('Financial Report') }}</h2>
    <p>{{ __('Revenue, expenses, and profitability analysis') }}</p>
    @if($startDate && $endDate)
        <div style="display: inline-block; background: #EBF8FF; color: #2C5282; padding: 0.5rem 1rem; border-radius: 6px; margin-top: 0.5rem; font-size: 0.875rem;">
            📅 <strong>Period:</strong> {{ date('d M Y', strtotime($startDate)) }} - {{ date('d M Y', strtotime($endDate)) }}
        </div>
    @endif
</div>

<div class="card">
    <form method="GET" action="{{ route('reports.financial') }}" class="flex gap-2" style="align-items: end;">
        <div class="form-group" style="margin-bottom: 0; flex: 1;">
            <label for="start_date">{{ __('Start Date') }}</label>
            <input type="date" id="start_date" name="start_date" value="{{ $startDate }}" required>
        </div>
        
        <div class="form-group" style="margin-bottom: 0; flex: 1;">
            <label for="end_date">{{ __('End Date') }}</label>
            <input type="date" id="end_date" name="end_date" value="{{ $endDate }}" required>
        </div>
        
        <button type="submit" class="btn btn-primary">🔍 {{ __('Filter') }}</button>
        <a href="{{ route('reports.financial') }}" class="btn btn-secondary">{{ __('Clear') }}</a>
    </form>
</div>

<div class="grid grid-4">
    <div class="stat-card" style="border-left-color: #48bb78;">
        <div class="stat-label">{{ __('Total Sales Revenue') }}</div>
        <div class="stat-value" style="color: #48bb78;">৳{{ number_format($summary['total_sales'], 2) }}</div>
    </div>
    
    <div class="stat-card" style="border-left-color: #f56565;">
        <div class="stat-label">{{ __('Total Expenses (COGS)') }}</div>
        <div class="stat-value" style="color: #f56565;">৳{{ number_format($summary['total_cogs'], 2) }}</div>
    </div>
    
    <div class="stat-card" style="border-left-color: #ed8936;">
        <div class="stat-label">{{ __('Total Discounts') }}</div>
        <div class="stat-value" style="color: #ed8936;">৳{{ number_format($summary['total_discounts'], 2) }}</div>
    </div>
    
    <div class="stat-card" style="border-left-color: #667eea;">
        <div class="stat-label">{{ __('Gross Profit') }}</div>
        <div class="stat-value" style="color: {{ $summary['gross_profit'] >= 0 ? '#48bb78' : '#f56565' }};">
            ৳{{ number_format($summary['gross_profit'], 2) }}
        </div>
    </div>
</div>

<div class="grid grid-2">
    <div class="card">
        <div class="card-header">
            <h3>{{ __('Sales by Date') }}</h3>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Date') }}</th>
                        <th class="text-right">{{ __('Total Sales') }}</th>
                        <th class="text-right">{{ __('Total COGS') }}</th>
                        <th class="text-right">{{ __('Profit') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($salesByDate as $date => $data)
                        <tr>
                            <td><strong>{{ date('d M Y', strtotime($date)) }}</strong></td>
                            <td class="text-right">৳{{ number_format($data->revenue, 2) }}</td>
                            <td class="text-right">৳{{ number_format($data->cogs, 2) }}</td>
                            <td class="text-right" style="color: {{ ($data->revenue - $data->cogs) >= 0 ? '#48bb78' : '#f56565' }};">
                                <strong>৳{{ number_format($data->revenue - $data->cogs, 2) }}</strong>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">{{ __('No sales data available for the selected period') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3>{{ __('VAT Summary') }}</h3>
        </div>
        
        <div class="stat-card" style="margin-bottom: 1rem;">
            <div class="stat-label">{{ __('Total VAT Collected') }}</div>
            <div class="stat-value">৳{{ number_format($summary['total_vat'], 2) }}</div>
        </div>
        
        <div style="background: #f7fafc; padding: 1rem; border-radius: 6px; margin-top: 1rem;">
            <h4 style="margin-bottom: 1rem; color: #4a5568;">ℹ️ {{ __('Report Summary') }}</h4>
            <div style="color: #718096; line-height: 1.7;">
                <p style="margin-bottom: 0.5rem;"><strong>Revenue:</strong> Total sales from all invoices</p>
                <p style="margin-bottom: 0.5rem;"><strong>COGS:</strong> Cost of Goods Sold (purchase price × quantity sold)</p>
                <p style="margin-bottom: 0.5rem;"><strong>Gross Profit:</strong> Revenue - COGS - Discounts</p>
                <p style="margin-bottom: 0.5rem;"><strong>VAT:</strong> Total VAT collected from customers</p>
            </div>
        </div>
        
        <div style="margin-top: 1.5rem;">
            <a href="{{ route('reports.accounts') }}" class="btn btn-secondary" style="width: 100%; text-align: center;">View Chart of Accounts</a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>{{ __('Profit & Loss Statement') }}</h3>
    </div>
    
    <table>
        <tbody>
            <tr style="background: #f7fafc;">
                <td><strong>{{ __('Revenue') }}</strong></td>
                <td></td>
            </tr>
            <tr>
                <td style="padding-left: 2rem;">{{ __('Sales Revenue') }}</td>
                <td class="text-right">৳{{ number_format($summary['total_sales'], 2) }}</td>
            </tr>
            <tr style="border-bottom: 2px solid #cbd5e0;">
                <td style="padding-left: 2rem;">{{ __('Less: Discounts') }}</td>
                <td class="text-right">(৳{{ number_format($summary['total_discounts'], 2) }})</td>
            </tr>
            <tr style="font-weight: 600;">
                <td>{{ __('Net Revenue') }}</td>
                <td class="text-right">৳{{ number_format($summary['total_sales'] - $summary['total_discounts'], 2) }}</td>
            </tr>
            
            <tr style="background: #f7fafc;">
                <td><strong>{{ __('Cost of Goods Sold') }}</strong></td>
                <td class="text-right">(৳{{ number_format($summary['total_cogs'], 2) }})</td>
            </tr>
            
            <tr style="background: {{ $summary['gross_profit'] >= 0 ? '#c6f6d5' : '#fed7d7' }}; font-weight: 700; font-size: 1.125rem;">
                <td>{{ __('Gross Profit') }}</td>
                <td class="text-right" style="color: {{ $summary['gross_profit'] >= 0 ? '#22543d' : '#742a2a' }};">
                    ৳{{ number_format($summary['gross_profit'], 2) }}
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endsection
