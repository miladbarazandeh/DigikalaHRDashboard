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
                        $questions[] = $param->title;
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
                    $formId = $cycleRelation->form_id;
                    $form = Forms::find($formId);

                    $parameters = $form['parameters'];
                    $questions = [];
                    foreach ($parameters as $parameter) {
                        $param = Parameters::find($parameter['id']);
                        $questions[] = $param->title;
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
            $employeeId = $query['employeeId'];
            $user = User::where('id', '=', $employeeId)->first();
            $form = Forms::where('id', '=', $user->form_id)->first();
            $params = [];
            foreach ($form['parameters'] as $parameter){
                $params[] = Parameters::find($parameter['id']);
            }
            return response()->json($params);
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 400);
        }
    }


public function setPointAction(Request $request)
    {
        try {
            $query = json_decode($request->getContent(), true);
            $employeeId = $query['employeeId'];
            $appraiserId = $request->auth->id;
            $points = $query['points'];

            $relation = Relation::where('appraisal_id', $employeeId)->where('appraiser_id', $appraiserId)->get();

            foreach ($points as $parameterId=>$point)
            {
                $pointDB = new Points(
                    [
                        'employee_id'=>$employeeId,
                        'parameter_id'=>$parameterId,
                        'relation_id' =>$relation->id,
                        'point'=>$point
                    ]
                );
                $pointDB->save();
            }

            $relation->update(['evaluated' => true]);
            return response()->json(['status'=>'success'], 200);
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 400);
        }
    }
}



