<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Services\ResultPublishingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ResultPublishingController extends Controller
{
    public function __construct(
        protected ResultPublishingService $publishingService
    ) {}

    public function index(Request $request): View
    {
        $exams = Exam::with('academicYear')->orderBy('id', 'desc')->get();
        $selectedExamId = $request->filled('exam_id') ? $request->query('exam_id') : $exams->first()?->id;
        $selectedExam = $exams->firstWhere('id', $selectedExamId) ?? $exams->first();

        $readiness = null;
        if ($selectedExam) {
            $readiness = $this->publishingService->getPublishingReadiness($selectedExam);
        }

        return view('results.admin.publishing.index', compact('exams', 'selectedExam', 'selectedExamId', 'readiness'));
    }

    public function publish(Request $request, Exam $exam): RedirectResponse
    {
        $this->publishingService->publish($exam, Auth::id());
        return back()->with('success', "Results for examination '{$exam->exam_name}' published successfully.");
    }

    public function unpublish(Request $request, Exam $exam): RedirectResponse
    {
        $this->publishingService->unpublish($exam, Auth::id());
        return back()->with('success', "Results for examination '{$exam->exam_name}' unpublished successfully.");
    }
}
