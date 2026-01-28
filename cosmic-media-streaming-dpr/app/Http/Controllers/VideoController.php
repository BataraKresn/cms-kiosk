<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VideoController extends Controller
{
    public function streamVideo(Request $request, $filename)
    {
        $filePath = storage_path("app/public/{$filename}");

        if (!file_exists($filePath)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        $fileSize = filesize($filePath);
        $start = 0;
        $end = $fileSize - 1;
        $status = 200;
        $headers = [
            'Content-Type' => mime_content_type($filePath),
            'Accept-Ranges' => 'bytes',
            'Content-Length' => $fileSize,
            'Connection' => 'keep-alive', // Keep the connection alive
        ];

        // Handle Range Requests for Faster Loading
        if ($request->hasHeader('Range')) {
            preg_match('/bytes=(\d+)-(\d*)/', $request->header('Range'), $matches);
            $start = intval($matches[1]);
            $end = !empty($matches[2]) ? intval($matches[2]) : $end;

            if ($end >= $fileSize) {
                $end = $fileSize - 1;
            }

            $length = ($end - $start) + 1;
            $headers['Content-Length'] = $length;
            $headers['Content-Range'] = "bytes $start-$end/$fileSize";
            $status = 206; // Partial Content
        }

        // Increase chunk size to 8MB (even higher if needed)
        $chunkSize = 8 * 1024 * 1024; // 8MB chunks for even faster streaming

        // Enable output buffering control
        ob_end_flush(); // Disable internal PHP output buffering
        ob_implicit_flush(true); // Force implicit flushing of output

        // Optimized Response
        return response()->stream(function () use ($filePath, $start, $end, $chunkSize) {
            $file = fopen($filePath, 'rb');
            fseek($file, $start);

            while (!feof($file) && ($pos = ftell($file)) <= $end) {
                // Read file in larger chunks
                echo fread($file, min($chunkSize, $end - $pos + 1));
                flush();
                if (connection_aborted()) break; // Stop streaming if client disconnects
            }

            fclose($file);
        }, $status, $headers);
    }

}
