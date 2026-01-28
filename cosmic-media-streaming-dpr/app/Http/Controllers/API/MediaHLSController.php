<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MediaHLSController extends Controller
{
    public function create(Request $request)
    {
        try {
            $name = $request->input('name');
            $url = $request->input('url');

            DB::table('media_hls')->insert([
                'name' => $name,
                'url' => $url,
            ]);

            return response()->json([
                'message' => 'Media HLS created successfully!',
                'status' => 200,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to create media HLS!',
                'error' => $th->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    public function detail($id)
    {
        try {
            $data = DB::table('media_hls')->where('id', $id)->first();

            return response()->json([
                'message' => 'Get data media HLS successfully!',
                'data' => $data,
                'status' => 200,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Get data media HLS failed!',
                'error' => $th->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    public function edit(Request $request, $id)
    {
        try {
            $name = $request->input('name');
            $url = $request->input('url');

            DB::table('media_hls')->where('id', $id)->update([
                'name' => $name,
                'url' => $url
            ]);

            return response()->json([
                'message' => 'Edit media HLS successfully!',
                'status' => 200,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Edit media HLS failed!',
                'error' => $th->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    public function delete($id)
    {
        try {
            $data = DB::table('media_hls')->where('id', $id)->delete();

            return response()->json([
                'message' => 'Delete media HLS successfully!',
                'data' => $data,
                'status' => 200,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Delete media HLS failed!',
                'error' => $th->getMessage(),
                'status' => 500,
            ], 500);

        }
    }
}
