<?php

namespace App\Enums;

enum TeamPermission: string
{
    case UpdateTeam = 'team:update';
    case DeleteTeam = 'team:delete';

    case AddMember = 'member:add';
    case UpdateMember = 'member:update';
    case RemoveMember = 'member:remove';

    case CreateInvitation = 'invitation:create';
    case CancelInvitation = 'invitation:cancel';

    // School specific permissions
    case ManageStudents = 'students:manage';
    case ViewStudents = 'students:view';

    case ManageCourses = 'courses:manage';
    case ViewCourses = 'courses:view';

    case ManageExams = 'exams:manage';
    case ViewExams = 'exams:view';

    case ManageGrades = 'grades:manage';
    case ViewGrades = 'grades:view';

    case ManageAttendance = 'attendance:manage';
    case ViewAttendance = 'attendance:view';

    case ManagePayments = 'payments:manage';
    case ViewPayments = 'payments:view';

    case ManageExpenses = 'expenses:manage';
    case ViewExpenses = 'expenses:view';

    case SendSms = 'sms:send';
    case ViewSms = 'sms:view';

    case GenerateReports = 'reports:generate';
    case ViewReports = 'reports:view';
}
