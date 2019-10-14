<?php

namespace App\Http\Controllers;


use App\Cards;
use App\Cycle;
use App\Forms;
use App\Parameters;
use App\Points;
use App\Relation;
use App\Target;
use App\User;
use Illuminate\Http\Request;

class PointsController extends Controller
{
    public function getAppraiserList(Request $request)
    {
        try {
            $user = $request->auth;
            $relationsAsAppraiser = Relation::where('appraiser_id', $user->id)->get();

            $cyclesAsAppraiserIds = [];


            foreach ($relationsAsAppraiser as $item) {
                if(!in_array($item->cycle, $cyclesAsAppraiserIds)) {
                    $cyclesAsAppraiserIds[] = $item->cycle;
                }
            }


            $appraiserCycles = Cycle::WhereIn('id', $cyclesAsAppraiserIds)->get(['id', 'title', 'active']);

            $cycleRelationList = [];
            foreach ($appraiserCycles as $appraiserCycle) {
                $relations = [];
                $cycleRelations = Relation::where('appraiser_id', $user->id)->where('cycle', $appraiserCycle->id)->get();
                foreach ($cycleRelations as $cycleRelation) {
                    $employee = User::find($cycleRelation->appraisal_id);
                    $formId = $cycleRelation->form_id;
                    $form = Forms::find($formId);
                    $questionCount = count($form->parameters);
                    $answeredQuestions = Points::where('relation_id', $cycleRelation->id)->count();

                    $parameters = $form['parameters'];
                    $questions = [];
                    foreach ($parameters as $parameter) {
                        $target = 0;
                        if ($cycleRelation->weight > 0.1) {
                            $targetEntity = Target::where('cycle', $appraiserCycle->id)->where('user_id', $employee->id)->where('parameter_id', $parameter['id'])->first();
                            if ($targetEntity) {
                                $target = $targetEntity->target;
                            }
                        }
                        $param = Parameters::find($parameter['id']);
                        $point = Points::where('relation_id', $cycleRelation->id)->where('parameter_id', $parameter['id'])->first();
                        $questions[] = [
                            'questionId'=>$param->id,
                            'question'=>$param->title,
                            'point'=> $point?$point->point:null,
                            'target'=>$target?$target:null
                            ];
                    }
                    $relations[] = [
                        'employee'=> [
                            'id'=>$employee->id,
                            'name'=>$employee->name,
                            'email'=>$employee->email,
                            'done'=>$cycleRelation->evaluated
                        ],
                        'question'=>$questions,
                        'unanswered'=> $questionCount-$answeredQuestions
                    ];

                }

                $cycleRelationList[] = [
                    'cycle' => $appraiserCycle,
                    'relations'=>$relations
                ];

            }

            return response()->json($cycleRelationList);
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 400);
        }
    }


    public function getAppraisalList(Request $request)
    {
        try {
            $user = $request->auth;
            $relationsAsAppraisal = Relation::where('appraisal_id', $user->id)->get();

            $cyclesAsAppraisalIds = [];


            foreach ($relationsAsAppraisal as $item) {
                if(!in_array($item->cycle, $cyclesAsAppraisalIds)) {
                    $cyclesAsAppraisalIds[] = $item->cycle;
                }
            }


            $appraisalCycles = Cycle::WhereIn('id', $cyclesAsAppraisalIds)->get(['id', 'title', 'active']);

            $cycleRelationList = [];
            foreach ($appraisalCycles as $appraisalCycle) {
                $relations = [];
                $cycleRelations = Relation::where('appraisal_id', $user->id)->where('cycle', $appraisalCycle->id)->get();
                if(!$cycleRelations) {
                    continue;
                }
                $formId = $cycleRelations[0]->form_id;
                $form = Forms::find($formId);

                $parameters = $form['parameters'];

                foreach ($parameters as $parameter) {
                    $employees = [];
                    $param = Parameters::find($parameter['id']);
                    foreach ($cycleRelations as $cycleRelation) {
                        $employee = User::find($cycleRelation->appraiser_id);
                        $point = Points::where('relation_id', $cycleRelation->id)->where('parameter_id', $parameter['id'])->first();
                        $name = $employee->name;
                        $email= $employee->email;
                        if ($cycleRelation->weight < 0.1 && $cycleRelation->appraiser_id != $cycleRelation->appraisal_id) {
                            $name = 'همکار';
                            $email = 'hidden';

                        }
                        $employees[] = [
                            'name'=>$name,
                            'email'=>$email,
                            'point'=> $point?$point->point:null
                        ];
                    }
                    $relations[] = [
                        'questions'=> [
                            'question'=>$param->title,
                        ],
                        'employee'=>$employees
                    ];

                }



                $cycleRelationList[] = [
                    'cycle' => $appraisalCycle,
                    'relations'=>$relations
                ];

            }

            return response()->json($cycleRelationList);
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 400);
        }
    }

    public function getFormAction(Request $request)
    {
        try {
//            $query = json_decode($request->getContent(), true);
//            $formId = $query['formId'];

            $formId = $request->get('formId');

            $form = Forms::find($formId);

            return response()->json($form);
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 400);
        }
    }


public function setPointAction(Request $request)
    {
        try {
            $query = json_decode($request->getContent(), true);
            $employeeId = $query['employeeId'];
            $parameterId = $query['questionId'];
            $appraiserId = $request->auth->id;
            $point = $query['point'];

            if (($point < 1) && ($point > 10)) {
                throw new \Exception('امتیاز وارد شده صحیح نیست.');
            }
            $lastCycle = Cycle::orderBy('id', 'DESC')->first();
            $relation = Relation::where('appraisal_id', $employeeId)->where('appraiser_id', $appraiserId)->where('cycle', $lastCycle->id)->first();
            $cycleId = $relation->cycle;
            $cycle = Cycle::find($cycleId);
            if (!$cycle->active) {
                throw new \Exception('مهلت این نظرسنجی به پایان رسیده است.');
            }


            if (!$relation) {
                throw new \Exception('Relation Not found');
            }





            $pointEntity = Points::where('relation_id', $relation->id)->where('parameter_id', $parameterId)->first();


            if(!$pointEntity) {
                $pointDB = new Points(
                    [
                        'parameter_id'=>$parameterId,
                        'relation_id' =>$relation->id,
                        'point'=>$point
                    ]
                );
                $pointDB->save();
            } else {
                $pointEntity->update(['point'=>$point]);
            }
            $form = Forms::find($relation->form_id);
            $questionCount = count($form->parameters);
            $answeredQuestions = Points::where('relation_id', $relation->id)->count();
            if ($questionCount == $answeredQuestions) {
                $relation->update(['evaluated' => true]);
            }
            return response()->json(['message'=>'پیام شما ثبت شد.', 'unanswered'=>$questionCount-$answeredQuestions], 200);
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 400);
        }
    }

    public function calculateKPI($userId, $cycleId)
    {
        $relations = Relation::where('appraisal_id', $userId)->where('cycle', $cycleId)->where('evaluated', 1)->get();

        if(!$relations) {
            return 'هنوز ارزیابی انجام نشده است.';
        }


        $relationWeights = 0;
        $finalPoint = 0;
        foreach ($relations as $relation) {
            $relationWeight = $relation->weight;
//            if (!$pointsEntity) {
//                continue;
//            }
            $formId = $relation->form_id;
            $form = Forms::find($formId);
            $parameters = $form->parameters;
            $categories = $form->categories;
            $values = $form->values;
            $totalPoint = 0;

            foreach ($parameters as $parameter) {
                $pointsEntity = Points::where('relation_id', $relation->id)->where('parameter_id', $parameter['id'])->first();
                $parameterPoint = $pointsEntity->point * $parameter['weight'];
                $categoryId = $parameter['categoryId'];
                foreach ($categories as $category) {
                    if ($category['id'] == $categoryId) {
                       $categoryPoint = $parameterPoint * $category['weight'];
                        $valueId = $category['valueId'];
                       break;
                    }
                }
                foreach ($values as $value) {
                    if ($value['id'] == $valueId) {
                        $valuePoint = $categoryPoint * $value['weight'];
                        $totalPoint += $valuePoint;
                        break;
                    }
                }

            }
            $relationPoint = $totalPoint * $relationWeight;
            $finalPoint +=$relationPoint;
            $relationWeights += $relationWeight;
        }


        return $relationWeights ? round($finalPoint * 10/$relationWeights, 2):0;

    }
}

