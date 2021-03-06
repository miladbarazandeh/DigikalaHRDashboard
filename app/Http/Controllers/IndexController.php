<?php

namespace App\Http\Controllers;


use App\Cards;
use App\Cycle;
use App\Relation;
use Illuminate\Http\Request;


class IndexController extends Controller
{
    public function getHome(Request $request)
    {
        try {
            $user = $request->auth;
            $relationsAsAppraiser = Relation::where('appraiser_id', $user->id)->get();
            $relationsAsAppraisal = Relation::where('appraisal_id', $user->id)->get();

            $cyclesAsAppraiserIds = [];
            $cyclesAsAppraisalIds = [];

            foreach ($relationsAsAppraisal as $item) {
                if(!in_array($item->cycle, $cyclesAsAppraisalIds)) {
                    $cyclesAsAppraisalIds[] = $item->cycle;
                }
            }

            foreach ($relationsAsAppraiser as $item) {
                if(!in_array($item->cycle, $cyclesAsAppraiserIds)) {
                    $cyclesAsAppraiserIds[] = $item->cycle;
                }
            }


            $appraiserCycles = Cycle::WhereIn('id', $cyclesAsAppraiserIds)->get(['id', 'title', 'active']);
//            $appraisalCycles = Cycle::WhereIn('id', $cyclesAsAppraisalIds)->get();
            $lastCycle = Cycle::orderBy('id', 'DESC')->first();

            $cards = Cards::where('active', 1)->get();
            $kpi = (new PointsController())->calculateKPI($user->id, $lastCycle->id, true);

            return response()->json(
                [
                    'cycles'=>$appraiserCycles,
                    'cards'=>$cards,
                    'kpi'=> $kpi
                ]
            );

        } catch (\Exception $exception) {
            return response()->json(['message'=>$exception->getMessage()], 400);
        }
    }
}