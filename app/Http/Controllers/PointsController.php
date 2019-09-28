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
                    $formId = $employee->form_id;
                    $form = Forms::where('id', $formId)->get();

                    return $form;
                    $parameters = $form->parameters;
                    $questions = [];
                    foreach ($parameters as $parameter) {
                        $param = Parameters::where('id', $parameter['id'])->get();
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


