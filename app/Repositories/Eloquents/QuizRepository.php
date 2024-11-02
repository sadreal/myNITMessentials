<?php

namespace App\Repositories\Eloquents;

use App\Http\Resources\api\v2\Quize\CourseQuizDetailResource;
use App\Http\Resources\api\v2\Quize\QuestionBankDetailResource;
use App\Http\Resources\api\v2\Quize\QuestionGroupListResource;
use App\Http\Resources\api\v2\Quize\QuestionLevelListResource;
use App\Http\Resources\api\v2\Quize\QuestionListResource;
use App\Http\Resources\api\v2\Quize\QuestionsResource;
use App\Http\Resources\api\v2\Quize\QuizListResource;
use App\Jobs\SendGeneralEmail;
use App\Repositories\Interfaces\QuizRepositoryInterface;
use App\Traits\UploadMedia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Modules\AdvanceQuiz\Entities\MatchingTypeQuestionAssign;
use Modules\AdvanceQuiz\Http\Controllers\AdvanceQuizGroupController;
use Modules\Assignment\Entities\InfixAssignment;
use Modules\CourseSetting\Entities\Chapter;
use Modules\CourseSetting\Entities\Course;
use Modules\CourseSetting\Entities\Lesson;
use Modules\Quiz\Entities\OnlineExamQuestionAssign;
use Modules\Quiz\Entities\OnlineQuiz;
use Modules\Quiz\Entities\QuestionBank;
use Modules\Quiz\Entities\QuestionBankMuOption;
use Modules\Quiz\Entities\QuestionGroup;
use Modules\Quiz\Entities\QuestionLevel;
use Modules\Quiz\Entities\QuizeSetup;
use Modules\Quiz\Entities\QuizTest;

class QuizRepository implements QuizRepositoryInterface
{
    use UploadMedia;

    public function storeCourseQuiz(object $request): void
    {
        if ($request->type == 'long_form') {
            DB::transaction(function () use ($request) {
                $sub = $request->sub_category;
                if (empty($sub)) {
                    $sub = null;
                }
                $online_exam = new OnlineQuiz();

                foreach ($request->title as $key => $title) {
                    $online_exam->setTranslation('title', $key, $title);
                }
                foreach ($request->instruction as $key => $instruction) {
                    $online_exam->setTranslation('instruction', $key, $instruction);
                }

                $online_exam->category_id = (int)$request->category;
                $online_exam->sub_category_id = (int)$sub;
                $online_exam->course_id = (int)$request->course_id;
                $online_exam->percentage = $request->percentage;
                $online_exam->status = 1;
                $online_exam->created_by = auth()->id();

                $setup = QuizeSetup::getData();
                $online_exam->random_question = $setup->random_question == 1 ? 1 : 0;
                $online_exam->question_time_type = $setup->set_per_question_time == 1 ? 0 : 1;
                $online_exam->question_time = $setup->set_per_question_time == 1 ? $setup->time_per_question : $setup->time_total_question;
                $online_exam->question_review = $setup->question_review == 1 ? 1 : 0;
                $online_exam->show_result_each_submit = $setup->show_result_each_submit == 1 ? 1 : 0;
                $online_exam->multiple_attend = $setup->multiple_attend == 1 ? 1 : 0;
                $online_exam->show_ans_with_explanation = $setup->show_ans_with_explanation == 1 ? 1 : 0;

                $online_exam->save();

                $user = auth()->user();
                if ($user->role_id == 2) {
                    $course = Course::where('id', $request->course_id)->where('user_id', auth()->id())->first();
                } else {
                    $course = Course::where('id', $request->course_id)->first();
                }
                $chapter = Chapter::find($request->chapterId);
                if (isset($course) && isset($chapter)) {
                    $lesson = new Lesson();
                    $lesson->course_id = $course->id;
                    $lesson->chapter_id = $chapter->id;
                    $lesson->quiz_id = $online_exam->id;
                    $lesson->is_quiz = (int)$request->is_quiz;
                    $lesson->is_lock = (int)$request->lock;
                    $lesson->save();

                    $quiz = OnlineQuiz::find($online_exam->id);
                    $codes = [
                        'time' => Carbon::now()->format('d-M-Y, g:i A'),
                        'course' => $course->title,
                        'chapter' => $chapter->name,
                        'quiz' => $quiz->title,
                    ];

                    $act = 'Course_Quiz_Added';
                    $this->quizStoreNotification($course, $act, $codes);
                }
            });
        } else {
            $user = auth()->user();
            if ($user->role_id == 2) {
                $course = Course::where('id', $request->course_id)->where('user_id', auth()->id())->first();
            } else {
                $course = Course::where('id', $request->course_id)->first();
            }
            $chapter = Chapter::find($request->chapterId);

            if (isset($course) && isset($chapter)) {
                $lesson = new Lesson();
                $lesson->course_id = $request->course_id;
                $lesson->chapter_id = $request->chapterId;
                $lesson->quiz_id = $request->quiz;
                $lesson->is_quiz = $request->is_quiz;
                $lesson->is_lock = (int)$request->lock;
                $lesson->save();
                $quiz = OnlineQuiz::find($request->quiz);

                $act = 'Course_Quiz_Added';
                $codes = [
                    'time' => Carbon::now()->format('d-M-Y, g:i A'),
                    'course' => $course->title,
                    'chapter' => $chapter->name,
                    'quiz' => $quiz->title,
                ];
                $this->quizStoreNotification($course, $act, $codes);
            }
        }
    }

    public function quizDetail(object $request): object
    {
        $lesson = Lesson::where('course_id', $request->course_id)
            ->where('chapter_id', $request->chapter_id)
            ->where('is_quiz', 1)
            ->where('quiz_id', $request->quiz_id)
            ->firstOrFail();

        return new CourseQuizDetailResource($lesson);
    }

    public function courseQuizUpdate(object $request): void
    {
        DB::transaction(function () use ($request) {
            $sub = $request->sub_category_id;
            if (empty($sub)) {
                $sub = null;
            }
            $online_exam = OnlineQuiz::find($request->quiz_id);
            foreach ($request->title as $key => $title) {
                $online_exam->setTranslation('title', $key, $title);
            }
            foreach ($request->instruction as $key => $instruction) {
                $online_exam->setTranslation('instruction', $key, $instruction);
            }
            $online_exam->category_id = (int)$request->category_id;
            $online_exam->sub_category_id = (int)$sub;
            $online_exam->percentage = (int)$request->percentage;

            $online_exam->status = 0;
            $online_exam->created_by = auth()->user()->id;
            $online_exam->save();
            $course = $online_exam->course;
            if ($request->lock) {
                $lesson = Lesson::whereNotNull('is_quiz')->where('quiz_id', $online_exam->id)->where('course_id', $course->id)->first();
                if ($lesson) {
                    $lesson->is_lock = (int)$request->lock;
                    $lesson->save();
                }
            }
        });
    }

    public function storeQuestion(object $request): void
    {
        $user = auth()->user();

        $online_question = new QuestionBank();
        $online_question->type              = $request->question_type;
        $online_question->q_group_id        = $request->group;
        $online_question->category_id       = (int)$request->category;
        $online_question->sub_category_id   = (int)$request->sub_category;
        $online_question->marks             = (int)$request->marks;
        $online_question->question          = $request->question;
        $online_question->user_id           = (int)$user->id;
        $online_question->shuffle           = (int)$request->question_type == 'M' ? $request->shuffle : 0;

        if (isModuleActive('AdvanceQuiz')) {
            $online_question->level         = (int)$request->level;
            $online_question->pre_condition = (int)$request->get('pre_condition', 0);
        }
        $online_question->save();

        if ($request->image) {
            $online_question->image = $this->generateLink($request->image, $online_question->id, get_class($online_question), 'image');
        }
        $online_question->save();
        if ($request->question_type == 'M') {
            $online_question->explanation       = $request->explanation;
            $online_question->number_of_option  = $request->number_of_option;
            $online_question->save();
            $online_question->toArray();
            $i = 0;
            if (isset($request->option)) {
                foreach ($request->option as $option) {
                    $i++;
                    $option_check = 'option_check_' . $i;
                    $online_question_option = new QuestionBankMuOption();
                    $online_question_option->question_bank_id   = $online_question->id;
                    $online_question_option->title              = $option;
                    if (isset($request->$option_check)) {
                        $online_question_option->status = 1;
                    } else {
                        $online_question_option->status = 0;
                    }
                    $online_question_option->save();
                }
            }
            if ($request->quize_id) {
                $assign = new OnlineExamQuestionAssign();
                $assign->online_exam_id     = $request->quize_id;
                $assign->question_bank_id   = $online_question->id;
                $assign->save();
            }
        } elseif ($request->question_type == 'X') {
            $online_question->number_of_qus = $request->number_of_qus;
            $online_question->number_of_ans = $request->number_of_ans;
            $online_question->data          = $request->data;
            $online_question->connection    = $request->connection;
            $online_question->save();

            $qus = [];
            $ans = [];
            foreach ((array)$request->qus as $key => $option) {
                $online_question_option = new QuestionBankMuOption();
                $online_question_option->question_bank_id   = $online_question->id;
                $online_question_option->title              = $option;
                $online_question_option->status             = 0;
                $online_question_option->type               = 1;
                $online_question_option->option_index       = $key;
                $online_question_option->save();
                $qus[] = $online_question_option->id;
            }

            foreach ((array)$request->ans as $key => $option) {
                $online_question_option = new QuestionBankMuOption();
                $online_question_option->question_bank_id   = $online_question->id;
                $online_question_option->title              = $option;
                $online_question_option->status             = 0;
                $online_question_option->type               = 0;
                $online_question_option->option_index       = $key;
                $online_question_option->save();
                $ans[] = $online_question_option->id;
            }

            $connection = $request->connection;
            $connection = explode(',', $connection);
            foreach ($connection as $con) {
                $con = explode('|', $con);
                if (empty($con)) {
                    continue;
                }
                if (isset($con[0]) && isset($con[1])) {
                    $qusKey = explode('-', $con[0])[0];
                    $ansKey = explode('-', $con[1])[0];
                    MatchingTypeQuestionAssign::create([
                        'question_id'   => $online_question->id,
                        'option_id'     => $qus[$qusKey],
                        'answer_id'     => $ans[$ansKey],
                    ]);
                }
            }
        }
    }


    public function updateQuizQuestion(object $request): void
    {
        /* if (demoCheck()) {
            return redirect()->back();
        } */

        $id = $request->question_id;
        $online_question = QuestionBank::find($id);
        $online_question->type = $request->question_type;
        $online_question->q_group_id = $request->group;
        $online_question->category_id = (int)$request->category;
        $online_question->sub_category_id = (int)$request->sub_category;
        $online_question->marks = (int)$request->marks;
        $online_question->shuffle = (int)$request->question_type == 'M' ? $request->shuffle : 0;
        $online_question->question = $request->question;
        if (isModuleActive('AdvanceQuiz')) {
            $online_question->level = (int)$request->level;
            $online_question->pre_condition = (int)$request->get('pre_condition', 0);
        }

        $online_question->image = null;
        $online_question->save();

        $this->removeLink($online_question->id, get_class($online_question));
        if ($request->image) {
            $online_question->image = $this->generateLink($request->image, $online_question->id, get_class($online_question), 'image');
        }
        $online_question->save();

        if ($request->question_type == 'M') {
            $i = 0;
            if (isset($request->option)) {
                QuestionBankMuOption::where('question_bank_id', $online_question->id)->delete();
                foreach ($request->option as $option) {
                    $i++;
                    $option_check = 'option_check_' . $i;
                    $online_question_option = new QuestionBankMuOption();
                    $online_question_option->question_bank_id = (int)$online_question->id;
                    $online_question_option->title = $option;
                    if (isset($request->$option_check)) {
                        $online_question_option->status = 1;
                    } else {
                        $online_question_option->status = 0;
                    }
                    $online_question_option->save();
                }
            }
        } elseif ($request->question_type == 'X') {


            $online_question->number_of_qus = (int)$request->number_of_qus;
            $online_question->number_of_ans = (int)$request->number_of_ans;
            $online_question->data = $request->data;
            $online_question->connection = $request->connection;
            $online_question->save();
            QuestionBankMuOption::where('question_bank_id', $online_question->id)->delete();
            MatchingTypeQuestionAssign::where('question_id', $online_question->id)->delete();
            $qus = [];
            $ans = [];
            foreach ((array)$request->qus as $key => $option) {
                $online_question_option = new QuestionBankMuOption();
                $online_question_option->question_bank_id = $online_question->id;
                $online_question_option->title = $option;
                $online_question_option->status = 0;
                $online_question_option->type = 1;
                $online_question_option->save();
                $qus[] = $online_question_option->id;
            }

            foreach ((array)$request->ans as $key => $option) {
                $online_question_option = new QuestionBankMuOption();
                $online_question_option->question_bank_id = $online_question->id;
                $online_question_option->title = $option;
                $online_question_option->status = 0;
                $online_question_option->type = 0;
                $online_question_option->save();
                $ans[] = $online_question_option->id;
            }


            $connection = $request->connection;
            $connection = explode(',', $connection);
            foreach ($connection as $con) {
                $con = explode('|', $con);
                if (empty($con)) {
                    continue;
                }
                if (isset($con[0]) && isset($con[1])) {
                    $qusKey = explode('-', $con[0])[0];
                    $ansKey = explode('-', $con[1])[0];
                    MatchingTypeQuestionAssign::create([
                        'question_id' => $online_question->id,
                        'option_id' => (int)$qus[$qusKey],
                        'answer_id' => (int)$ans[$ansKey],
                    ]);
                }
            }
        }
    }

    public function groupList(object $request): object
    {
        $groups = QuestionGroup::where('active_status', 1)
            ->when(isModuleActive('AdvanceQuiz'), function ($group) {
                $group->orderBy('order');
            })
            ->when($search = $request->search, function ($groups) use ($search) {
                $groups->whereLike('title', $search);
            })->paginate($request->per_page ?? 10);
        return QuestionGroupListResource::collection($groups);
    }

    public function questionLevels(object $request): object
    {
        $levels = QuestionLevel::when($search = $request->search, function ($levels) use ($search) {
            $levels->whereLike('title', $search);
        })->paginate($request->get('per_page', 10));

        return QuestionLevelListResource::collection($levels);
    }

    public function questionTypes(): object
    {
        $types = [
            'M'     => 'Multiple Choice',
            'S'     => 'Short Answer',
            'L'     => 'Long Answer',
            'X'     => 'Matching Type',
        ];
        $response = [
            'success'   => true,
            'data'      => $types,
            'message'   => trans('api.Operation successful'),
        ];
        return response()->json($response);
    }

    public function deleteLesson(object $request): void
    {
        $lesson = Lesson::where('course_id', $request->course_id)->where('chapter_id', $request->chapter_id)->where('is_quiz', 1)->find($request->content_id);
        if (auth()->user()->role_id == 2) {
            $course = Course::where('user_id', auth()->id())->find($lesson->course_id);
        } else {
            $course = Course::find($lesson->course_id);
        }

        if ($course) {
            if ($lesson->is_assignment == 1) {
                $assignment = InfixAssignment::find($lesson->assignment_id);
                $assignment->delete();
            }
            $this->lessonFileDelete($lesson);

            if (isModuleActive('BunnyStorage')) {
                if ($lesson->bunnyLesson) {
                    $lesson->bunnyLesson->delete();
                }
            }
            $lesson->delete();
        }
        // return false;
    }

    public function questionList(object $request): object
    {
        $quizId = Lesson::where('course_id', $request->course_id)
            ->where('chapter_id', $request->chapter_id)
            ->where('is_quiz', 1)
            ->where('quiz_id', $request->quiz_id)
            ->first()->quiz_id;

        $questionIds = OnlineExamQuestionAssign::where('online_exam_id', $quizId)->pluck('question_bank_id');

        return QuestionListResource::collection($questionIds);
    }

    public function questions(object $request): object
    {
        $user = auth()->user();
        if ($user->role_id == 2) {
            $queries = QuestionBank::latest()->select('question_banks.*')->where('question_banks.active_status', 1)->with('category', 'subCategory', 'questionGroup')->where('question_banks.user_id', $user->id);
        } else {
            $queries = QuestionBank::latest()->select('question_banks.*')->where('question_banks.active_status', 1)->with('category', 'subCategory', 'questionGroup');
        }
        $queries->withCount('quizAssign');

        if ($request->group) {
            if (isModuleActive('AdvanceQuiz')) {
                $group = QuestionGroup::find($request->group);
                $ids = $group->getAllChildIds($group, [$group->id]);
                $queries->whereIn('q_group_id', $ids);
            } else {
                $queries->where('q_group_id', $request->group);
            }
        }
        if (isModuleActive('Organization') && auth()->user()->isOrganization()) {
            $queries->whereHas('user', function ($q) {
                $q->where('organization_id', auth()->id());
                $q->orWhere('id', auth()->id());
            });
        }

        $questions = $queries->when($search = $request->search, function ($questions) use ($search) {
            $questions->whereLike('question', $search);
        })->paginate($request->per_page ?? 10);

        return QuestionsResource::collection($questions);
    }

    public function questionDetail(object $request): object
    {
        $quizId = Lesson::where('course_id', $request->course_id)
            ->where('chapter_id', $request->chapter_id)
            ->where('is_quiz', 1)
            ->where('quiz_id', $request->quiz_id)->first()->quiz_id;

        $questionId = OnlineExamQuestionAssign::where('online_exam_id', $quizId)->where('question_bank_id', $request->question_id)->first()->question_bank_id;

        return new QuestionListResource($questionId);
    }

    public function updateQuestion(object $request): array
    {
        $quizId = Lesson::where('course_id', $request->course_id)
            ->where('chapter_id', $request->chapter_id)
            ->where('is_quiz', 1)
            ->where('quiz_id', $request->quiz_id)->first()->quiz_id;

        $questionId = OnlineExamQuestionAssign::where('online_exam_id', $quizId)->where('question_bank_id', $request->question_id)->first()->question_bank_id;

        $online_question = QuestionBank::find($questionId);
        $online_question->type = $request->question_type;
        $online_question->q_group_id = $request->group;
        $online_question->category_id = (int)$request->category;
        $online_question->sub_category_id = (int)$request->sub_category;
        $online_question->marks = (int)$request->marks;
        $online_question->shuffle = (int)$request->question_type == 'M' ? $request->shuffle : 0;
        $online_question->question = $request->question;
        if (isModuleActive('AdvanceQuiz')) {
            $online_question->level = (int)$request->level;
            $online_question->pre_condition = (int)$request->get('pre_condition', 0);
        }

        $online_question->image = null;
        $online_question->save();

        $this->removeLink($online_question->id, get_class($online_question));
        if ($request->image) {
            $online_question->image = $this->generateLink($request->image, $online_question->id, get_class($online_question), 'image');
        }
        $online_question->save();

        if ($request->question_type == 'M') {
            $i = 0;
            if (isset($request->option)) {
                QuestionBankMuOption::where('question_bank_id', $online_question->id)->delete();
                foreach ($request->option as $option) {
                    $i++;
                    $option_check = 'option_check_' . $i;
                    $online_question_option = new QuestionBankMuOption();
                    $online_question_option->question_bank_id = (int)$online_question->id;
                    $online_question_option->title = $option;
                    if (isset($request->$option_check)) {
                        $online_question_option->status = 1;
                    } else {
                        $online_question_option->status = 0;
                    }
                    $online_question_option->save();
                }
            }
        } elseif ($request->question_type == 'X') {
            $online_question->number_of_qus = (int)$request->number_of_qus;
            $online_question->number_of_ans = (int)$request->number_of_ans;
            $online_question->data = $request->data;
            $online_question->connection = $request->connection;
            $online_question->save();
            QuestionBankMuOption::where('question_bank_id', $online_question->id)->delete();
            MatchingTypeQuestionAssign::where('question_id', $online_question->id)->delete();
            $qus = [];
            $ans = [];
            foreach ((array)$request->qus as $key => $option) {
                $online_question_option = new QuestionBankMuOption();
                $online_question_option->question_bank_id = $online_question->id;
                $online_question_option->title = $option;
                $online_question_option->status = 0;
                $online_question_option->type = 1;
                $online_question_option->save();
                $qus[] = $online_question_option->id;
            }

            foreach ((array)$request->ans as $key => $option) {
                $online_question_option = new QuestionBankMuOption();
                $online_question_option->question_bank_id = $online_question->id;
                $online_question_option->title = $option;
                $online_question_option->status = 0;
                $online_question_option->type = 0;
                $online_question_option->save();
                $ans[] = $online_question_option->id;
            }

            $connection = $request->connection;
            $connection = explode(',', $connection);
            foreach ($connection as $con) {
                $con = explode('|', $con);
                if (empty($con)) {
                    continue;
                }
                if (isset($con[0]) && isset($con[1])) {
                    $qusKey = explode('-', $con[0])[0];
                    $ansKey = explode('-', $con[1])[0];
                    $data = MatchingTypeQuestionAssign::create([
                        'question_id' => $online_question->id,
                        'option_id' => (int)$qus[$qusKey],
                        'answer_id' => (int)$ans[$ansKey],
                    ]);
                }
            }
        }
        return $data ?? [];
    }



    public function storeQuestionGroup(object $request): bool
    {
        if (isModuleActive('AdvanceQuiz')) {
            $AdvanceQuizGroupController = new AdvanceQuizGroupController();
            $AdvanceQuizGroupController->createOrUpdate($request);
            return true;
        } else {
            $group = new QuestionGroup();
            $group->title = $request->title;
            $group->user_id = auth()->id();
            $group->save();
            return true;
        }
    }

    public function updateQuestionGroup(object $request): bool
    {
        if (isModuleActive('AdvanceQuiz')) {
            $AdvanceQuizGroupController = new AdvanceQuizGroupController();
            $AdvanceQuizGroupController->createOrUpdate($request, $request->id);
            return true;
        } else {
            $group = QuestionGroup::find($request->id);
            $group->title = $request->title;
            $group->save();
            return true;
        }
    }

    public function orderQuestionGroup(object $request): bool
    {
        if (isModuleActive('AdvanceQuiz') && count($request->group_ids) > 0) {
            $ids = $request->group_ids;
            foreach ($ids as $key => $id) {
                $lesson = QuestionGroup::find($id);
                if ($lesson) {
                    $lesson->order = $key + 1;
                    $lesson->save();
                }
            }
            return true;
        } else {
            return false;
        }
    }

    public function destroyQuestionGroup(object $request): bool
    {
        $id = $request->group_id;
        if (isModuleActive('AdvanceQuiz')) {
            $group = QuestionGroup::findOrFail($id);
            $childs = $group->getAllChildIds($group);
            $group->delete();
            foreach ($childs as $child) {
                $b = QuestionGroup::where('id', $child)->first();
                $b->delete();
            }
            return true;
        } else {
            $group = QuestionGroup::destroy($id);
            return true;
        }
    }

    public function storeQuestionLevel(object $request): bool
    {
        if (isModuleActive('AdvanceQuiz')) {
            $level = new QuestionLevel();
            $level->id = QuestionLevel::max('id') + 1;
            foreach ($request->title as $key => $title) {
                $level->setTranslation('title', $key, $title);
            }
            $level->save();
            return true;
        } else {
            return false;
        }
    }

    public function updateQuestionLevel(object $request): bool
    {
        if (isModuleActive('AdvanceQuiz')) {
            $id = $request->level_id;
            $edit = QuestionLevel::find($id);
            foreach ($request->title as $key => $title) {
                $edit->setTranslation('title', $key, $title);
            }
            $edit->save();

            return true;
        } else {
            return false;
        }
    }

    public function updateQuestionLevelStatus(object $request): bool
    {
        if (isModuleActive('AdvanceQuiz')) {
            QuestionLevel::find($request->level_id)->update([
                'status' => (bool)$request->status
            ]);
            return true;
        } else {
            return false;
        }
    }

    public function deleteQuestionLevel(object $request): bool|object
    {
        if (isModuleActive('AdvanceQuiz')) {
            $level = QuestionLevel::withCount('questions')->find($request->level_id);

            $hasQuestion = $level->questions_count;
            if ($hasQuestion > 0) {
                return response()->json([
                    'message' => trans('quiz.This level has been used in') . ' ' . $hasQuestion . ' ' . trans('quiz.questions') . ' ' . trans('quiz.and cannot be deleted')
                ]);
            } else {
                $level->delete();
                return true;
            }
        } else {
            return false;
        }
    }

    public function quizList(object $request): object
    {
        $quizes = OnlineQuiz::where('active_status', 1)
            ->when($search = $request->search, function ($quizes) use ($search) {
                $quizes->whereLike('title', $search);
            })->paginate($request->get('per_page', 10));

        return QuizListResource::collection($quizes);
    }

    public function updateQuizStatus(object $request): object
    {
        $rules = [
            'quiz_id' => 'required|exists:online_quizzes,id',
            'status' => 'nullable|boolean',
        ];

        $request->validate($rules, validationMessage($rules));

        if (demoCheck()) {
            return response()->json([
                'message' => trans('api.For the demo version, you cannot change this')
            ]);
        }

        OnlineQuiz::where('active_status', 1)
            ->find($request->quiz_id)
            ->update([
                'status' => (bool)$request->status
            ]);

        $response = [
            'success' => true,
            'message' => trans('api.Quiz status changed successfully'),
        ];

        return response()->json($response);
    }

    public function deleteQuiz(object $request): object
    {
        $rules = [
            'quiz_id' => 'required|exists:online_quizzes,id',
        ];

        $request->validate($rules, validationMessage($rules));

        if (demoCheck()) {
            return response()->json([
                'message' => trans('api.For the demo version, you cannot change this')
            ]);
        }

        $check = QuizTest::where('quiz_id', $request->quiz_id)->count();
        if ($check > 0) {
            return response()->json([
                'message' => trans('quiz.You cannot delete this quiz because it has been taken by users')
            ]);
        }

        $lessons = Lesson::where('quiz_id', $request->quiz_id)->get();
        foreach ($lessons as $lesson) {
            $lesson->delete();
        }
        $questions = OnlineExamQuestionAssign::where('online_exam_id', $request->quiz_id)->get();
        foreach ($questions as $question) {
            $question->delete();
        }
        OnlineQuiz::destroy($request->quiz_id);
        $response = [
            'success' => true,
            'message' => trans('api.Quiz deleted successfully'),
        ];

        return response()->json($response);
    }

    public function deleteQuestion(object $request): bool
    {
        $quizAssign = QuestionBank::find($request->question_id)->quizAssign;
        if ($quizAssign->count() < 1) {
            $id = $request->question_id;
            $online_question = QuestionBank::find($id);
            if ($online_question->type == "M") {
                QuestionBankMuOption::where('question_bank_id', $online_question->id)->delete();
            }
            $online_question->delete();
            return true;
        } else {
            return false;
        }
    }

    public function questionBankDetail(object $request): object
    {
        $id = $request->question_id;
        $bank = QuestionBank::find($id);
        return new QuestionBankDetailResource($bank);
    }

    private function lessonFileDelete($lesson): bool
    {
        try {
            $host = $lesson->host;
            if ($host == "SCORM") {
                $index = $lesson->video_url;
                if (!empty($index)) {
                    $new_path = str_replace("/public/uploads/scorm/", "", $index);
                    $folder = explode('/', $new_path)[0] ?? '';
                    $delete_dir = public_path() . "/uploads/scorm/" . $folder;

                    if (File::isDirectory($delete_dir)) {
                        File::deleteDirectory($delete_dir);
                    }
                }
            } elseif (in_array($host, ['Self', 'Image', 'PDF', 'Word', 'Excel', 'PowerPoint', 'Text', 'Zip'])) {
                $file = File::exists($lesson->video_url);

                if ($file) {
                    File::delete($lesson->video_url);
                }
            }
        } catch (\Exception $e) {
        }
        return true;
    }
    private function quizStoreNotification($course, $act, $codes)
    {
        try {
            if (isset($course->enrollUsers) && !empty($course->enrollUsers)) {
                foreach ($course->enrollUsers as $user) {
                    if (UserEmailNotificationSetup($act, $user)) {
                        SendGeneralEmail::dispatch($user, $act, $codes);
                    }
                    if (UserBrowserNotificationSetup($act, $user)) {

                        send_browser_notification(
                            $user,
                            $act,
                            $codes,
                            trans('common.View'),
                            courseDetailsUrl(@$course->id, @$course->type, @$course->slug),
                        );
                    }

                    if (UserMobileNotificationSetup($act, $user) && !empty($user->device_token)) {
                        send_mobile_notification($user, $act, $codes);
                    }

                    if (UserSmsNotificationSetup($act, $user)) {

                        send_sms_notification($user, $act, $codes);
                    }
                }
            }
            $courseUser = $course->user;
            if (UserEmailNotificationSetup($act, $courseUser)) {
                SendGeneralEmail::dispatch($courseUser, $act, $codes);
            }
            if (UserBrowserNotificationSetup($act, $courseUser)) {

                send_browser_notification(
                    $courseUser,
                    $act,
                    $codes,
                    trans('common.View'),
                    courseDetailsUrl(@$course->id, @$course->type, @$course->slug),
                );
            }

            if (UserMobileNotificationSetup($act, $courseUser) && !empty($courseUser->device_token)) {
                send_mobile_notification($courseUser, $act, $codes);
            }

            if (UserSmsNotificationSetup($act, $courseUser)) {
                send_sms_notification($courseUser, $act, $codes);
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
