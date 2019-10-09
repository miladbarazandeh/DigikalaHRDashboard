<?php

namespace App\Http\Controllers;


use App\Categories;
use App\Cycle;
use App\Forms;
use App\Parameters;
use App\Values;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CreateFormController extends Controller
{

    public function newFormData()
    {
        try {
            $forms = Forms::all();
            $values = Values::all(['id', 'title']);
            $params = Parameters::all(['id', 'title']);
            $categories = Categories::all(['id', 'title']);

            return response()->json(
                [
                    'forms'=>$forms,
                    'values' => $values,
                    'categories' => $categories,
                    'parameter' => $params
                ]
            );
        } catch (\Exception $exception) {
            return response()->json(['message'=>$exception->getMessage()], 400);
        }
    }
    public function createNewCycle(Request $request)
    {
        try{
            $query = json_decode($request->getContent(), true);
            $title = $query['title'];
            $lastCycle = Cycle::orderBy('id', 'DESC')->first();
            if ($lastCycle) {
                $lastCycle->update(['active'=>false]);
            }
            $newCycle = new Cycle(
                [
                    'title'=>$title,
                    'active'=>true
                ]
            );
            $newCycle->save();
            return response()->json(['message'=>'دوره جدید ایجاد شد.']);
        } catch (\Exception $exception) {
            return response()->json(['message'=>$exception->getMessage()], 400);
        }

    }

    public function getAllCycles(Request $request)
    {
        $lastCycle = Cycle::All();

    }

    public function getAll()
    {
        try {
            $values = Values::all(['id', 'title']);
            $params = Parameters::all(['id', 'title']);
            $categories = Categories::all(['id', 'title']);

            return response()->json(
                [
                    'values' => $values,
                    'categories' => $categories,
                    'parameter' => $params
                ]
            );
        } catch (\Exception $exception) {
            return response()->json(['message'=>$exception->getMessage()], 400);
        }

    }
    public function createFormAction(Request $request)
    {
        try {
            $query = json_decode($request->getContent(), true);
            $name = $query['name'];
            $values = $query['values'];
            $categories = $query['categories'];
            $parameters = $query['parameters'];
            $form = new Forms(
                [
                    'name'=>$name,
                    'values'=>$values,
                    'categories'=>$categories,
                    'parameters'=>$parameters
                ]
            );
            $form->save();
            return response()->json(['message'=>'فرم ایجاد شد'], 200);
        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 400);
        }

    }

    public function setFormAction(Request $request)
    {
        try {
            $query = json_decode($request->getContent(), true);
            $formId = $query['formId'];

            $values = $query['values'];
            $categories = $query['categories'];
            $parameters = $query['parameters'];

            $form = Forms::find($formId);

            $form->update(['categories' => $categories]);
            $form->update(['values' => $values]);
            $form->update(['parameters' => $parameters]);

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

    public function insertNewValue(Request $request)
    {
        try {
            $query = json_decode($request->getContent(), true);
            $title = $query['title'];
            $value = new Values(
                [
                    'title'=>$title,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]
            );

            $value->save();
            return response()->json(['message'=>'new value added']);

        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 400);
        }
    }

    public function insertNewCategory(Request $request)
    {
        try {
            $query = json_decode($request->getContent(), true);
            $title = $query['title'];
            $category = new Categories(
                [
                    'title'=>$title,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]
            );

            $category->save();
            return response()->json(['message'=>'new category added']);

        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 400);
        }
    }

    public function insertNewParameter(Request $request)
    {
        try {
            $query = json_decode($request->getContent(), true);
            $title = $query['title'];
            $parameter = new Parameters(
                [
                    'title'=>$title,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]
            );

            $parameter->save();
            return response()->json(['message'=>'new parameter added']);

        } catch (\Exception $exception) {
            return response()->json($exception->getMessage(), 400);
        }
    }

    public function getCategories(Request $request)
    {
        try {
            $query = json_decode($request->getContent(), true);
            $valueId = $query['valueId'];
            $categories = Categories::where('value_id', $valueId)->get();
            return response()->json($categories);
        } catch (\Exception $exception) {
            return response()->json(['message'=>$exception->getMessage()], 400);
        }
    }

    public function getParameters(Request $request)
    {
        try {
            $query = json_decode($request->getContent(), true);
            $categoryId = $query['categoryId'];
            $parameters = Parameters::where('category_id', $categoryId)->get();
            return response()->json($parameters);
        } catch (\Exception $exception) {
            return response()->json(['message'=>$exception->getMessage()], 400);
        }
    }
}
