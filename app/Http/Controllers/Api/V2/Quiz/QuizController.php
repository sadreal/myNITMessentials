<?php

namespace App\Http\Controllers\Api\V2\Quiz;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\QuizRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Quiz\Entities\QuestionBank;

class QuizController extends Controller
{
    private $quizRepository;
    public function __construct(QuizRepositoryInterface $quizRepository)
    {
        $this->quizRepository = $quizRepository;
    }

    public function quizList(Request $request): object
    {
        return response()->json([
            'success' => true,
            'data' => $this->quizRepository->quizList($request),
            'message' => trans('api.Get question list successfully'),
        ]);
    }

    public function updateQuizStatus(Request $request): object
    {
        return $this->quizRepository->updateQuizStatus($request);
    }

    public function deleteQuiz(Request $request): object
    {
        return $this->quizRepository->deleteQuiz($request);
    }

    public function questions(Request $request): object
    {
        return response()->json([
            'success' => true,
            'data' => $this->quizRepository->questions($request),
            'message' => trans('api.Get question bank list successfully'),
        ]);
    }

    public function deleteQuestion(Request $request): object
    {
        if (demoCheck()) {
            return response()->json([
                'message' => trans('api.For the demo version, you cannot change this')
            ], Response::HTTP_UNAUTHORIZED);
        }

        $rules = [
            'question_id' => 'required|exists:question_banks,id',
        ];
        $request->validate($rules, validationMessage($rules));

        $quizAssign = QuestionBank::find($request->question_id)->quizAssign;
        if ($quizAssign->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => trans('quiz.You cannot delete this question because it has been used in') . ' ' . $quizAssign->count() . ' ' . trans('quiz.quiz already'),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $delete = $this->quizRepository->deleteQuestion($request);

        if ($delete) {
            $response = [
                'success' => true,
                'message' => trans('api.Question deleted successfully'),
            ];
        }
        return response()->json($response);
    }

    public function questionBankDetail(Request $request): object
    {
        if (demoCheck()) {
            return response()->json([
                'message' => trans('api.For the demo version, you cannot change this')
            ], Response::HTTP_UNAUTHORIZED);
        }

        $rules = [
            'question_id' => 'required|exists:question_banks,id',
        ];
        $request->validate($rules, validationMessage($rules));

        return response()->json([
            'success' => true,
            'data' => $this->quizRepository->questionBankDetail($request),
            'message' => trans('api.Getting questions details successfully'),
        ]);
    }
}
