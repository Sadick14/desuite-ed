@extends('exports.layout')

@section('content')
    @if(isset($filterLabel))
    <div class="filter-info">
        <strong>Filters:</strong> {{ $filterLabel }}
    </div>
    @endif

    {{-- Summary Cards --}}
    <table style="margin-bottom: 24px;">
        <tr>
            <td style="width: 20%; text-align: center; border: 1px solid #e7e5e4; padding: 12px;">
                <div style="font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; color: #78716c; margin-bottom: 4px;">Total Students</div>
                <div style="font-size: 16px; font-weight: 800; color: #292524;">{{ number_format($analytics['summary']['totalStudents']) }}</div>
            </td>
            <td style="width: 20%; text-align: center; border: 1px solid #e7e5e4; padding: 12px;">
                <div style="font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; color: #78716c; margin-bottom: 4px;">Total Revenue</div>
                <div style="font-size: 16px; font-weight: 800; color: #16a34a;">GHS {{ number_format($analytics['summary']['totalRevenue'], 2) }}</div>
            </td>
            <td style="width: 20%; text-align: center; border: 1px solid #e7e5e4; padding: 12px;">
                <div style="font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; color: #78716c; margin-bottom: 4px;">Total Expenses</div>
                <div style="font-size: 16px; font-weight: 800; color: #dc2626;">GHS {{ number_format($analytics['summary']['totalExpenses'], 2) }}</div>
            </td>
            <td style="width: 20%; text-align: center; border: 1px solid #e7e5e4; padding: 12px;">
                <div style="font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; color: #78716c; margin-bottom: 4px;">Net Profit</div>
                <div style="font-size: 16px; font-weight: 800; color: {{ $analytics['summary']['netProfit'] >= 0 ? '#16a34a' : '#dc2626' }};">GHS {{ number_format($analytics['summary']['netProfit'], 2) }}</div>
            </td>
            <td style="width: 20%; text-align: center; border: 1px solid #e7e5e4; padding: 12px;">
                <div style="font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; color: #78716c; margin-bottom: 4px;">Payment Rate</div>
                <div style="font-size: 16px; font-weight: 800; color: #292524;">{{ $analytics['summary']['paymentRate'] }}%</div>
            </td>
        </tr>
    </table>

    {{-- Revenue by Term --}}
    <h3 class="section-title">Revenue by Term</h3>
    <table>
        <thead>
            <tr>
                <th>Term</th>
                <th class="text-right">Revenue (GHS)</th>
                <th class="text-right">Expected (GHS)</th>
                <th class="text-right">Collection Rate</th>
            </tr>
        </thead>
        <tbody>
            @foreach($analytics['revenueByTerm'] as $item)
            <tr>
                <td class="font-bold">{{ $item['term'] }}</td>
                <td class="text-right">{{ number_format($item['revenue'], 2) }}</td>
                <td class="text-right">{{ number_format($item['expected'], 2) }}</td>
                <td class="text-right">{{ $item['expected'] > 0 ? number_format(($item['revenue'] / $item['expected']) * 100, 1) : 0 }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Students by Level --}}
    <h3 class="section-title">Students by Level</h3>
    <table>
        <thead>
            <tr>
                <th>Level / Class</th>
                <th class="text-right">Count</th>
            </tr>
        </thead>
        <tbody>
            @foreach($analytics['studentsByLevel'] as $item)
            <tr>
                <td class="font-bold">{{ $item['level'] }}</td>
                <td class="text-right">{{ $item['count'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Expenses by Category --}}
    <h3 class="section-title">Expenses by Category</h3>
    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th class="text-right">Amount (GHS)</th>
                <th class="text-right">Percentage</th>
            </tr>
        </thead>
        <tbody>
            @foreach($analytics['expensesByCategory'] as $item)
            <tr>
                <td class="font-bold">{{ $item['category'] }}</td>
                <td class="text-right">{{ number_format($item['amount'], 2) }}</td>
                <td class="text-right">{{ $item['percentage'] }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Monthly Trend --}}
    <h3 class="section-title">Monthly Trend</h3>
    <table>
        <thead>
            <tr>
                <th>Month</th>
                <th class="text-right">Revenue (GHS)</th>
                <th class="text-right">Expenses (GHS)</th>
                <th class="text-right">Net (GHS)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($analytics['monthlyTrend'] as $item)
            <tr>
                <td class="font-bold">{{ $item['month'] }}</td>
                <td class="text-right" style="color: #16a34a;">{{ number_format($item['revenue'], 2) }}</td>
                <td class="text-right" style="color: #dc2626;">{{ number_format($item['expenses'], 2) }}</td>
                <td class="text-right font-bold">{{ number_format($item['revenue'] - $item['expenses'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endsection
