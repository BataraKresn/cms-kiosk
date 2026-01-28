<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeviceController extends Controller
{
    public function new_connection(Request $request){
    try {
        $curl = curl_init();

        // Convert array to JSON string
        $postData = json_encode([
            "serial_number" => $request->serial_number,
            "ip_device" => $request->ip_device,
            "port_device" => $request->port_device
        ]);
        $url = env('SERVICE_REMOTE_DEVICE').'/new_connection_device';
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            // Pass the JSON data
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $response_data = json_decode($response, true);
        
        $result = DB::table('devices')->insert([
            "serial_number" => $request->serial_number,
            "ip_device" => $request->ip_device,
            "port_device" => $request->port_device,
            "created_at" => Carbon::now(),
        ]);

        if ($result) {
            return response()->json([
                "message" => $response_data->message,
                "status" => 200,
                "serial_number" => $response_data->serial_number,
                "ip_device" => $response_data->ip_device,
                "port_device" => $response_data->port_device,
            ]);
        }
    } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $th->getMessage(),
                'status' => 500,
            ], 500);
        }
    }
}
