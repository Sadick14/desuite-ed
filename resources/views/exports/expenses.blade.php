@extends('exports.layout')

@section('content')
    @if(isset($filterLabel))
    <div class="filter-info">
        <strong>Filters:</strong> {{ $filterLabel }}
    </div>
    @endif

    <div class="summary-row">
        <table style="margin-bottom: 20px;">
            <tr>
                <td style="width: 33%; text-align: center; border: 1px solid #e7e5e4; padding: 12px;">
                    <div style="font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; color: #78716c; margin-bottom: 4px;">Total Expenses</div>
                    <div style="font-size: 18px; font-weight: 800; color: #292524;">{{ $expenses->count() }}</div>
                </td>
                <td style="width: 33%; text-align: center; border: 1px solid #e7e5e4; padding: 12px;">
                    <div style="font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; color: #78716c; margin-bottom: 4px;">Total Amount</div>
                    <div style="font-size: 18px; font-weight: 800; color: #dc2626;">GHS {{ number_format($expenses->sum('amount'), 2) }}</div>
                </td>
                <td style="width: 33%; text-align: center; border: 1px solid #e7e5e4; padding: 12px;">
                    <div style="font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; color: #78716c; margin-bottom: 4px;">Categories</div>
                    <div style="font-size: 18px; font-weight: 800; color: #292524;">{{ $expenses->pluck('expense_category_id')->unique()->count() }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Category breakdown --}}
    @php
        $byCategory = $expenses->groupBy(fn($e) => $e->category->name ?? 'Uncategorized');
    @endphp

    <h3 class="section-title">Expense Breakdown by Category</h3>
    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th class="text-right">Count</th>
                <th class="text-right">Total (GHS)</th>
                <th class="text-right">% of Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($byCategory as $cat => $items)
            <tr>
                <td class="font-bold">{{ $cat }}</td>
                <td class="text-right">{{ $items->count() }}</td>
                <td class="text-right">{{ number_format($items->sum('amount'), 2) }}</td>
                <td class="text-right">{{ $expenses->sum('amount') > 0 ? number_format(($items->sum('amount') / $expenses->sum('amount')) * 100, 1) : 0 }}%</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td>TOTAL</td>
                <td class="text-right">{{ $expenses->count() }}</td>
                <td class="text-right">{{ number_format($expenses->sum('amount'), 2) }}</td>
                <td class="text-right">100%</td>
            </tr>
        </tbody>
    </table>

    <h3 class="section-title">Detailed Expense Log</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Title</th>
                <th>Category</th>
                <th>Method</th>
                <th>Recorded By</th>
                <th class="text-right">Amount (GHS)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expenses as $index => $expense)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($expense->expense_date)->format('d M Y') }}</td>
                <td class="font-bold">{{ $expense->title }}</td>
                <td>{{ $expense->category->name ?? '—' }}</td>
                <td>
                    <span class="badge badge-{{ $expense->payment_method ?? 'other' }}">{{ strtoupper($expense->payment_method ?? 'N/A') }}</span>
                </td>
                <td>{{ $expense->user->name ?? '—' }}</td>
                <td class="text-right font-bold">{{ number_format($expense->amount, 2) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="6" class="text-right">TOTAL</td>
                <td class="text-right">{{ number_format($expenses->sum('amount'), 2) }}</td>
            </tr>
        </tbody>
    </table>
@endsection
