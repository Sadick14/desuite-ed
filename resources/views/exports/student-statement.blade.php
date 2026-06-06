@extends('exports.layout')

@section('content')
    {{-- Student Info Card --}}
    <table style="margin-bottom: 24px;">
        <tr>
            <td style="width: 50%; vertical-align: top; padding: 12px; border: 1px solid #e7e5e4;">
                <div style="font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; color: #78716c; margin-bottom: 6px;">Student Information</div>
                <div style="font-size: 14px; font-weight: 800; color: #292524; margin-bottom: 4px;">{{ $student->first_name }} {{ $student->last_name }}</div>
                <div style="font-size: 10px; color: #57534e;">
                    <strong>ID:</strong> {{ $student->student_id }}<br>
                    <strong>Class:</strong> {{ $student->class?->name ?? '—' }}<br>
                    <strong>Gender:</strong> {{ ucfirst($student->gender ?? '—') }}<br>
                    <strong>DOB:</strong> {{ $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('d M Y') : '—' }}
                </div>
            </td>
            <td style="width: 50%; vertical-align: top; padding: 12px; border: 1px solid #e7e5e4;">
                <div style="font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; color: #78716c; margin-bottom: 6px;">Parent / Guardian</div>
                <div style="font-size: 14px; font-weight: 800; color: #292524; margin-bottom: 4px;">{{ $student->parent_name ?? '—' }}</div>
                <div style="font-size: 10px; color: #57534e;">
                    <strong>Phone:</strong> {{ $student->parent_phone ?? '—' }}<br>
                    <strong>Address:</strong> {{ $student->address ?? '—' }}<br>
                    <strong>Admission:</strong> {{ $student->admission_date ? \Carbon\Carbon::parse($student->admission_date)->format('d M Y') : '—' }}
                </div>
            </td>
        </tr>
    </table>

    {{-- Fee Balance Summary --}}
    <table style="margin-bottom: 24px;">
        <tr>
            <td style="width: 33%; text-align: center; border: 1px solid #e7e5e4; padding: 12px;">
                <div style="font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; color: #78716c; margin-bottom: 4px;">Total Expected</div>
                <div style="font-size: 18px; font-weight: 800; color: #292524;">GHS {{ number_format($totalExpected, 2) }}</div>
            </td>
            <td style="width: 33%; text-align: center; border: 1px solid #e7e5e4; padding: 12px;">
                <div style="font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; color: #78716c; margin-bottom: 4px;">Total Paid</div>
                <div style="font-size: 18px; font-weight: 800; color: #16a34a;">GHS {{ number_format($totalPaid, 2) }}</div>
            </td>
            <td style="width: 33%; text-align: center; border: 1px solid #e7e5e4; padding: 12px;">
                <div style="font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; color: #78716c; margin-bottom: 4px;">Outstanding Balance</div>
                <div style="font-size: 18px; font-weight: 800; color: {{ $totalBalance > 0 ? '#dc2626' : '#16a34a' }};">GHS {{ number_format($totalBalance, 2) }}</div>
            </td>
        </tr>
    </table>

    {{-- Fee Breakdown by Type --}}
    @if(count($feeBreakdown) > 0)
    <h3 class="section-title">Fee Breakdown</h3>
    <table>
        <thead>
            <tr>
                <th>Fee Type</th>
                <th>Term</th>
                <th class="text-right">Expected (GHS)</th>
                <th class="text-right">Paid (GHS)</th>
                <th class="text-right">Balance (GHS)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($feeBreakdown as $fee)
            <tr>
                <td class="font-bold">{{ $fee['fee_type'] }}</td>
                <td>{{ $fee['term'] }}</td>
                <td class="text-right">{{ number_format($fee['expected'], 2) }}</td>
                <td class="text-right" style="color: #16a34a;">{{ number_format($fee['paid'], 2) }}</td>
                <td class="text-right font-bold" style="color: {{ $fee['balance'] > 0 ? '#dc2626' : '#16a34a' }};">{{ number_format($fee['balance'], 2) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="2" class="text-right">TOTAL</td>
                <td class="text-right">{{ number_format($totalExpected, 2) }}</td>
                <td class="text-right">{{ number_format($totalPaid, 2) }}</td>
                <td class="text-right">{{ number_format($totalBalance, 2) }}</td>
            </tr>
        </tbody>
    </table>
    @endif

    {{-- Payment History --}}
    <h3 class="section-title">Payment History</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Receipt</th>
                <th>Fee Type</th>
                <th>Method</th>
                <th class="text-right">Amount (GHS)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $index => $payment)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</td>
                <td style="font-family: monospace; font-size: 9px;">{{ $payment->receipt_number }}</td>
                <td>{{ ucwords(str_replace('_', ' ', $payment->payment_type)) }}</td>
                <td>
                    <span class="badge badge-{{ $payment->payment_method }}">{{ strtoupper($payment->payment_method) }}</span>
                </td>
                <td class="text-right font-bold">{{ number_format($payment->amount, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; color: #78716c; padding: 20px;">No payments recorded</td>
            </tr>
            @endforelse
            @if($payments->count() > 0)
            <tr class="total-row">
                <td colspan="5" class="text-right">TOTAL PAID</td>
                <td class="text-right">{{ number_format($payments->sum('amount'), 2) }}</td>
            </tr>
            @endif
        </tbody>
    </table>
@endsection
