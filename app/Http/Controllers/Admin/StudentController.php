<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\StoreStudentSubjectRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Services\StudentSubjectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function __construct(
        protected StudentSubjectService $studentSubjectService
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $academicYearId = $request->query('academic_year_id');
        $classId = $request->query('class_id');
        $sectionId = $request->query('section_id');
        $search = $request->query('search');

        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $classes = SchoolClass::where('status', 'active')->orderBy('display_order')->get();
        $sections = $classId ? Section::where('class_id', $classId)->get() : collect();

        // If a section is selected that does not belong to the selected class, reset it
        if ($classId && $sectionId && !$sections->contains('id', $sectionId)) {
            $sectionId = null;
        }

        $students = Student::with(['academicYear', 'schoolClass', 'section', 'subjects'])
            ->when($academicYearId, fn ($q) => $q->where('academic_year_id', $academicYearId))
            ->when($classId, fn ($q) => $q->where('class_id', $classId))
            ->when($sectionId, fn ($q) => $q->where('section_id', $sectionId))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('student_id', 'like', "%{$search}%")
                        ->orWhere('roll_no', 'like', "%{$search}%");
                });
            })
            ->orderBy('class_id')
            ->orderBy('section_id')
            ->orderBy('roll_no', 'asc')
            ->paginate(25);

        if ($students->currentPage() > $students->lastPage() && $students->lastPage() > 0) {
            $redirectParams = array_merge($request->query(), ['page' => $students->lastPage()]);
            return redirect()->route('admin.students.index', $redirectParams);
        }

        return view('results.admin.students.index', compact(
            'students',
            'academicYears',
            'classes',
            'sections',
            'academicYearId',
            'classId',
            'sectionId',
            'search'
        ));
    }

    public function create(): View
    {
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $classes = SchoolClass::with('sections')->where('status', 'active')->orderBy('display_order')->get();
        return view('results.admin.students.create', compact('academicYears', 'classes'));
    }

    public function store(StoreStudentRequest $request): RedirectResponse
    {
        $student = Student::create($request->validated());

        if ($request->filled('subject_ids')) {
            $this->studentSubjectService->allocateSubjects($student, $request->subject_ids, true, Auth::id());
        }

        return redirect()->route('admin.students.index')->with('success', 'Student created successfully.');
    }

    public function edit(Student $student): View
    {
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $classes = SchoolClass::with('sections')->where('status', 'active')->orderBy('display_order')->get();
        $sections = Section::where('class_id', $student->class_id)->get();

        return view('results.admin.students.edit', compact('student', 'academicYears', 'classes', 'sections'));
    }

    public function update(UpdateStudentRequest $request, Student $student): RedirectResponse
    {
        $student->update($request->validated());
        return redirect()->route('admin.students.index', $this->getReturnParams($request))->with('success', 'Student updated successfully.');
    }

    public function destroy(Request $request, Student $student): RedirectResponse
    {
        $student->delete();
        return redirect()->route('admin.students.index', $this->getReturnParams($request))->with('success', 'Student deleted successfully.');
    }

    /**
     * Subject allocation screen (strictly for Class 11 & 12 individual elective allocation).
     */
    public function subjectAllocation(Request $request, Student $student): View|RedirectResponse
    {
        $student->load(['schoolClass', 'section', 'academicYear', 'subjects']);

        if (!$student->allowsIndividualSubjectAllocation()) {
            return redirect()->route('admin.students.index', $this->getReturnParams($request))->with(
                'warning',
                "Individual subject allocation is only available for 11th and 12th class students. Students in " . ($student->schoolClass?->name ?? 'this class') . " are assigned full subjects by default."
            );
        }

        // Fetch all active subjects available for this academic year
        $allSubjects = Subject::where('status', 'active')
            ->when($student->academic_year_id, fn ($q, $yrId) => $q->where('academic_year_id', $yrId))
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();

        if ($allSubjects->isEmpty()) {
            $allSubjects = Subject::where('status', 'active')->orderBy('display_order')->orderBy('name')->get();
        }

        $allocatedSubjectIds = $student->subjects->pluck('id')->toArray();

        return view('results.admin.students.subjects', compact('student', 'allSubjects', 'allocatedSubjectIds'));
    }

    /**
     * Save subject allocations for an individual student (Class 11/12 only).
     */
    public function saveSubjectAllocation(StoreStudentSubjectRequest $request, Student $student): RedirectResponse
    {
        if (!$student->allowsIndividualSubjectAllocation()) {
            return redirect()->route('admin.students.index', $this->getReturnParams($request))->with(
                'error',
                'Individual subject allocation is only permitted for 11th and 12th class students.'
            );
        }

        $this->studentSubjectService->allocateSubjects(
            $student,
            $request->subject_ids,
            (bool) $request->input('is_special', true),
            Auth::id()
        );

        return redirect()->route('admin.students.index', $this->getReturnParams($request))->with(
            'success',
            "Subject allocation for student {$student->name} ({$student->student_id}) updated successfully."
        );
    }

    /**
     * Extract filter and pagination query parameters to preserve list context on redirects.
     */
    protected function getReturnParams(Request $request): array
    {
        $keys = ['academic_year_id', 'class_id', 'section_id', 'search', 'page'];
        $query = $request->query();
        $source = !empty($query) ? array_intersect_key($query, array_flip($keys)) : $request->only($keys);

        return array_filter($source, fn ($v) => !is_null($v) && $v !== '');
    }
}
