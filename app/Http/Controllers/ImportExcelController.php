<?php

namespace App\Http\Controllers;


use App\Categories;
use App\Parameters;
use App\User;
use App\Values;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
//use Symfony\Component\HttpFoundation\Request;

class ImportExcelController extends Controller
{
    public function getUserTemplatePath(Request $request)
    {
        return response()->json(['url'=>url('csv/data.csv')]);
    }
    public function importUsersAction(Request $request)
    {
        try {
            $file_n = storage_path('data.csv');
            $csv = array_map("str_getcsv", file($file_n));
            $keys = array_shift($csv);
            foreach ($csv as $i=>$row) {
                $csv[$i] = array_combine($keys, $row);
                $csv[$i]['created_at'] = Carbon::now();
                $csv[$i]['updated_at'] = Carbon::now();
            }

            foreach ($csv as &$item) {
                $password = $item['password'];
                $item['password']= Hash::make($password);
            }

            User::insert($csv);
        } catch (\Exception $exception) {
            return response()->json(['error'=>$exception->getMessage()], 400);
        }
        return $csv;
    }

    public function importParametersAction(Request $request)
    {
        try {
            $file_n = storage_path('parameters.csv');
            $csv = array_map("str_getcsv", file($file_n));
            $keys = array_shift($csv);
            foreach ($csv as $i=>$row) {
                $csv[$i] = array_combine($keys, $row);
                $csv[$i]['created_at'] = Carbon::now();
                $csv[$i]['updated_at'] = Carbon::now();
            }

            Parameters::insert($csv);
        } catch (\Exception $exception) {
            return response()->json(['error'=>$exception->getMessage()], 400);
        }
        return $csv;

    }

    public function importCategoriesAction(Request $request)
    {
        try {
            $file_n = storage_path('categories.csv');
            $csv = array_map("str_getcsv", file($file_n));
            $keys = array_shift($csv);
            foreach ($csv as $i=>$row) {
                $csv[$i] = array_combine($keys, $row);
                $csv[$i]['created_at'] = Carbon::now();
                $csv[$i]['updated_at'] = Carbon::now();
            }

            Categories::insert($csv);
        } catch (\Exception $exception) {
            return response()->json(['error'=>$exception->getMessage()], 400);
        }
        return $csv;

    }

    public function importValuesAction(Request $request)
    {
        try {
            $file_n = storage_path('values.csv');
            $csv = array_map("str_getcsv", file($file_n));
            $keys = array_shift($csv);
            foreach ($csv as $i=>$row) {
                $csv[$i] = array_combine($keys, $row);
                $csv[$i]['created_at'] = Carbon::now();
                $csv[$i]['updated_at'] = Carbon::now();
            }

            Values::insert($csv);
        } catch (\Exception $exception) {
            return response()->json(['error'=>$exception->getMessage()], 400);
        }
        return $csv;

    }

}