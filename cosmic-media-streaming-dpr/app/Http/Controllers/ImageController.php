<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImageController extends Controller
{
    public function serveImage(Request $request, $filename)
    {
        $filename = urldecode($filename);
        $filePath = storage_path("app/public/{$filename}");

        if (!file_exists($filePath)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        // Fastest: Use Nginx X-Accel-Redirect if in production
        if (env('APP_ENV') === 'production') {
            return response()->file($filePath, [
                'X-Accel-Redirect' => '/protected/images/' . urlencode($filename)
            ]);
        }

        $fileSize = filesize($filePath);
        $range = $request->header('Range');

        if ($range) {
            list($unit, $ranges) = explode('=', $range, 2);
            $ranges = explode(',', $ranges);
            $rangeParts = explode('-', $ranges[0]);
            $start = (int)$rangeParts[0];
            $end = isset($rangeParts[1]) ? (int)$rangeParts[1] : $fileSize - 1;
            $end = min($end, $fileSize - 1);
            $length = $end - $start + 1;
            $chunkSize = 131072; // 128 KB

            $file = fopen($filePath, 'rb');
            fseek($file, $start);

            $response = new StreamedResponse(function () use ($file, $length, $chunkSize) {
                $remaining = $length;
                while ($remaining > 0) {
                    echo fread($file, min($chunkSize, $remaining));
                    flush();
                    $remaining -= $chunkSize;
                }
            });

            $response->headers->set('Content-Type', mime_content_type($filePath));
            $response->headers->set('Content-Length', $length);
            $response->headers->set('Content-Range', "bytes {$start}-{$end}/{$fileSize}");
            $response->headers->set('Accept-Ranges', 'bytes');
            $response->headers->set('Cache-Control', 'public, max-age=31536000');
            $response->headers->set('Expires', gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
            $response->setStatusCode(206);

            fclose($file);
            return $response;
        }

        // For non-range requests, use readfile() for efficiency
        return response()->stream(function () use ($filePath) {
            readfile($filePath);
        }, 200, [
            'Content-Type' => mime_content_type($filePath),
            'Content-Length' => $fileSize,
            'Cache-Control' => 'public, max-age=31536000',
            'Expires' => gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT',
            'Accept-Ranges' => 'bytes'
        ]);
    }

    public function serveImageFromMinio($path)
    {
        try {
            $minioUrl = env('MINIO_URL');
            
            // Try multiple path variations for images
            $pathsToTry = [
                'images/' . $path,  // Preferred: images folder
                $path               // Fallback: direct path in root /cms/
            ];
            
            foreach ($pathsToTry as $testPath) {
                if (Storage::disk('minio')->exists($testPath)) {
                    $url = $minioUrl . '/' . $testPath;
                    return redirect($url);
                }
            }

            // Fallback to local storage
            $filePath = storage_path("app/public/" . $path);
            if (file_exists($filePath)) {
                return response()->file($filePath);
            }

            return response()->json(['error' => 'Image not found in any location: ' . $path], 404);
        } catch (\Exception $e) {
            \Log::error('Serve image error: ' . $e->getMessage());
            return response()->json(['error' => 'Error serving image: ' . $e->getMessage()], 500);
        }
    }
}
