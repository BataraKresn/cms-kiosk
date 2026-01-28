<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MediaVideoController extends Controller
{
    public function create(Request $request)
    {
        try {
            $name = $request->input('name');
            $video_file = $request->file('video_file');

            $filename = null;

            if ($video_file) {
                $path = Storage::disk('minio')->putFile('video', $video_file);
                $filename = basename($path);
            }

            DB::table('media_videos')->insert([
                'name' => $name,
                'path' => $filename,
            ]);

            return response()->json([
                'message' => 'Media video created successfully!',
                'status' => 200,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to create media video!',
                'error' => $th->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    public function detail(Request $request, $id)
    {
        try {
            $data = DB::table('media_videos')->where('id', $id)->first();

            $path = 'video/' . $data->path;
            $url = Storage::disk('minio')->temporaryUrl($path, now()->addMinutes(1));

            $result = [
                'id' => $data->id,
                'name' => $data->name,
                'image' => $url
            ];

            return response()->json([
                'message' => 'Get data media video successfully!',
                'data' => $result,
                'status' => 200,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Get data media video failed!',
                'error' => $th->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    public function edit(Request $request, $id)
    {
        try {
            $name = $request->input('name');
            $video_file = $request->file('video_file');

            $filename = null;

            if ($video_file) {
                $path = Storage::disk('minio')->putFile('video', $video_file);
                $filename = basename($path);
            }

            DB::table('media_videos')->where('id', $id)->update([
                'name' => $name,
                'path' => $filename,
            ]);

            return response()->json([
                'message' => 'Edit media video successfully!',
                'status' => 200,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Edit media video failed!',
                'error' => $th->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    public function delete($id)
    {
        try {
            $data = DB::table('media_videos')->where('id', $id)->delete();

            return response()->json([
                'message' => 'Delete media video successfully!',
                'data' => $data,
                'status' => 200,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Delete media video failed!',
                'error' => $th->getMessage(),
                'status' => 500,
            ], 500);

        }
    }
}
