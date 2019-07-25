<?php

namespace App\Http\Controllers;


use App\Categories;
use App\Forms;
use App\Parameters;
use App\Points;
use App\User;
use App\Values;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Request;

class PointsController extends Controller
{
    public function setPointAction(Request $request)
    {
        try {
            $query = json_decode($request->getContent(), true);
            $employeeId = $query['employee_id'];
            $appraiserId = $query['appraiser_id'];
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
