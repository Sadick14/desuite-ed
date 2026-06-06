<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SchoolController extends Controller
{
    /**
     * Show school settings / profile
     */
    public function index()
    {
        return Inertia::render('School/Index', [
            'school' => School::first(), // single school system
        ]);
    }

    /**
     * Store school (initial setup only)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable'],
            'address' => ['nullable'],
            'logo' => ['nullable'],
        ]);

        // Single school rule: ensure only one exists
        if (School::exists()) {
            return back()->withErrors([
                'school' => 'School already exists. Use update instead.',
            ]);
        }

        School::create($data);

        return redirect()->back();
    }

    /**
     * Update school settings
     */
    public function update(Request $request, School $school)
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable'],
            'address' => ['nullable'],
            'logo' => ['nullable'],
        ]);

        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($school->logo) {
                Storage::disk('public')->delete($school->logo);
            }
            $path = $request->file('logo')->store('schools', 'public');
            $data['logo'] = $path;
        }

        $school->update($data);

        return redirect()->back();
    }

    /**
     * No deletion in production SaaS (optional)
     */
    public function destroy(School $school)
    {
        abort(403, 'School deletion is disabled for safety.');
    }
}
