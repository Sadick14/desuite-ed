@extends('exports.layout')

@section('content')
    @if(isset($filterLabel))
    <div class="filter-info">
        <strong>Filters:</strong> {{ $filterLabel }}
    </div>
    @endif

    <div class="summary-row">
        <table style="margin-bottom: 0px;">
            <tr>
                <td style="border: none; padding: 0;">
                    <div class="summary-card" style="width: 220px;">
                        <div class="label">Total Fee Structures Setup</div>
                        <div class="value">{{ count($feeStructures) }}</div>
                    </div>
                </td>
                <td style="border: none; padding: 0; padding-left: 20px;">
                    <div class="summary-card" style="width: 220px;">
                        <div class="label">Sum of Configured Fees</div>
                        <div class="value green">GHS {{ number_format($feeStructures->sum('amount'), 2) }}</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section-title">Fee Setup Structure Matrix</div>
    <table>
        <thead>
            <tr>
                <th>Term</th>
                <th>Academic Year</th>
                <th>Grade Level</th>
                <th>Fee Type</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($feeStructures as $fs)
            <tr>
                <td>{{ $fs->term->name }}</td>
                <td>{{ $fs->term->academicYear->name }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $fs->level)) }}</td>
                <td>{{ ucwords(str_replace('_', ' ', $fs->fee_type)) }}</td>
                <td class="text-right font-bold">GHS {{ number_format($fs->amount, 2) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="4">TOTAL EXPECTED SETUP AMOUNT</td>
                <td class="text-right">GHS {{ number_format($feeStructures->sum('amount'), 2) }}</td>
            </tr>
        </tbody>
    </table>
@endsection
