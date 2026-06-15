<?php

namespace App\Http\Controllers;

use App\Models\Apiresponse;
use Illuminate\Http\Request;

class GenericCallbackController extends Controller
{
    public function receive(Request $request)
    {
        $payload = [
            'method' => $request->method(),
            'query' => $request->query(),
            'body' => $request->all(),
            'raw' => $request->getContent(),
            'headers' => $request->headers->all(),
        ];

        Apiresponse::insertGetId([
            'message' => json_encode($payload),
            'response_type' => 'generic_callback',
            'request_message' => $request->fullUrl(),
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Callback received',
        ], 200);
    }
}
