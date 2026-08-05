<?php

namespace App\Http\Controllers;

use App\Modules\Exam\Models\Exam;
use App\Modules\Exam\Models\StudentExam;
use App\Modules\Grade\Models\Grade;
use App\Services\AssessmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExamController extends Controller
{
    public function __construct(
        private readonly AssessmentService $assessmentService,
    ) {}

    public function exam()
    {
        $grades = Grade::has('exams')->withCount('exams')->orderBy('id')->get();

        return view('pages.exam', compact('grades'));
    }

    public function examGrade(Grade $grade)
    {
        // Students may only browse exams for their own grade.
        $student = Auth::user()?->student;
        if ($student && $student->grade_id && $student->grade_id !== $grade->id) {
            abort(403, 'This exam is not available for your grade.');
        }

        $exams = $grade->exams()->with('grade')->withCount('questions')->get();

        $user = Auth::user();
        $student = $user?->student;

        // Get past attempt scores for each exam
        $examScores = [];
        if ($student && $exams->isNotEmpty()) {
            $attempts = StudentExam::where('student_id', $student->id)
                ->whereIn('exam_id', $exams->pluck('id'))
                ->get()
                ->groupBy('exam_id');

            foreach ($attempts as $examId => $records) {
                $total = $records->count();
                $correct = $records->where('is_correct', true)->count();
                $examScores[$examId] = $total > 0 ? round(($correct / $total) * 100) : null;
            }
        }

        return view('pages.exam', compact('grade', 'exams', 'examScores'));
    }

    public function examDetail(Exam $exam)
    {
        // Students may only view exams for their own grade.
        $student = Auth::user()?->student;
        if ($student && !$exam->isAvailableForGrade($student->grade_id)) {
            abort(403, 'This exam is not available for your grade.');
        }

        $exam->load('grade');
        $exam->loadCount('questions');
        $pastScore = null;

        if ($student) {
            $attempts = StudentExam::where('student_id', $student->id)
                ->where('exam_id', $exam->id)
                ->get();

            if ($attempts->isNotEmpty()) {
                $total = $attempts->count();
                $correct = $attempts->where('is_correct', true)->count();
                $pastScore = $total > 0 ? round(($correct / $total) * 100) : null;
            }
        }

        return view('pages.exam-detail', compact('exam', 'pastScore'));
    }

    public function examStart(Exam $exam)
    {
        $exam->load('questions', 'grade');

        // Students may only take exams for their own grade.
        $student = Auth::user()?->student;
        if ($student && $exam->grade_id && $student->grade_id && $student->grade_id !== $exam->grade_id) {
            abort(403, 'This exam is not available for your grade.');
        }

        $questions = $exam->questions->map(function ($q) {
            $data = [
                'id' => $q->id,
                'type' => $q->type,
                'answer_type' => $q->answer_type,
                'question' => $q->question ?? [],
                'difficulty_level' => $q->difficulty_level,
            ];

            if ($q->type === 'regular') {
                $data['variant_a'] = $q->variant_a ?? [];
                $data['variant_b'] = $q->variant_b ?? [];
                $data['variant_c'] = $q->variant_c ?? [];
                $data['variant_d'] = $q->variant_d ?? [];
                $data['variant_e'] = $q->variant_e ?? [];
            }

            return $data;
        });

        return view('pages.exam-solve', [
            'exam' => $exam,
            'questions' => $questions,
            'totalSteps' => $questions->count(),
        ]);
    }

    public function examSubmit(Request $request, Exam $exam)
    {
        $exam->load('questions');

        $request->validate([
            'answers' => 'required|array|min:1',
            'answers.*.question_id' => 'required|integer|exists:questions,id',
            'answers.*.answer' => 'nullable|string',
        ]);

        $answers = $request->input('answers', []);
        $user = Auth::user();
        $student = $user->student;

        abort_unless($student, 403, 'Only students can submit exams');

        $result = $this->assessmentService->submitExam($user, $student, $exam, $answers);

        return redirect()->route('exam.result', $exam)
            ->with('exam_result', $result);
    }

    public function examResult(Exam $exam)
    {
        $exam->load('grade', 'questions');
        $user = Auth::user();
        $student = $user->student;

        $result = session('exam_result');

        if (!$result && $student) {
            $attempts = StudentExam::where('student_id', $student->id)
                ->where('exam_id', $exam->id)
                ->with('question')
                ->get();

            if ($attempts->isNotEmpty()) {
                $result = $this->assessmentService->buildResultFromAttempts($attempts);
            }
        }

        if (!$result) {
            return redirect()->route('exam.detail', $exam);
        }

        return view('pages.exam-result', [
            'exam' => $exam,
            'result' => $result,
        ]);
    }
}
