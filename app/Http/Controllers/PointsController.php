<?php

namespace App\Http\Controllers;


use App\Cards;
use App\Cycle;
use App\Forms;
use App\Parameters;
use App\Points;
use App\Relation;
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
            $relations = [];
            foreach ($appraiserCycles as $appraiserCycle) {
                $cycleRelations = Relation::where('appraiser_id', $user->id)->where('cycle', $appraiserCycle->id)->get();
                foreach ($cycleRelations as $cycleRelation) {
                    $employee = User::find($cycleRelation->appraisal_id);
                    $formId = $cycleRelation->form_id;
                    $form = Forms::find($formId);

                    $parameters = $form['parameters'];
                    $questions = [];
                    foreach ($parameters as $parameter) {
                        $param = Parameters::find($parameter['id']);
                        $point = Points::where('relation_id', $cycleRelation->id)->where('parameter_id', $parameter['id'])->first();
                        $questions[] = [
                            'question'=>$param->title,
                            'point'=> $point?$point->point:null
                            ];
                    }
                    $relations[] = [
                        'employee'=> [
                            'name'=>$employee->name,
                            'email'=>$employee->email
                        ],
                        'question'=>$questions
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
            $relations = [];
            foreach ($appraisalCycles as $appraisalCycle) {
                $cycleRelations = Relation::where('appraisal_id', $user->id)->where('cycle', $appraisalCycle->id)->get();
                foreach ($cycleRelations as $cycleRelation) {
                    $employee = User::find($cycleRelation->appraiser_id);
                    if ($cycleRelation->type != 'lead' || $cycleRelation->appraiser_id != $user->id) {
                        continue;
                    }
                    $formId = $cycleRelation->form_id;
                    $form = Forms::find($formId);

                    $parameters = $form['parameters'];
                    $questions = [];
                    foreach ($parameters as $parameter) {
                        $param = Parameters::find($parameter['id']);
                        $point = Points::where('relation_id', $cycleRelation->id)->where('parameter_id', $parameter['id'])->first();
                        $questions[] = [
                            'question'=>$param->title,
                            'point'=> $point?$point->point:null
                        ];
                    }
                    $relations[] = [
                        'employee'=> [
                            'name'=>$employee->name,
                            'email'=>$employee->email
                        ],
                        'question'=>$questions
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
            $query = json_decode($request->getContent(), true);
            $formId = $query['formId'];

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
            $parameterId = $query['$parameterId'];
            $appraiserId = $request->auth->id;
            $point = $query['point'];

            $relation = Relation::where('appraisal_id', $employeeId)->where('appraiser_id', $appraiserId)->first();
            $cycleId = $relation->cycle;
            $cycle = Cycle::find($cycleId);
            if (!$cycle->active) {
                throw new \Exception('مهلت این نظرسنجی به پایان رسیده است.');
            }


            if (!$relation) {
                throw new \Exception('Relation Not found');
            }

            $pointDB = new Points(
                [
                    'employee_id'=>$employeeId,
                    'parameter_id'=>$parameterId,
                    'relation_id' =>$relation->id,
                    'point'=>$point
                ]
            );
            $pointDB->save();
            $form = Forms::find($relation->form_id);
            $questionCount = count($form->parameters);
            $answeredQuestions = Points::where('relation_id', $relation->id)->count();
            if ($questionCount == $answeredQuestions) {
                $relation->update(['evaluated' => true]);
            }
            return response()->json(['status'=>'success'], 200);
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
            $relationWeights += $relationPoint;
        }

        return $finalPoint;

    }
}



