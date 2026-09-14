<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAcademicYearRequest;
use App\Models\AcademicYear;
use App\Services\AcademicYearService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AcademicYearController extends Controller
{
    public function __construct(
        protected AcademicYearService $academicYearService
    ) {}

    public function index(): View
    {
        $years = AcademicYear::orderBy('id', 'desc')->paginate(15);
        return view('results.admin.academic_years.index', compact('years'));
    }

    public function create(): View
    {
        return view('results.admin.academic_years.create');
    }

    public function store(StoreAcademicYearRequest $request): RedirectResponse
    {
        $this->academicYearService->create($request->validated());
        return redirect()->route('admin.academic-years.index')->with('success', 'Academic Year created successfully.');
    }

    public function edit(AcademicYear $academicYear): View
    {
        return view('results.admin.academic_years.edit', compact('academicYear'));
    }

    public function update(StoreAcademicYearRequest $request, AcademicYear $academicYear): RedirectResponse
    {
        $this->academicYearService->update($academicYear, $request->validated());
        return redirect()->route('admin.academic-years.index')->with('success', 'Academic Year updated successfully.');
    }

    public function toggleActive(AcademicYear $academicYear): RedirectResponse
    {
        $this->academicYearService->setActiveYear($academicYear);
        return back()->with('success', "Academic Year '{$academicYear->name}' is now active.");
    }
}
