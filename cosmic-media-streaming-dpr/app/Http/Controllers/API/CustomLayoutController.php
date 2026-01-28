<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomLayoutController extends Controller
{
    public function save_change(Request $request)
    {

            $data = DB::table('custom_layout')->where('id', $request->id)->update([
                "data_layout" => $request->input('data_layout'),
                "data_html" => $request->input('data_html'),
                "data_css" => $request->input('data_css')
            ]);
    
            return response()->json([
                "message" => "Saved successfully",
                "status" => 200
            ], 200);
        
    }

    public function load_data($id)
    {
        $data = DB::table('custom_layout')->where('id', $id)->select('data_html', 'data_css', 'data_layout')->first();

        return response()->json([
            "message" => "Loaded successfully",
            "status" => 200,
            "data" => $data
        ], 200);
    }
}
