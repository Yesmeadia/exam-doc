<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSchoolClassRequest;
use App\Models\SchoolClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SchoolClassController extends Controller
{
    public function index(): View
    {
        $classes = SchoolClass::with('sections')->orderBy('display_order')->orderBy('name')->get();
        return view('results.admin.classes.index', compact('classes'));
    }

    public function show(SchoolClass $class): RedirectResponse
    {
        return redirect()->route('admin.classes.index');
    }

    public function store(StoreSchoolClassRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if (empty($data['code'])) {
            $data['code'] = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::slug($data['name']));
        }
        SchoolClass::create($data);
        return redirect()->route('admin.classes.index')->with('success', 'Class created successfully.');
    }

    public function update(StoreSchoolClassRequest $request, SchoolClass $class): RedirectResponse
    {
        $class->update($request->validated());
        return redirect()->route('admin.classes.index')->with('success', 'Class updated successfully.');
    }

    public function destroy(SchoolClass $class): RedirectResponse
    {
        $class->delete();
        return redirect()->route('admin.classes.index')->with('success', 'Class deleted successfully.');
    }
}
