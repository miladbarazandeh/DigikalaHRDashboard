<?php

namespace App\Http\Controllers;


use App\Forms;
use App\Parameters;
use App\Points;
use App\User;
use Illuminate\Http\Request;

class PointsController extends Controller
{
    public function getList(Request $request)
    {
        try {
            $user = $request->auth;
            $assignedUserIds = $user->getAttribute('assigned_user_ids');
            $employees = [];
//            return key($assignedUserIds[2]);
            foreach ($assignedUserIds as $assignedUserId) {
                $employee = User::where('id', key($assignedUserId) )->get()->first();
//                return $employee->name;
                $employees[] = [
                    'id' => $employee->id,
                    'name'=>$employee->name,
                    'email'=>$employee->email,
                    'status'=>current($assignedUserId)
                ];
            }
            return $employees;
            $assignedUsers = User::whereIn('id', $assignedUserIds)->get(['id', 'name', 'email']);
            return response()->json($assignedUsers);
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
            foreach ($form['parameters'] as $id=>$weight){
                $params[] = Parameters::find($id);
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
            foreach ($points as $parameterId=>$point)
            {
                $pointDB = new Points(
                    [
                        'employee_id'=>$employeeId,
                        'appraiser_id'=>$appraiserId,
                        'parameter_id'=>$parameterId,
                        'point'=>$point
                    ]
                );
                $pointDB->save();
            }
            $appraiser = User::where('id', '=', $appraiserId)->first();
            $assignedUserIds = $appraiser->assigned_user_ids;
//            return $assignedUserIds;
            foreach ($assignedUserIds as $assignedUserId) {
//                if (current($assignedUserId) == true) {
//                    continue;
//                }
                if (key($assignedUserId) == $employeeId) {
                    $users[] = [key($assignedUserId)=>true];
                } else {
                    $users[] = [key($assignedUserId)=>false];
                }
            }
            $appraiser->update(['assigned_user_ids' => $users]);
            return response()->json(['status'=>'success'], 200);
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 400);
        }
    }
}

