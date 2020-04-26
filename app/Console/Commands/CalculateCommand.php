<?php

namespace App\Console\Commands;

use App\Forms;
use App\Points;
use App\Relation;
use App\Target;
use App\User;
use Illuminate\Console\Command;

class CalculateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calc:kpi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'KPI calculation';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $users = User::all();
        $file = fopen("result.csv","w");
        foreach ($users as $user) {
            $softSkills = $this->calculateKPI($user->id, 2, true, 'softSkills');
            $hardSkills = $this->calculateKPI($user->id, 2, true, 'hardSkills');
            $kpiWithTarget = $this->calculateKPI($user->id, 2, true);
            $this->info(implode(', ', [$user->name, $softSkills, $hardSkills, $kpiWithTarget]));
            fputcsv($file, [$user->name, $softSkills, $hardSkills, $kpiWithTarget]);
//            $list = $this->getAppraisalList($user);
//            $team = [];
//            if ($list) {
//                $this->info($user->name);
//                foreach ($list as $item) {
////                    $this->info($item['question']);
//                    foreach ($item['employee'] as $employee) {
////                        $this->info($employee['point']);
//                        $team[] = $employee['name'];
//                    }
//                    break;
//
//                }
//                fputcsv($file, array_merge([' '], $team));
//
//                foreach ($list as $item) {
////                    $this->info($item['question']);
//                    $points = [];
//                    foreach ($item['employee'] as $employee) {
////                        $this->info($employee['point']);
//                        $points[] = $employee['point'];
//                    }
//                    fputcsv($file, array_merge([$item['question']], $points));
//                }
//            }

        }
    }

    public function calculateKPI($userId, $cycleId, $usingTarget=false, $type = null)
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
                if ($type == 'softSkills' && !in_array($parameter['id'], [132, 133, 134, 135])) {
                    continue;
                }
                if ($type == 'hardSkills' && in_array($parameter['id'], [132, 133, 134, 135])) {
                    continue;
                }

                $pointsEntity = Points::where('relation_id', $relation->id)->where('parameter_id', $parameter['id'])->first();
                $target = 0;
                if (true) {
                    $targetEntity = Target::where('cycle', $cycleId)->where('user_id', $userId)->where('parameter_id', $parameter['id'])->first();
                    if ($targetEntity) {
                        $target = $targetEntity->target;
                    }
                }

                if ($target && $usingTarget) {
                    $parameterPoint = ($pointsEntity->point * $parameter['weight'])*10/$target;
                } else {
                    $parameterPoint = $pointsEntity->point * $parameter['weight'];

                }
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
                        $valuePoint = $type ? $categoryPoint : $categoryPoint * $value['weight'];
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