<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SchoolClassController extends Controller
{
    public function index()
    {
        return Inertia::render('Classes/Index', [
            'classes' => SchoolClass::all(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required'],
            'level' => ['required'],
        ]);

        SchoolClass::create($data);

        return redirect()->back();
    }

    public function update(Request $request, SchoolClass $class)
    {
        $data = $request->validate([
            'name' => ['required'],
            'level' => ['required'],
        ]);

        $class->update($data);

        return redirect()->back();
    }

    public function destroy(SchoolClass $class)
    {
        $class->delete();

        return redirect()->back();
    }
}
