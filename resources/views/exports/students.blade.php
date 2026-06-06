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
                    <div style="font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; color: #78716c; margin-bottom: 4px;">Total Students</div>
                    <div style="font-size: 18px; font-weight: 800; color: #292524;">{{ $students->count() }}</div>
                </td>
                <td style="width: 33%; text-align: center; border: 1px solid #e7e5e4; padding: 12px;">
                    <div style="font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; color: #78716c; margin-bottom: 4px;">Active</div>
                    <div style="font-size: 18px; font-weight: 800; color: #16a34a;">{{ $students->where('active', true)->count() }}</div>
                </td>
                <td style="width: 33%; text-align: center; border: 1px solid #e7e5e4; padding: 12px;">
                    <div style="font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; color: #78716c; margin-bottom: 4px;">Inactive</div>
                    <div style="font-size: 18px; font-weight: 800; color: #dc2626;">{{ $students->where('active', false)->count() }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Student ID</th>
                <th>Full Name</th>
                <th>Class</th>
                <th>Gender</th>
                <th>Parent / Guardian</th>
                <th>Phone</th>
                <th>Admission Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $student)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td style="font-family: monospace;">{{ $student->student_id }}</td>
                <td class="font-bold">{{ $student->first_name }} {{ $student->last_name }}</td>
                <td>{{ $student->class?->name ?? '—' }}</td>
                <td>{{ ucfirst($student->gender ?? '—') }}</td>
                <td>{{ $student->parent_name ?? '—' }}</td>
                <td>{{ $student->parent_phone ?? '—' }}</td>
                <td>{{ $student->admission_date ? \Carbon\Carbon::parse($student->admission_date)->format('d M Y') : '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endsection
