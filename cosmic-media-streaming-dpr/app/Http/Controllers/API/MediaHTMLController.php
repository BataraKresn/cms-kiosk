<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MediaHTMLController extends Controller
{
    public function create(Request $request)
    {
        try {
            $name = $request->input('name');
            $html_file = $request->file('html_file');

            $filename = null;

            if ($html_file) {
                // Store directly in bucket root without folder prefix
                $filename = $html_file->hashName();
                Storage::disk('minio')->putFileAs('', $html_file, $filename);
            }

            DB::table('media_htmls')->insert([
                'name' => $name,
                'path' => $filename,
            ]);

            return response()->json([
                'message' => 'Media html created successfully!',
                'status' => 200,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to create media html!',
                'error' => $th->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    public function detail(Request $request, $id)
    {
        try {
            $data = DB::table('media_htmls')->where('id', $id)->first();

            $path = $data->path; // Path already includes folder
            $url = Storage::disk('minio')->url($path);

            $result = [
                'id' => $data->id,
                'name' => $data->name,
                'image' => $url
            ];

            return response()->json([
                'message' => 'Get data media html successfully!',
                'data' => $result,
                'status' => 200,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Get data media html failed!',
                'error' => $th->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    public function edit(Request $request, $id)
    {
        try {
            $name = $request->input('name');
            $html_file = $request->file('html_file');

            $filename = null;

            if ($html_file) {
                $path = Storage::disk('minio')->putFile('html', $html_file);
                $filename = basename($path);
            }

            DB::table('media_htmls')->where('id', $id)->update([
                'name' => $name,
                'path' => $filename,
            ]);

            return response()->json([
                'message' => 'Edit media html successfully!',
                'status' => 200,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Edit media html failed!',
                'error' => $th->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    public function delete($id)
    {
        try {
            $data = DB::table('media_htmls')->where('id', $id)->delete();

            return response()->json([
                'message' => 'Delete media html successfully!',
                'data' => $data,
                'status' => 200,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Delete media html failed!',
                'error' => $th->getMessage(),
                'status' => 500,
            ], 500);

        }
    }
}
