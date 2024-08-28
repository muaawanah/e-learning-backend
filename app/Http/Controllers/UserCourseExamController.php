<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\McqOption;
use App\Models\Module;
use App\Models\Question;
use App\Models\UserExam;
use App\Models\UserMcqAnswer;
use App\Models\UserWrittenAnswer;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UserCourseExamController extends Controller
{
    public function fetchExamWithQuestion(Course $course, Exam $exam)
    {
        if (Carbon::now()->lessThan(Carbon::make($exam->opening_time))) {
            return response()->json([
                'message' => 'Exam is not yet open.'
            ], 403);
        }

        Module::query()
            ->where([
                'id' => $exam->module_id,
                'course_id' => $course->id,
            ])
            ->firstOrFail();

        $questions = $exam->questions()
            ->with([
                'mcq_options:id,question_id,option_text'
            ])
            ->get();

        $exam->questions = $questions->map(function ($question) {
            return [
                'id'            => $question->id,
                'type'          => $question->type,
                'question_text' => $question->question_text,
                'mcq_options'   => $question->mcq_options,
                'user_answers'  => (array) ([])
            ];
        });

        return response()->json($exam);
    }

    public function submitExamWithQuestion(Request $request, Course $course, Exam $exam)
    {
        if (Carbon::now()->lessThan(Carbon::make($exam->opening_time))) {
            return response()->json([
                'message' => 'Exam is not yet open.'
            ], 403);
        }

        Module::query()
            ->where([
                'id' => $exam->module_id,
                'course_id' => $course->id,
            ])
            ->firstOrFail();

        $answered_questions = $request->user_given_answers;

        // return
        $mcq_exam_questions = $exam->exam_questions()
            ->get();

        $mcq_correct_mark = 0;
        $mcq_negative_mark = 0;

        $user_id = auth('sanctum')->id();

        $user_exam = UserExam::create([
            'exam_id'       => $exam->id,
            'user_id'       => $user_id,
            'is_practice'   => now() > $exam->result_publish_time,
        ]);

        foreach($answered_questions as $answered_question) {
            if(!count($answered_question['user_answers'] ?? [])) {
                continue;
            }

            if($answered_question["type"] == 'MCQ') {
                $mark = $this->getMark($answered_question, $mcq_exam_questions, $exam);

                if ($mark > 0) {
                    $mcq_correct_mark += $mark;
                } elseif ($mark < 0) {
                    $mcq_negative_mark += (- $mark);
                }

                UserMcqAnswer::create([
                    'user_exam_id'  => $user_exam->id,
                    'question_id'   => $answered_question["id"],
                    'answers'       => $answered_question["user_answers"],
                    'mark'          => $mark,
                ]);
            } else {
                UserWrittenAnswer::create([
                    'user_exam_id'  => $user_exam->id,
                    'question_id'   => $answered_question["id"],
                    'answers'       => $answered_question["user_answers"],
                ]);
            }
        }
        
        $user_exam->update([
            'mcq_correct_mark'  => $mcq_correct_mark,
            'mcq_negative_mark' => $mcq_negative_mark,
        ]);

        $exam->user_exam = $user_exam;

        return response()->json($exam);
    }

    private function getMark($answered_question, $mcq_exam_questions, $exam)
    {
        $mark = 0;

        $selected_exam_question = $mcq_exam_questions->where('question_id', $answered_question["id"])->first();

        if(!$selected_exam_question) {
            $selected_exam_question = ExamQuestion::query()
                ->where([
                    'exam_id'       => $exam->id,
                    'question_id'   => $answered_question["id"],
                ])
                ->first();
        }

        $correct_option_ids = McqOption::query()
            ->where('question_id', $answered_question["id"])
            ->where('is_correct', 1)
            ->pluck('id')
            ->toArray();

        $user_selected_option_ids = $answered_question["user_answers"];

        $mark = (!array_diff($correct_option_ids, $user_selected_option_ids) && !array_diff($user_selected_option_ids, $correct_option_ids))
            ? $selected_exam_question->mark
            : - $selected_exam_question->negative_mark;

        return $mark;
    }

}
