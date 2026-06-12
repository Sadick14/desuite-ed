<?php

namespace App\Http\Controllers;

use App\Models\FeeStructure;
use App\Models\Payment;
use App\Models\School;
use App\Models\Student;
use App\Models\Term;
use App\Services\BalanceService;
use App\Services\FeedingFeeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PaymentController extends Controller
{
    // In PaymentController@index
    public function index()
    {
        $activeTerm = Term::where('is_active', true)->with('academicYear')->first();
        $students = Student::with('class')->get();

        // Build feeding fee data for all students
        $feedingFeeData = [];
        if ($activeTerm) {
            $students = BalanceService::attachBalances($students, $activeTerm);

            foreach ($students as $student) {
                $feedingFeeData[$student->id] = FeedingFeeService::getStudentFeedingBalance($student, $activeTerm);
            }
        }

        return Inertia::render('Payments/Index', [
            'payments' => Payment::with('student', 'term', 'user')->latest()->paginate(20),
            'students' => $students,
            'terms' => Term::all(),
            'feeStructures' => FeeStructure::with('term')->get(),
            'activeTerm' => $activeTerm,
            'school' => School::first(),
            'feedingFeeData' => $feedingFeeData,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'term_id' => ['required', 'exists:terms,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_type' => ['required', 'in:school_fees,feeding_fees,registration_fees,others'],
            'payment_method' => ['required', 'in:cash,momo,bank'],
            'payment_date' => ['required', 'date'],
        ]);

        $student = Student::findOrFail($data['student_id']);
        $term = Term::findOrFail($data['term_id']);

        // Handle feeding fees differently (attendance-based)
        if ($data['payment_type'] === 'feeding_fees') {
            $payment = DB::transaction(function () use ($data) {
                $data['receipt_number'] = Payment::generateReceiptNumber();
                $data['user_id'] = Auth::id();

                $payment = Payment::create($data);

                // Allocate feeding fee payment to weekly balances
                FeedingFeeService::recordFeedingFeePayment(
                    Student::find($data['student_id']),
                    Term::find($data['term_id']),
                    $data['amount']
                );

                return $payment;
            });
        } else {
            // Regular fees
            if (! BalanceService::canPay($student, $term, $data['payment_type'], $data['amount'])) {
                return back()->withErrors(['amount' => "Payment exceeds remaining balance for {$data['payment_type']}"]);
            }

            $payment = DB::transaction(function () use ($data) {
                $data['receipt_number'] = Payment::generateReceiptNumber();
                $data['user_id'] = Auth::id();

                return Payment::create($data);
            });
        }

        return back()->with('payment', $payment->load('term'));
    }

    public function storeBulk(Request $request)
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'term_id' => ['required', 'exists:terms,id'],
            'payment_method' => ['required', 'in:cash,momo,bank'],
            'payment_date' => ['required', 'date'],
            'payments' => ['required', 'array', 'min:1'],
            'payments.*.payment_type' => ['required', 'in:school_fees,feeding_fees,registration_fees,others'],
            'payments.*.amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $student = Student::findOrFail($data['student_id']);
        $term = Term::findOrFail($data['term_id']);

        $lastPayment = DB::transaction(function () use ($data, $student, $term) {
            $payments = [];

            foreach ($data['payments'] as $paymentData) {
                $amount = (float) $paymentData['amount'];
                if ($amount <= 0) {
                    continue;
                }

                $payment = Payment::create([
                    'student_id' => $data['student_id'],
                    'term_id' => $data['term_id'],
                    'amount' => $amount,
                    'payment_type' => $paymentData['payment_type'],
                    'payment_method' => $data['payment_method'],
                    'payment_date' => $data['payment_date'],
                    'receipt_number' => Payment::generateReceiptNumber(),
                    'user_id' => Auth::id(),
                ]);

                if ($paymentData['payment_type'] === 'feeding_fees') {
                    FeedingFeeService::recordFeedingFeePayment($student, $term, $amount);
                }

                $payments[] = $payment;
            }

            return count($payments) > 0 ? end($payments) : null;
        });

        if (!$lastPayment) {
            return back()->withErrors(['amount' => 'No valid payments were processed']);
        }

        return back()->with('payment', $lastPayment->load('term'));
    }

    public function update(Request $request, Payment $payment)
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'term_id' => ['required', 'exists:terms,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_type' => ['required', 'in:school_fees,feeding_fees,registration_fees,others'],
            'payment_method' => ['required', 'in:cash,momo,bank'],
            'payment_date' => ['required', 'date'],
        ]);

        $student = Student::findOrFail($data['student_id']);
        $term = Term::findOrFail($data['term_id']);

        $oldAmount = $payment->amount;
        $amountDifference = $data['amount'] - $oldAmount;

        if ($amountDifference > 0 && ! BalanceService::canPay($student, $term, $data['payment_type'], $amountDifference)) {
            return back()->withErrors(['amount' => 'Adjusted payment would exceed remaining balance']);
        }

        $payment->update($data);

        return redirect()->back()->with('success', 'Payment updated successfully');
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();

        return redirect()->back();
    }
}
