<?php

namespace App\Http\Controllers;


use App\Forms;
use App\Parameters;
use App\Points;
use App\User;
use Symfony\Component\HttpFoundation\Request;

class PointsController extends Controller
{
    public function getFormAction(Request $request)
    {
        try {
            $query = json_decode($request->getContent(), true);
            $employeeId = $query['employeeId'];
            $user = User::where('id', '=', $employeeId)->first();
            $form = Forms::where('id', '=', $user->form_id)->first();
//            $form = json_decode($form);
//            return $form['parameters'];
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
            $appraiserId = $query['appraiserId'];
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
            return response()->json('OK', 200);
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 400);
        }
    }

}