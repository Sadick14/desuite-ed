# Student Attendance System - Complete Implementation

## Overview
A comprehensive attendance tracking system for managing student presence, absences, tardiness, and excused absences with feeding fee integration.

## Features Implemented

### 1. Attendance Marking
- **Route:** `/attendance`
- **Features:**
  - Select class and attendance date
  - Click to toggle between statuses: Present → Absent → Late → Excused
  - Bulk save attendance for entire class at once
  - Color-coded status indicators
  - Academic year and term scoped

### 2. Attendance History
- **Route:** `/attendance/history`
- **Features:**
  - View all past attendance records
  - Filter by:
    - Class
    - Student
    - Date range (start & end date)
    - Attendance status
  - Delete attendance records
  - Paginated results (50 per page)
  - Sortable by date

### 3. Attendance Report
- **Route:** `/attendance/report`
- **Features:**
  - Generate detailed report by class and term
  - Shows for each student:
    - Total present days
    - Total absent days
    - Total late arrivals
    - Total excused absences
    - Total school days
    - Attendance percentage
  - Sorted by attendance percentage (highest first)
  - Class average attendance calculation
  - Summary statistics

### 4. Attendance Analytics
- **Route:** `/attendance/analytics`
- **Features:**
  - Overall statistics:
    - Average attendance percentage
    - Total present/absent/late/excused records
    - Percentage breakdown
  - Daily trends: attendance by date
  - Class comparison: attendance by class (when no specific class is selected)
  - Filterable by class and term

## Database Schema

### Attendance Model
```php
- id: integer (primary key)
- student_id: foreign key to students
- school_class_id: foreign key to school_classes
- academic_year_id: foreign key to academic_years
- term_id: foreign key to terms
- attendance_date: date
- status: enum (present, absent, excused, late)
- notes: text (nullable)
- sms_sent: boolean
- timestamps: created_at, updated_at
```

**Status Values:**
- `present` - Student was present
- `absent` - Student was absent
- `excused` - Absence was excused/justified
- `late` - Student arrived late

## API Endpoints

### Attendance Management
- `GET /attendance` - Attendance marking page
- `POST /attendance/store` - Record single attendance
- `POST /attendance/bulk` - Bulk record attendance for class
- `DELETE /attendance/{attendance}` - Delete attendance record

### Reports & Analytics
- `GET /attendance/history` - View past records with filters
- `GET /attendance/report` - Class attendance report
- `GET /attendance/analytics` - Overall analytics and trends

## Integration with Feeding Fees

The attendance system integrates with the `FeedingFeeService`:
- Daily feeding fee calculations are based on attendance
- Students only pay for days they are marked present
- Attendance records in `StudentFeedingBalance` track weekly attendance

### Key Relationship
```
Attendance → FeedingFeeService::calculateWeeklyFee()
           → StudentFeedingBalance (weekly tracking)
```

## Frontend Components

### Attendance/Index.vue
- Attendance marking interface
- Click-to-toggle status buttons
- Class and date selection
- Bulk save functionality

### Attendance/History.vue
- Filterable attendance history
- Multiple filter options
- Delete functionality
- Paginated results

### Attendance/Report.vue
- Class-based report generation
- Student-level statistics
- Color-coded percentage indicators
- Term selection

### Attendance/Analytics.vue
- Overall statistics dashboard
- Daily trend visualization
- Class comparison
- Attendance percentage breakdown

## Usage Examples

### Mark Attendance for a Class
1. Go to `/attendance`
2. Select a class
3. Select attendance date
4. Click buttons to toggle student status
5. Click "Save Attendance"

### View Attendance History
1. Go to `/attendance/history`
2. Apply filters (optional):
   - Class
   - Date range
   - Status
3. View results with option to delete records

### Generate Attendance Report
1. Go to `/attendance/report`
2. Select class and term
3. Click "Generate Report"
4. View detailed statistics by student

### View Analytics
1. Go to `/attendance/analytics`
2. Select term (and optional class)
3. Click "Generate Analytics"
4. View overall stats and daily/class trends

## Academic Year Scoping

All attendance records are scoped by academic year:
- Attendance data is isolated by academic year
- Year-end closure won't carry over previous year attendance
- Clean separation of data across academic years

## Permissions

The attendance system respects the existing permission system:
- Administrators and staff can manage attendance
- Teachers can mark attendance for their classes
- View-only roles cannot modify records

## Future Enhancements

Possible additions:
- SMS notifications for excessive absences
- Automated reports to parents
- Attendance targets/goals
- Integration with academic performance
- Absence tracking with reasons
- Make-up attendance makeup procedures
- Attendance policies configuration
