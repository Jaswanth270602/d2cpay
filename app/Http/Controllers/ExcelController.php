<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ExcelImport;


class ExcelController extends Controller
{

    function uploadExcel()
    {
        return view('uploadExcel');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx',
        ]);

        $file = $request->file('file');

        // Process the uploaded file using Laravel Excel
        $import = new ExcelImport;
        $excelData =  Excel::import($import, $file);
        foreach ($excelData as $data){
            foreach ($data as $value){
                echo $value->Name;
            }
        }
    exit();
        return redirect()->back()->with('success', 'File uploaded successfully');
    }
}
