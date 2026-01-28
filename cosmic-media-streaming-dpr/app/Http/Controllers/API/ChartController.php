<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChartController extends Controller
{
    public function device_status()
    {
        $data = DB::table('remotes')->get();

        return response()->json([
            "message" => "Loaded successfully",
            "status" => 200,
            "data" => $data
        ], 200);
    }
}
