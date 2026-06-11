<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\SmsLog;
use App\Models\SmsTemplate;
use App\Models\Student;
use App\Models\Term;
use App\Services\BalanceService;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SmsController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function index()
    {
        $smsLogs = SmsLog::with(['student', 'user'])
            ->latest()
            ->paginate(20);

        return Inertia::render('Sms/Index', [
            'smsLogs' => $smsLogs,
            'stats' => [
                'total' => SmsLog::count(),
                'sent' => SmsLog::sent()->count(),
                'failed' => SmsLog::failed()->count(),
                'pending' => SmsLog::pending()->count(),
            ],
        ]);
    }

    public function templates()
    {
        $templates = SmsTemplate::all();

        return Inertia::render('Sms/Templates', [
            'templates' => $templates,
        ]);
    }

    public function compose()
    {
        $students = Student::with('class')->get();
        $templates = SmsTemplate::active()->get();
        $terms = Term::all();

        return Inertia::render('Sms/Compose', [
            'students' => $students,
            'templates' => $templates,
            'terms' => $terms,
        ]);
    }

    public function send(Request $request)
    {
        $request->validate([
            'recipients' => 'required|array',
            'recipients.*.phone' => 'required|string',
            'message' => 'required|string|min:1|max:1600',
            'type' => 'required|string',
        ]);

        $recipients = $request->recipients;
        $message = $request->message;
        $type = $request->type;

        $results = $this->smsService->sendBulkSms($recipients, $message, $type);

        return back()->with('success', sprintf('%d SMS messages queued for sending!', count($results)));
    }

    public function sendToStudent(Request $request, Student $student)
    {
        $request->validate([
            'message' => 'required|string|min:1|max:1600',
            'type' => 'required|string',
        ]);

        $this->smsService->sendSingleSms([
            'student_id' => $student->id,
            'phone' => $student->parent_phone,
            'name' => $student->parent_name,
            'message' => $request->message,
            'type' => $request->type,
        ]);

        return back()->with('success', 'SMS sent successfully!');
    }

    public function sendPaymentConfirmation(Request $request)
    {
        $request->validate([
            'payment_id' => 'required|exists:payments,id',
        ]);

        $payment = Payment::with('student', 'term')->findOrFail($request->payment_id);

        $this->smsService->sendPaymentConfirmation($payment->student, [
            'amount' => $payment->amount,
            'fee_type' => $this->formatFeeType($payment->payment_type),
            'receipt_number' => $payment->receipt_number,
        ]);

        return back()->with('success', 'Payment confirmation SMS sent!');
    }

    public function sendBalanceReminders(Request $request)
    {
        $request->validate([
            'term_id' => 'required|exists:terms,id',
            'student_ids' => 'array',
        ]);

        $term = Term::findOrFail($request->term_id);
        $students = Student::with('class');

        if ($request->has('student_ids') && ! empty($request->student_ids)) {
            $students = $students->whereIn('id', $request->student_ids);
        }

        $students = $students->get();

        $studentsWithBalances = [];
        foreach ($students as $student) {
            $balanceData = BalanceService::forStudent($student, $term);
            if ($balanceData['balance'] > 0) {
                $studentsWithBalances[] = [
                    'student' => $student,
                    'balance_data' => [
                        'balance' => $balanceData['balance'],
                        'term' => $term->name,
                    ],
                ];
            }
        }

        $results = $this->smsService->sendBulkBalanceReminders($studentsWithBalances);

        return back()->with('success', sprintf('%d balance reminder SMS sent!', count($results)));
    }

    public function resend(Request $request, SmsLog $smsLog)
    {
        $this->smsService->sendSingleSms([
            'student_id' => $smsLog->student_id,
            'phone' => $smsLog->recipient_phone,
            'name' => $smsLog->recipient_name,
            'message' => $smsLog->message,
            'type' => $smsLog->sms_type,
        ]);

        return back()->with('success', 'SMS resent successfully!');
    }

    public function destroy(SmsLog $smsLog)
    {
        $smsLog->delete();

        return back()->with('success', 'SMS log deleted!');
    }

    protected function formatFeeType($type)
    {
        return str_replace('_', ' ', ucwords($type));
    }
}
