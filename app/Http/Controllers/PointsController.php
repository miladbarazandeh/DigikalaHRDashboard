<?php

namespace App\Http\Controllers;


use App\Forms;
use App\Parameters;
use App\Points;
use App\Relation;
use App\User;
use Illuminate\Http\Request;

class PointsController extends Controller
{
    public function getList(Request $request)
    {
        try {
            $user = $request->auth;
            $assignedUserIds = Relation::where('appraiser_id', $user->id)->get('appraisal_id');
            $assignedUsers = User::whereIn('id', $assignedUserIds)->get(['id', 'name', 'email', 'status']);
            $employees = [];
//            return key($assignedUserIds[2]);
            foreach ($assignedUsers as $assignedUser) {
//                return $employee->name;
                $employees[] = [
                    'id' => $assignedUser->id,
                    'name'=>$assignedUser->name,
                    'email'=>$assignedUser->email,
                    'status'=> $assignedUser->evaluated
                ];
            }

            return response()->json($employees);
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