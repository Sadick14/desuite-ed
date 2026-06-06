<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            line-height: 1.5;
        }
        .pdf-header {
            border-bottom: 3px solid #292524;
            padding-bottom: 16px;
            margin-bottom: 24px;
            display: table;
            width: 100%;
        }
        .pdf-header-logo {
            display: table-cell;
            vertical-align: middle;
            width: 70px;
        }
        .pdf-header-logo img {
            width: 60px;
            height: 60px;
            border-radius: 8px;
        }
        .pdf-header-info {
            display: table-cell;
            vertical-align: middle;
            padding-left: 16px;
        }
        .pdf-header-info h1 {
            font-size: 20px;
            font-weight: 800;
            color: #292524;
            margin-bottom: 2px;
            letter-spacing: -0.5px;
        }
        .pdf-header-info p {
            font-size: 10px;
            color: #78716c;
            margin: 0;
        }
        .pdf-header-meta {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
        }
        .pdf-header-meta .report-title {
            font-size: 14px;
            font-weight: 700;
            color: #292524;
            margin-bottom: 4px;
        }
        .pdf-header-meta .report-date {
            font-size: 9px;
            color: #a8a29e;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th {
            background-color: #292524;
            color: #ffffff;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 8px 10px;
            text-align: left;
        }
        table th.text-right { text-align: right; }
        table td {
            padding: 7px 10px;
            border-bottom: 1px solid #e7e5e4;
            font-size: 10px;
            color: #1c1917;
        }
        table td.text-right { text-align: right; }
        table td.font-bold { font-weight: 700; }
        table tr:nth-child(even) { background-color: #fafaf9; }
        table tr.total-row {
            background-color: #f5f5f4;
            font-weight: 700;
        }
        table tr.total-row td {
            border-top: 2px solid #292524;
            border-bottom: 2px solid #292524;
            padding-top: 10px;
            padding-bottom: 10px;
        }

        /* Section titles */
        .section-title {
            font-size: 13px;
            font-weight: 700;
            color: #292524;
            margin: 28px 0 10px;
            padding-bottom: 6px;
            border-bottom: 1px solid #d6d3d1;
        }

        /* Summary cards */
        .summary-row {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .summary-card {
            display: table-cell;
            width: 25%;
            padding: 12px;
            text-align: center;
            border: 1px solid #e7e5e4;
            border-radius: 8px;
        }
        .summary-card .label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #78716c;
            margin-bottom: 4px;
        }
        .summary-card .value {
            font-size: 16px;
            font-weight: 800;
            color: #292524;
        }
        .summary-card .value.green { color: #16a34a; }
        .summary-card .value.red { color: #dc2626; }

        /* Badge */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-cash { background: #dcfce7; color: #166534; }
        .badge-momo { background: #ffedd5; color: #9a3412; }
        .badge-bank { background: #dbeafe; color: #1e40af; }
        .badge-other { background: #f3f4f6; color: #374151; }

        /* Footer */
        .pdf-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #a8a29e;
            padding: 10px 0;
            border-top: 1px solid #e7e5e4;
        }

        /* Filter info */
        .filter-info {
            background: #fafaf9;
            border: 1px solid #e7e5e4;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 20px;
            font-size: 10px;
            color: #57534e;
        }
        .filter-info strong { color: #292524; }

        @page {
            margin: 20mm 15mm 25mm 15mm;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="pdf-header">
        @if($school && $school->logo)
        <div class="pdf-header-logo">
            <img src="{{ public_path('storage/' . $school->logo) }}" alt="Logo">
        </div>
        @endif
        <div class="pdf-header-info">
            <h1>{{ $school->name ?? 'School Management System' }}</h1>
            @if($school && $school->address)
                <p>{{ $school->address }}</p>
            @endif
            @if($school && ($school->phone || $school->email))
                <p>{{ $school->phone }}{{ $school->phone && $school->email ? ' • ' : '' }}{{ $school->email }}</p>
            @endif
        </div>
        <div class="pdf-header-meta">
            <div class="report-title">{{ $reportTitle ?? 'Report' }}</div>
            <div class="report-date">Generated {{ now()->format('d M Y, h:i A') }}</div>
            @if(isset($filterLabel) && $filterLabel)
                <div class="report-date" style="margin-top: 2px;">{{ $filterLabel }}</div>
            @endif
        </div>
    </div>

    {{-- Content injected by each template --}}
    @yield('content')

    {{-- Footer --}}
    <div class="pdf-footer">
        {{ $school->name ?? 'School Management System' }} &bull; Confidential &bull; Generated {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
