<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExamRequest;
use App\Http\Requests\UpdateExamRequest;
use App\Models\AcademicYear;
use App\Models\Exam;
use App\Services\ExamService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ExamController extends Controller
{
    public function __construct(
        protected ExamService $examService
    ) {}

    public function index(Request $request): View
    {
        $academicYearId = $request->query('academic_year_id');
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $statuses = Exam::STATUSES;

        $exams = Exam::with(['academicYear', 'creator'])
            ->when($academicYearId, fn ($q) => $q->where('academic_year_id', $academicYearId))
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('results.admin.exams.index', compact('exams', 'academicYears', 'academicYearId', 'statuses'));
    }

    public function create(): View
    {
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $statuses = Exam::STATUSES;

        return view('results.admin.exams.create', compact('academicYears', 'statuses'));
    }

    public function store(StoreExamRequest $request): RedirectResponse
    {
        $this->examService->create($request->validated(), Auth::id());
        return redirect()->route('admin.exams.index')->with('success', 'Exam created successfully.');
    }

    public function edit(Exam $exam): View
    {
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $statuses = Exam::STATUSES;

        return view('results.admin.exams.edit', compact('exam', 'academicYears', 'statuses'));
    }

    public function update(UpdateExamRequest $request, Exam $exam): RedirectResponse
    {
        $this->examService->update($exam, $request->validated(), Auth::id());
        return redirect()->route('admin.exams.index')->with('success', 'Exam updated successfully.');
    }

    public function updateStatus(Request $request, Exam $exam): RedirectResponse
    {
        $request->validate([
            'status' => 'required|string|in:Draft,Active,Mark Entry Open,Mark Entry Closed,Verification,Locked,Published,Archived',
        ]);

        $this->examService->updateStatus($exam, $request->status, Auth::id());
        return back()->with('success', "Exam status updated to '{$request->status}'.");
    }

    public function destroy(Exam $exam): RedirectResponse
    {
        $exam->delete();
        return redirect()->route('admin.exams.index')->with('success', 'Exam deleted successfully.');
    }
}
