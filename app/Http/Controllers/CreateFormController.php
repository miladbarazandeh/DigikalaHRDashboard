<?php

namespace App\Http\Controllers;


use App\Categories;
use App\Forms;
use App\Parameters;
use App\User;
use App\Values;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Request;

class CreateFormController extends Controller
{
    public function setValuesAction(Request $request)
    {
        try {
            $query = json_decode($request->getContent(), true);
            $formId = $query['formId'];
            $values = $query['values'];
            //return $values;
            $form = Forms::where('id', '=', $formId);
            $form->update(['values' => json_encode($values)]);
            return response()->json('Form updated', 200);
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 400);
        }

    }
}