<?php

namespace App\Http\Controllers;

use App\Models\MediaVideo;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use Pion\Laravel\ChunkUpload\Exceptions\UploadMissingFileException;

class MediaController extends Controller
{
    public function createMediaVideo()
    {
        return view('media.createMediaVideo');
    }

    public function storeCreateMediaVideo(Request $request)
    {
        try {
            MediaVideo::create([
                'name' => $request->name,
                'path' => $request->path,
            ]);

            return response()->json([
                "message" => "Create media video successfully!",
                "status" => 200,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => "Internal server error",
                "status" => 500,
                "error" => $th->getMessage(),
            ]);
        }
    }

    public function editMediaVideo($id)
    {
        $result = MediaVideo::where('id', $id)->first();
        return view('media.editMediaVideo', ['data' => $result]);
    }

    public function downloadVideo($id)
    {
        try {
            $video = MediaVideo::findOrFail($id);
            
            // Check if file exists in MinIO
            if (Storage::disk('minio')->exists($video->path)) {
                // Generate public URL through nginx proxy
                $minioUrl = env('MINIO_URL');
                $url = $minioUrl . '/' . $video->path;
                
                return redirect($url);
            }
            
            // Fallback to local storage
            $filePath = storage_path("app/public/" . $video->path);
            if (file_exists($filePath)) {
                return response()->download($filePath);
            }

            return response()->json(['error' => 'File not found'], 404);
        } catch (\Exception $e) {
            Log::error('Download video error: ' . $e->getMessage());
            return response()->json(['error' => 'Error downloading file'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            // Create the file receiver
            $receiver = new FileReceiver("file", $request, HandlerFactory::classFromRequest($request));

            // Check if the upload is success
            if ($receiver->isUploaded() === false) {
                throw new UploadMissingFileException();
            }

            // Receive the file
            $save = $receiver->receive();

            // Check if the upload has finished
            if ($save->isFinished()) {
                try {
                    // Get the uploaded file
                    $file = $save->getFile();

                    // Validate file
                    $this->validateUploadedFile($file);

                    // Save to MinIO (primary) with fallback to local
                    return $this->saveFileToMinIO($file);
                } catch (\Exception $e) {
                    Log::error('Save error: ' . $e->getMessage());

                    return response()->json([
                        'error' => 'Error saving file: ' . $e->getMessage(),
                        'status' => false
                    ], 500);
                }
            }

            // We are in chunk mode, send the current progress
            $handler = $save->handler();
            return response()->json([
                "done" => $handler->getPercentageDone(),
                'status' => true
            ]);
        } catch (\Exception $e) {
            Log::error('Upload error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Upload error: ' . $e->getMessage(),
                'status' => false
            ], 500);
        }
    }

    /**
     * Validate uploaded file
     */
    protected function validateUploadedFile(UploadedFile $file): void
    {
        // Get file info
        $mimeType = $file->getMimeType();
        $fileSize = $file->getSize();
        $maxSize = 10 * 1024 * 1024 * 1024; // 10GB

        // Allowed MIME types
        $allowedMimes = [
            'video/mp4',
            'video/mpeg',
            'video/quicktime',
            'video/x-msvideo',
            'video/x-ms-wmv',
            'video/webm',
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/webp',
            'application/pdf',
            'text/html',
        ];

        // Check MIME type
        if (!in_array($mimeType, $allowedMimes)) {
            throw new \Exception("Invalid file type: {$mimeType}. Allowed types: video, image, pdf, html");
        }

        // Check file size
        if ($fileSize > $maxSize) {
            $sizeMB = round($fileSize / 1024 / 1024, 2);
            throw new \Exception("File size ({$sizeMB}MB) exceeds maximum allowed size of 10GB");
        }
    }

    /**
     * Save file to MinIO storage
     */
    protected function saveFileToMinIO(UploadedFile $file)
    {
        try {
            // Sanitize filename
            $originalName = $file->getClientOriginalName();
            $fileName = $this->sanitizeFilename($originalName);
            
            // Get file extension
            $extension = $file->getClientOriginalExtension();
            $baseName = pathinfo($fileName, PATHINFO_FILENAME);
            
            // Determine folder based on MIME type
            $mimeType = $file->getMimeType();
            $folder = $this->getFolderByMimeType($mimeType);
            
            // Generate unique filename if file exists
            $counter = 1;
            $finalFileName = $fileName;
            $path = "{$folder}/{$finalFileName}";
            
            while (Storage::disk('minio')->exists($path)) {
                $finalFileName = "{$baseName}_{$counter}.{$extension}";
                $path = "{$folder}/{$finalFileName}";
                $counter++;
            }

            // Upload to MinIO
            $uploaded = Storage::disk('minio')->putFileAs(
                $folder,
                $file,
                $finalFileName,
                'public'
            );

            if (!$uploaded) {
                throw new \Exception('Failed to upload file to MinIO');
            }

            Log::info("File uploaded to MinIO", [
                'path' => $path,
                'size' => $file->getSize(),
                'mime' => $mimeType
            ]);

            // Generate public URL
            $url = Storage::disk('minio')->url($path);

            return response()->json([
                'path' => $path,
                'name' => $finalFileName,
                'url' => $url,
                'mime_type' => str_replace('/', '-', $mimeType),
                'storage' => 'minio',
                'status' => true
            ]);
        } catch (\Exception $e) {
            Log::error('Save file to MinIO error: ' . $e->getMessage());
            
            // Fallback to local storage if MinIO fails
            try {
                return $this->saveFileToLocal($file);
            } catch (\Exception $fallbackError) {
                Log::error('Fallback to local storage also failed: ' . $fallbackError->getMessage());
                throw new \Exception('Failed to save file to both MinIO and local storage');
            }
        }
    }

    /**
     * Fallback: Save file to local storage
     */
    protected function saveFileToLocal(UploadedFile $file)
    {
        try {
            // Sanitize filename
            $fileName = $this->sanitizeFilename($file->getClientOriginalName());

            // Build the file path
            $finalPath = storage_path("app/public/");

            // Ensure directory exists
            if (!file_exists($finalPath)) {
                mkdir($finalPath, 0755, true);
            }

            // Check if file already exists
            $counter = 1;
            $extension = $file->getClientOriginalExtension();
            $baseName = pathinfo($fileName, PATHINFO_FILENAME);
            $finalFileName = $fileName;

            while (file_exists($finalPath . $finalFileName)) {
                $finalFileName = "{$baseName}_{$counter}.{$extension}";
                $counter++;
            }

            // Move the file
            $file->move($finalPath, $finalFileName);

            Log::warning("File saved to local storage (MinIO unavailable): {$finalFileName}");

            return response()->json([
                'path' => 'public/' . $finalFileName,
                'name' => $finalFileName,
                'url' => asset('storage/' . $finalFileName),
                'mime_type' => str_replace('/', '-', $file->getMimeType() ?: 'video/mp4'),
                'storage' => 'local',
                'status' => true
            ]);
        } catch (\Exception $e) {
            Log::error('Save file to local error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Determine folder based on MIME type
     */
    protected function getFolderByMimeType(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'video/')) {
            return 'videos';
        } elseif (str_starts_with($mimeType, 'image/')) {
            return 'images';
        } elseif ($mimeType === 'application/pdf') {
            return 'pdfs';
        } elseif ($mimeType === 'text/html') {
            return 'html';
        }
        
        return 'files';
    }

    protected function sanitizeFilename($filename)
    {
        // Remove special characters
        $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $filename);

        // Remove consecutive underscores
        $filename = preg_replace('/_+/', '_', $filename);

        // Remove leading/trailing underscores
        $filename = trim($filename, '_');

        return $filename;
    }

    public function storeEditMediaVideo(Request $request)
    {
        try {
            $mediaVideo = MediaVideo::find($request->id);

            if ($mediaVideo) {
                // Check if the path is being changed
                if ($mediaVideo->path !== $request->path) {
                    // Store the old path for potential deletion
                    $oldPath = $mediaVideo->path;

                    // Update the record with new information
                    $mediaVideo->update([
                        'name' => $request->name,
                        'path' => $request->path,
                    ]);

                    // Optionally: Delete the old file if it exists
                    // Storage::disk('minio')->delete($oldPath);
                } else {
                    // Just update the name
                    $mediaVideo->update([
                        'name' => $request->name,
                    ]);
                }

                return response()->json([
                    "message" => "Update media video successfully!",
                    "status" => 200,
                ]);
            }

            return response()->json([
                "message" => "Media video not found",
                "status" => 404,
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => "Internal server error",
                "status" => 500,
                "error" => $th->getMessage(),
            ]);
        }
    }

    public function serveVideo($path)
    {
        try {
            $minioUrl = env('MINIO_URL');
            
            // Try multiple path variations
            $pathsToTry = [
                'videos/' . $path,  // Most common: videos folder (plural)
                'video/' . $path,   // Alternative: video folder (singular)
                $path               // Direct path without folder prefix
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

            return response()->json(['error' => 'Video not found in any location: ' . $path], 404);
        } catch (\Exception $e) {
            Log::error('Serve video error: ' . $e->getMessage());
            return response()->json(['error' => 'Error serving video: ' . $e->getMessage()], 500);
        }
    }

    public function checkVideoName(Request $request)
    {
        try {
            $name = $request->query('name');
            $fileName = $request->query('fileName');

            $nameExists = MediaVideo::where('name', $name)->exists();
            $fileExists = MediaVideo::where('path', 'like', '%' . $fileName)->exists();

            return response()->json([
                'nameExists' => $nameExists,
                'fileExists' => $fileExists,
            ]);
        } catch (\Exception $e) {
            Log::error('Check video name error: ' . $e->getMessage());
            return response()->json(['error' => 'Error checking video name'], 500);
        }
    }
}
