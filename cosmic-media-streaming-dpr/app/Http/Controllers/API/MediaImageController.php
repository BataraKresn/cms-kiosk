<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MediaImageController extends Controller
{
    public function create(Request $request)
    {
        try {
            $name = $request->input('name');
            $image_file = $request->file('path');

            $filename = null;

            if ($image_file) {
                // Store directly in bucket root without folder prefix
                $filename = $image_file->hashName();
                Storage::disk('minio')->putFileAs('', $image_file, $filename);
            }

            DB::table('media_images')->insert([
                'name' => $name,
                'path' => $filename,
            ]);

            return response()->json([
                'message' => 'Media image created successfully!',
                'status' => 200,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to create media image!',
                'error' => $th->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    public function detail(Request $request, $id)
    {
        try {
            $data = DB::table('media_images')->where('id', $id)->first();

            $path = $data->path; // Path already includes folder
            $url = Storage::disk('minio')->url($path);

            $result = [
                'id' => $data->id,
                'name' => $data->name,
                'image' => $url
            ];

            return response()->json([
                'message' => 'Get data media image successfully!',
                'data' => $result,
                'status' => 200,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Get data media image failed!',
                'error' => $th->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    public function edit(Request $request, $id)
    {
        try {
            $name = $request->input('name');
            $image_file = $request->file('image_file');

            $filename = null;

            if ($image_file) {
                $path = Storage::disk('minio')->putFile('image', $image_file);
                $filename = basename($path);
            }

            DB::table('media_images')->where('id', $id)->update([
                'name' => $name,
                'path' => $filename,
            ]);

            return response()->json([
                'message' => 'Edit media image successfully!',
                'status' => 200,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Edit media image failed!',
                'error' => $th->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    public function delete($id)
    {
        try {
            $data = DB::table('media_images')->where('id', $id)->delete();

            return response()->json([
                'message' => 'Delete media image successfully!',
                'data' => $data,
                'status' => 200,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Delete media image failed!',
                'error' => $th->getMessage(),
                'status' => 500,
            ], 500);

        }
    }
}
