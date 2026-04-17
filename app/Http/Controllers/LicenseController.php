<?php

namespace App\Http\Controllers;

use http\Env\Response;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    public function invalidLicense()
    {
        return Response()->json(['message' => 'License expired!']);
        return view('license.invalid'); // Adjust the view name as needed
    }
}
