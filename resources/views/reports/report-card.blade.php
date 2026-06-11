<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Card - {{ $student->first_name }} {{ $student->last_name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            padding: 30px;
            background: #fff;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #0f172a;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .school-name {
            font-size: 28px;
            font-weight: bold;
            color: #0f172a;
        }
        .report-title {
            font-size: 22px;
            font-weight: bold;
            color: #1f2937;
            margin-top: 10px;
        }
        .student-info {
            margin: 25px 0;
            padding: 20px;
            background: #f8fafc;
            border-radius: 8px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        .info-item {
            display: flex;
        }
        .info-label {
            font-weight: bold;
            color: #4b5563;
            width: 120px;
        }
        .info-value {
            color: #111827;
        }
        .grades-table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
        }
        .grades-table th, .grades-table td {
            border: 1px solid #e5e7eb;
            padding: 12px;
            text-align: left;
        }
        .grades-table th {
            background: #f1f5f9;
            font-weight: bold;
            color: #0f172a;
        }
        .pass {
            color: #059669;
            font-weight: bold;
        }
        .fail {
            color: #dc2626;
            font-weight: bold;
        }
        .summary {
            margin-top: 30px;
            padding: 20px;
            background: #fef3c7;
            border-radius: 8px;
            border-left: 4px solid #f59e0b;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }
        .summary-item {
            text-align: center;
        }
        .summary-label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            font-weight: bold;
        }
        .summary-value {
            font-size: 20px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 5px;
        }
        .footer {
            margin-top: 50px;
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: #6b7280;
        }
        .grade-aplus {
            background: #bbf7d0;
            color: #166534;
            padding: 2px 8px;
            border-radius: 4px;
        }
        .grade-a {
            background: #dcfce7;
            color: #166534;
            padding: 2px 8px;
            border-radius: 4px;
        }
        .grade-b {
            background: #f0fdf4;
            color: #166534;
            padding: 2px 8px;
            border-radius: 4px;
        }
        .grade-c {
            background: #fef9c3;
            color: #854d0e;
            padding: 2px 8px;
            border-radius: 4px;
        }
        .grade-d {
            background: #ffedd5;
            color: #9a3412;
            padding: 2px 8px;
            border-radius: 4px;
        }
        .grade-f {
            background: #fee2e2;
            color: #991b1b;
            padding: 2px 8px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="school-name">School Management System</div>
        <div class="report-title">REPORT CARD</div>
        @if($term || $academicYear)
        <div style="margin-top: 8px; color: #6b7280;">
            {{ $term->name ?? '' }} {{ $academicYear ? '- ' . $academicYear->name : '' }}
        </div>
        @endif
    </div>
    
    <div class="student-info">
        <h3 style="margin-bottom: 15px; color: #0f172a;">Student Information</h3>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Name:</span>
                <span class="info-value">{{ $student->first_name }} {{ $student->last_name }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Student ID:</span>
                <span class="info-value">{{ $student->student_id }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Class:</span>
                <span class="info-value">{{ $student->schoolClass->name ?? '-' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Parent Name:</span>
                <span class="info-value">{{ $student->parent_name }}</span>
            </div>
        </div>
    </div>
    
    <h3 style="color: #0f172a; margin-bottom: 10px;">Grades</h3>
    @if($grades->count() > 0)
    <table class="grades-table">
        <thead>
            <tr>
                <th>Course</th>
                <th>Exam</th>
                <th>Score</th>
                <th>Max Score</th>
                <th>Percentage</th>
                <th>Grade</th>
                <th>Status</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($grades as $grade)
            @php
                $percentage = 0;
                if ($grade->exam->max_score > 0) {
                    $percentage = round(($grade->score / $grade->exam->max_score) * 100, 2);
                }
                $letterGrade = 'F';
                if ($percentage >= 90) $letterGrade = 'A+';
                else if ($percentage >= 80) $letterGrade = 'A';
                else if ($percentage >= 70) $letterGrade = 'B';
                else if ($percentage >= 60) $letterGrade = 'C';
                else if ($percentage >= 50) $letterGrade = 'D';
                
                $gradeClass = 'grade-f';
                if ($percentage >= 90) $gradeClass = 'grade-aplus';
                else if ($percentage >= 80) $gradeClass = 'grade-a';
                else if ($percentage >= 70) $gradeClass = 'grade-b';
                else if ($percentage >= 60) $gradeClass = 'grade-c';
                else if ($percentage >= 50) $gradeClass = 'grade-d';
                
                $isPassing = $grade->score >= $grade->exam->pass_score;
            @endphp
            <tr>
                <td>{{ $grade->exam->course->name }}</td>
                <td>{{ $grade->exam->name }}</td>
                <td style="font-weight: bold;">{{ $grade->score }}</td>
                <td>{{ $grade->exam->max_score }}</td>
                <td style="font-weight: bold;">{{ $percentage }}%</td>
                <td><span class="{{ $gradeClass }}">{{ $letterGrade }}</span></td>
                <td>
                    @if($isPassing)
                        <span class="pass">PASS</span>
                    @else
                        <span class="fail">FAIL</span>
                    @endif
                </td>
                <td>{{ $grade->remarks ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p style="color: #6b7280;">No grades available for this period.</p>
    @endif
    
    @if($grades->count() > 0)
    <div class="summary">
        <h3 style="margin-bottom: 15px; color: #92400e;">Summary</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Total Subjects</div>
                <div class="summary-value">{{ $summary['totalSubjects'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Average Score</div>
                <div class="summary-value">{{ $summary['averageScore'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Percentage</div>
                <div class="summary-value">{{ $summary['percentage'] }}%</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Passed / Failed</div>
                <div class="summary-value">{{ $summary['passedSubjects'] }} / {{ $summary['failedSubjects'] }}</div>
            </div>
        </div>
    </div>
    @endif
    
    <div class="footer">
        <div>Generated on: {{ now()->format('F j, Y') }}</div>
        <div>Report Card System</div>
    </div>
</body>
</html>
