<?php

namespace App\Http\Controllers;

use App\Models\FeeStructure;
use App\Models\Payment;
use App\Models\School;
use App\Models\Student;
use App\Models\Term;
use App\Services\BalanceService;
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

        if ($activeTerm) {
            $students = BalanceService::attachBalances($students, $activeTerm);
        }

        return Inertia::render('Payments/Index', [
            'payments' => Payment::with('student', 'term', 'user')->latest()->paginate(20),
            'students' => $students,
            'terms' => Term::all(),
            'feeStructures' => FeeStructure::with('term')->get(),
            'activeTerm' => $activeTerm,
            'school' => School::first(),
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

        if (! BalanceService::canPay($student, $term, $data['payment_type'], $data['amount'])) {
            return back()->withErrors(['amount' => "Payment exceeds remaining balance for {$data['payment_type']}"]);
        }

        $payment = DB::transaction(function () use ($data) {
            $data['receipt_number'] = Payment::generateReceiptNumber();
            $data['user_id'] = Auth::id();

            return Payment::create($data);
        });

        return back()->with('payment', $payment->load('term'));
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
