<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\AssessmentSettingController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\FeeStructureController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\GradeScaleRuleController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TermController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');
        
    /*
    |--------------------------------------------------------------------------
    | USER MANAGEMENT
    |--------------------------------------------------------------------------
    */
    Route::resource('users', UserController::class);

    /*
    |--------------------------------------------------------------------------
    | SCHOOL (SYSTEM SETTINGS)
    |--------------------------------------------------------------------------
    */
    Route::resource('school', SchoolController::class)
        ->only(['index', 'store', 'update']);

    /*
    |--------------------------------------------------------------------------
    | GRADING & ASSESSMENT SETTINGS
    |--------------------------------------------------------------------------
    */
    Route::resource('grade-scale-rules', GradeScaleRuleController::class);
    Route::resource('assessment-settings', AssessmentSettingController::class);
    Route::resource('exam-templates', ExamTemplateController::class)->only(['index', 'store', 'update', 'destroy']);

    /*
    |--------------------------------------------------------------------------
    | ACADEMIC STRUCTURE
    |--------------------------------------------------------------------------
    */
    Route::resource('academic-years', AcademicYearController::class);
    Route::resource('terms', TermController::class);

    /*
    |--------------------------------------------------------------------------
    | ACADEMIC CORE
    |--------------------------------------------------------------------------
    */
    Route::resource('classes', SchoolClassController::class);
    Route::resource('students', StudentController::class);

    /*
    |--------------------------------------------------------------------------
    | STUDENT PROMOTIONS & ENROLLMENTS
    |--------------------------------------------------------------------------
    */
    Route::get('/promotions', [EnrollmentController::class, 'index'])->name('promotions.index');
    Route::get('/api/enrollments/{academicYearId}/{classId}', [EnrollmentController::class, 'getStudents'])->name('enrollments.getStudents');
    Route::get('/api/class/{classId}/students', [EnrollmentController::class, 'getClassStudents'])->name('enrollments.getClassStudents');
    Route::get('/api/student/{studentId}/enrollment-history', [EnrollmentController::class, 'getEnrollmentHistory'])->name('enrollments.history');

    Route::post('/students/{student}/promote', [PromotionController::class, 'promote'])->name('students.promote');
    Route::post('/classes/{class}/promote', [PromotionController::class, 'promoteClass'])->name('classes.promote');
    Route::post('/students/{student}/retain', [PromotionController::class, 'retainStudent'])->name('students.retain');
    Route::post('/promotions/bulk', [PromotionController::class, 'processBulk'])->name('promotions.bulk');

    /*
    |--------------------------------------------------------------------------
    | FEES & PAYMENTS
    |--------------------------------------------------------------------------
    */
    Route::resource('fee-structures', FeeStructureController::class);
    Route::resource('payments', PaymentController::class)->except(['show', 'create', 'edit']);
    Route::post('payments/bulk', [PaymentController::class, 'storeBulk'])->name('payments.storeBulk');

    /*
    |--------------------------------------------------------------------------
    | REPORTS & ANALYTICS
    |--------------------------------------------------------------------------
    */
    Route::get('/analytics', [ReportController::class, 'analytics'])->name('analytics.index');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::post('/reports/generate', [ReportController::class, 'generate'])->name('reports.generate');

    /*
    |--------------------------------------------------------------------------
    | EXPENSE MANAGEMENT
    |--------------------------------------------------------------------------
    */
    Route::resource('expense-categories', ExpenseCategoryController::class);
    Route::resource('expenses', ExpenseController::class);

    /*
    |--------------------------------------------------------------------------
    | AUDIT SYSTEM
    |--------------------------------------------------------------------------
    */
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('/audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit-logs.show');

    /*
    |--------------------------------------------------------------------------
    | ATTENDANCE TRACKING
    |--------------------------------------------------------------------------
    */
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/', [AttendanceController::class, 'index'])->name('index');
        Route::post('/store', [AttendanceController::class, 'store'])->name('store');
        Route::get('/students/{student}', [AttendanceController::class, 'show'])->name('student');
    });

    /*
    |--------------------------------------------------------------------------
    | ACADEMICS: COURSES, EXAMS, GRADES
    |--------------------------------------------------------------------------
    */
    Route::resource('courses', CourseController::class);
    Route::resource('exams', ExamController::class);
    Route::prefix('grades')->name('grades.')->group(function () {
        Route::get('/', [GradeController::class, 'index'])->name('index');
        Route::post('/store', [GradeController::class, 'store'])->name('store');
    });

    /*
    |--------------------------------------------------------------------------
    | REPORTS & REPORT CARDS
    |--------------------------------------------------------------------------
    */
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/students/{student}', [ReportController::class, 'show'])->name('show');
        Route::get('/students/{student}/download', [ReportController::class, 'download'])->name('download');
    });

    /*
    |--------------------------------------------------------------------------
    | SMS COMMUNICATION
    |--------------------------------------------------------------------------
    */
    Route::prefix('sms')->name('sms.')->group(function () {
        Route::get('/', [SmsController::class, 'index'])->name('index');
        Route::get('/compose', [SmsController::class, 'compose'])->name('compose');
        Route::get('/templates', [SmsController::class, 'templates'])->name('templates');
        Route::post('/send', [SmsController::class, 'send'])->name('send');
        Route::post('/students/{student}/send', [SmsController::class, 'sendToStudent'])->name('send.student');
        Route::post('/payment-confirmation', [SmsController::class, 'sendPaymentConfirmation'])->name('payment-confirmation');
        Route::post('/balance-reminders', [SmsController::class, 'sendBalanceReminders'])->name('balance-reminders');
        Route::post('/{smsLog}/resend', [SmsController::class, 'resend'])->name('resend');
        Route::delete('/{smsLog}', [SmsController::class, 'destroy'])->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | DATA EXPORTS
    |--------------------------------------------------------------------------
    |
    */
    Route::get('/exports/students', [ExportController::class, 'exportStudents'])->name('exports.students');
    Route::get('/exports/payments', [ExportController::class, 'exportPayments'])->name('exports.payments');
    Route::get('/exports/expenses', [ExportController::class, 'exportExpenses'])->name('exports.expenses');
    Route::get('/exports/analytics', [ExportController::class, 'exportAnalytics'])->name('exports.analytics');
    Route::get('/exports/fee-collection', [ExportController::class, 'exportFeeCollection'])->name('exports.fee-collection');
    Route::get('/exports/students/{student}/statement', [ExportController::class, 'exportStudentStatement'])->name('exports.students.statement');

});

require __DIR__.'/settings.php';
