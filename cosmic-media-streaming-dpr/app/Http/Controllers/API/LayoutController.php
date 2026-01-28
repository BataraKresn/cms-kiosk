<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LayoutController extends Controller
{
    public function create(Request $request, $id)
    {
        // Get current time in Asia/Jakarta timezone
        $Now = new \DateTime('now', new \DateTimeZone('Asia/Jakarta'));
        try {
            $data = DB::table('spots')->where('layout_id', $id)->first();
            if ($data) {
                DB::table('spots')->where('layout_id', $id)->delete();
            }

            // Retrieve spots from the request
            $spots = $request->input('spots');

            // Validate that spots is an array
            if (!is_array($spots)) {
                return response()->json([
                    'message' => 'Invalid input format. Expected an array of spots.',
                    'status' => 400,
                ], 400);

            // Ensure that each spot has the required fields
            foreach ($spots as $spot) {
                if (!isset($spot['layout_id'], $spot['x'], $spot['y'], $spot['w'], $spot['h'])) {
                    return response()->json([
                        'message' => 'Missing required fields in spot data.',
                        'status' => 400,
                    ], 400);
                }

                // Use updateOrInsert to handle both insert and update in one call
                DB::table('spots')->updateOrInsert(
                    [
                        'layout_id' => $spot['layout_id'], // Check by layout_id to find existing records
                        'x' => $spot['x'],
                        'y' => $spot['y'],
                    ],
                    [
                        'media_id' => 1,
                        'created_at' => $Now->format('Y-m-d H:i:s'),
                        'w' => $spot['w'],
                        'h' => $spot['h'],
                    ]
                );
            }

            // Return success response
            return response()->json([
                'message' => 'Saved successfully',
                'status' => 200
            ], 200);
        } else{
            foreach ($spots as $spot) {
                // Ensure that the necessary fields exist in each spot
                if (!isset($spot['layout_id'], $spot['x'], $spot['y'], $spot['w'], $spot['h'])) {
                    return response()->json([
                        'message' => 'Missing required spot data.',
                        'status' => 400,
                    ], 400);
                }

                DB::table('spots')->insert(
                    [
                        "layout_id" => $spot['layout_id'],
                        "media_id" => 1,
                        "created_at" => $Now->format('Y-m-d H:i:s'),
                        "x" => $spot['x'],
                        "y" => $spot['y'],
                        "w" => $spot['w'],
                        "h" => $spot['h'],
                    ]
                );
            }

            // Return success response
            return response()->json([
                'message' => 'Saved successfully',
                'status' => 200
            ], 200);
        }

        } catch (\Throwable $th) {
            // Log the error for better debugging
            Log::error('Error while saving layout: ' . $th->getMessage());

            // Return an error response
            return response()->json([
                'message' => 'Failed to save layout!',
                'error' => $th->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    public function delete($id)
    {
        try {
            DB::table('spots')->where('layout_id', $id)->delete();
            return response()->json([
                'message' => 'Deleted successfully',
                'status' => 200
            ], 200);            
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to delete layout!',
                'error' => $th->getMessage(),
                'status' => 500,
            ], 500);
        }
    }
}
