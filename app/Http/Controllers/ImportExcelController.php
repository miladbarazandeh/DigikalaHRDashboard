<?php

namespace App\Http\Controllers;


use Symfony\Component\HttpFoundation\Request;

class ImportExcelController extends Controller
{
    public function importUsersAction(Request $request)
    {
        $file_n = storage_path('data.csv');
        $csv = array_map("str_getcsv", file($file_n));
        $keys = array_shift($csv);
        foreach ($csv as $i=>$row) {
            $csv[$i] = array_combine($keys, $row);
        }
        return $csv;
    }

}