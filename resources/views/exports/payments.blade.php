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
                    <div style="font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; color: #78716c; margin-bottom: 4px;">Total Payments</div>
                    <div style="font-size: 18px; font-weight: 800; color: #292524;">{{ $payments->count() }}</div>
                </td>
                <td style="width: 33%; text-align: center; border: 1px solid #e7e5e4; padding: 12px;">
                    <div style="font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; color: #78716c; margin-bottom: 4px;">Total Amount</div>
                    <div style="font-size: 18px; font-weight: 800; color: #16a34a;">GHS {{ number_format($payments->sum('amount'), 2) }}</div>
                </td>
                <td style="width: 33%; text-align: center; border: 1px solid #e7e5e4; padding: 12px;">
                    <div style="font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; color: #78716c; margin-bottom: 4px;">Unique Students</div>
                    <div style="font-size: 18px; font-weight: 800; color: #292524;">{{ $payments->pluck('student_id')->unique()->count() }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Receipt</th>
                <th>Student</th>
                <th>Class</th>
                <th>Fee Type</th>
                <th>Method</th>
                <th class="text-right">Amount (GHS)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $index => $payment)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</td>
                <td style="font-family: monospace; font-size: 9px;">{{ $payment->receipt_number }}</td>
                <td class="font-bold">{{ $payment->student->first_name }} {{ $payment->student->last_name }}</td>
                <td>{{ $payment->student->class?->name ?? '—' }}</td>
                <td>{{ ucwords(str_replace('_', ' ', $payment->payment_type)) }}</td>
                <td>
                    <span class="badge badge-{{ $payment->payment_method }}">{{ strtoupper($payment->payment_method) }}</span>
                </td>
                <td class="text-right font-bold">{{ number_format($payment->amount, 2) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="7" class="text-right">TOTAL</td>
                <td class="text-right">{{ number_format($payments->sum('amount'), 2) }}</td>
            </tr>
        </tbody>
    </table>
@endsection
