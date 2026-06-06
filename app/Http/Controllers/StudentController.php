<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Term;
use App\Services\BalanceService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Students/Index', [
            'students' => Student::with('class')
                ->when($request->search, function ($q, $search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('student_id', 'like', "%{$search}%")
                        ->orWhere('parent_name', 'like', "%{$search}%")
                        ->orWhere('parent_phone', 'like', "%{$search}%");
                })
                ->latest()
                ->paginate(10),

            'classes' => SchoolClass::all(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'first_name' => ['required'],
            'last_name' => ['required'],
            'gender' => ['required', 'in:male,female'],
            'date_of_birth' => ['required', 'date'],
            'parent_name' => ['required'],
            'parent_phone' => ['required'],
            'address' => ['nullable'],
            'admission_date' => ['required', 'date'],
        ]);

        $data['student_id'] = Student::generateStudentId();

        Student::create($data);

        return redirect()->back();
    }

    public function update(Request $request, Student $student)
    {
        $data = $request->validate([
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'first_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'gender' => ['required', 'in:male,female'],
            'date_of_birth' => ['required', 'date'],
            'parent_name' => ['required', 'string'],
            'parent_phone' => ['required', 'string'],
            'address' => ['nullable', 'string'],
            'admission_date' => ['required', 'date'],
        ]);

        $student->update($data);

        return redirect()->back();
    }

    public function show(Student $student)
    {
        $student->load([
            'class',
            'payments.term',
        ]);

        $currentTerm = Term::where('is_active', true)->first();

        $financial = [
            'expected' => 0,
            'paid' => 0,
            'balance' => 0,
            'breakdown' => [],
        ];

        if ($currentTerm) {
            $financial = BalanceService::forStudent($student, $currentTerm);
        }

        return Inertia::render('Students/Show', [
            'student' => $student,
            'currentTerm' => $currentTerm,
            'classes' => SchoolClass::all(),

            'financial' => $financial,

            'payments' => $student->payments()
                ->with('term')
                ->latest()
                ->get(),
        ]);
    }

    public function destroy(Student $student)
    {
        $student->delete();

        return redirect()->back();
    }
}
