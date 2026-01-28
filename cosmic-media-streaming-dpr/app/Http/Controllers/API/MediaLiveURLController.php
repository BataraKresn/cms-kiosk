<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MediaLiveURLController extends Controller
{
    public function create(Request $request)
    {
        try {
            $name = $request->input('name');
            $url = $request->input('url');
            $Now = new \DateTime('now', new \DateTimeZone('Asia/Jakarta'));

            DB::table('media_live_urls')->insert([
                'name' => $name,
                'url' => $url,
                'created_at' => $Now->format('Y-m-d H:i:s')
            ]);

            return response()->json([
                'message' => 'Media Live URL created successfully!',
                'status' => 200,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to create media Live URL!',
                'error' => $th->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    public function detail($id)
    {
        try {
            $data = DB::table('media_live_urls')->where('id', $id)->first();

            return response()->json([
                'message' => 'Get data media Live URL successfully!',
                'data' => $data,
                'status' => 200,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Get data media Live URL failed!',
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
            $Now = new \DateTime('now', new \DateTimeZone('Asia/Jakarta'));

            DB::table('media_live_urls')->where('id', $id)->update([
                'name' => $name,
                'url' => $url,
                'updated_at' => $Now->format('Y-m-d H:i:s')
            ]);

            return response()->json([
                'message' => 'Edit media Live URL successfully!',
                'status' => 200,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Edit media Live URL failed!',
                'error' => $th->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    public function delete($id)
    {
        try {
            $data = DB::table('media_live_urls')->where('id', $id)->delete();

            return response()->json([
                'message' => 'Delete media Live URL successfully!',
                'data' => $data,
                'status' => 200,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Delete media Live URL failed!',
                'error' => $th->getMessage(),
                'status' => 500,
            ], 500);

        }
    }
}
