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
    public function createFormAction(Request $request)
    {
        try {
            $query = json_decode($request->getContent(), true);
            $name = $query['name'];
            $form = new Forms(
                ['name'=>$name]
            );
            $form->save();
            return response()->json($form->getAttribute('id'));
        } catch (\Exception $exception)
        {
            return response()->json($exception->getMessage(), 400);
        }
    }

    public function setForm(Request $request)
    {
        try {
            $query = json_decode($request->getContent(), true);
            $formId = $query['formId'];

            $values = $query['values'];
            $categories = $query['categories'];
            $parameters = $query['parameters'];

            $form = Forms::find($formId);

            $form->update(['categories' => json_encode($categories)]);
            $form->update(['values' => json_encode($values)]);
            $form->update(['parameters' => json_encode($parameters)]);

            return response()->json('Form Updated', 200);
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 400);
        }
    }

    public function setValuesAction(Request $request)
    {
        try {
            $query = json_decode($request->getContent(), true);
            $formId = $query['formId'];
            $values = $query['values'];
            $form = Forms::find($formId);
            $form->update(['values' => json_encode($values)]);
            return response()->json('Form updated', 200);
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 400);
        }

    }

    public function setCategoriesAction(Request $request)
    {
        try {
            $query = json_decode($request->getContent(), true);
            $formId = $query['formId'];
            $values = $query['categories'];
            $form = Forms::find($formId);
            $form->update(['categories' => json_encode($values)]);
            return response()->json('Form updated', 200);
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 400);
        }

    }

    public function setParametersAction(Request $request)
    {
        try {
            $query = json_decode($request->getContent(), true);
            $formId = $query['formId'];
            $values = $query['parameters'];
            $form = Forms::find($formId);
            $form->update(['parameters' => $values]);
            return response()->json('Form updated', 200);
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 400);
        }

    }
}

