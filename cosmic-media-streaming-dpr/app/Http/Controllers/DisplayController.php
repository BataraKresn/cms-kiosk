<?php

namespace App\Http\Controllers;

use App\Enums\ScreenModeEnum;
use App\Jobs\RefreshDisplayJob;
use App\Models\Display;
use App\Models\Media;
use App\Services\LayoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class DisplayController extends Controller
{
    public function show($token)
    {
        // Cache display data for 5 minutes to reduce database load
        $cacheKey = 'display_content_' . $token;
        
        $cachedData = Cache::remember($cacheKey, 300, function() use ($token) {
            // Optimize query with eager loading to prevent N+1 queries
            $display = Display::select('id', 'token', 'schedule_id', 'screen_id')
                ->whereToken($token)
                ->with([
                    'schedule:id,name',
                    'schedule.schedule_playlists:schedule_id,start_day,end_day,playlist_id',
                    'schedule.schedule_playlists.playlists.playlist_layouts:id,playlist_id,layout_id,start_time,end_time',
                    'schedule.schedule_playlists.playlists.playlist_layouts.layout:id,name,screen_id',
                    'schedule.schedule_playlists.playlists.playlist_layouts.layout.screen:id,mode,width,height,column,row',
                    'schedule.schedule_playlists.playlists.playlist_layouts.layout.spots:layout_id,id,media_id,x,y,w,h',
                    'screen:id,mode,width,height,column,row',
                ])
                ->firstOrFail();

            $data['data']['id'] = $display->schedule->id;
            $data['data']['name'] = $display->schedule->name;

            foreach ($display->schedule->schedule_playlists as $i => $row) {
                $data['data']['playlists'][$i] = $row->only('start_day', 'end_day', 'schedule_id', 'playlist_id');
                foreach ($row->playlists as $playlist) {
                    foreach ($playlist->playlist_layouts as $play) {
                        $data['data']['playlists'][$i]['layouts'][] = [
                            'id' => $play->id,
                            'name' => $play->layout->name,
                            'start_time' => $play->start_time,
                            'end_time' => $play->end_time,
                            'content' => json_encode(LayoutService::build($play->layout, true)),
                        ];
                    }
                }
            }

            $screen_aspect = $display->screen->mode === ScreenModeEnum::PORTRAIT->value ? $display->screen->height : $display->screen->width;

            return [
                'data' => $data,
                'display' => $display,
                'screen_aspect' => $screen_aspect,
                'token' => $display->token
            ];
        });

        return view('display', $cachedData);
    }

    public function refreshDisplaysByVideo(Request $request)
    {
        try {
            $videoId = $request->input('video_id');

            $media = Media::where('mediable_type', 'App\Models\MediaVideo')
                ->where('mediable_id', $videoId)
                ->first();

            if (!$media) {
                return response()->json([
                    'message' => 'Media not found',
                    'status' => 404
                ], 404);
            }

            // Optimize query to get only tokens
            $displays = Display::whereHas('schedule.schedule_playlists.playlists.playlist_layouts.layout.spots', function ($query) use ($media) {
                $query->where('media_id', $media->id);
            })
            ->whereNotNull('token')
            ->pluck('token');

            $urlAPI = env('URL_PDF');
            
            if (!$urlAPI) {
                return response()->json([
                    'message' => 'URL_PDF environment variable not configured',
                    'status' => 500
                ], 500);
            }

            // Dispatch jobs to queue for async processing
            foreach ($displays as $token) {
                RefreshDisplayJob::dispatch($token, $urlAPI);
            }

            return response()->json([
                'message' => "Queued refresh for {$displays->count()} displays",
                'status' => 200,
                'displays_count' => $displays->count()
            ]);
        } catch (\Throwable $th) {
            Log::error('Error refreshing displays: ' . $th->getMessage());
            return response()->json([
                'message' => 'Error refreshing displays',
                'status' => 500,
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function refreshDisplaysByLiveUrl(Request $request)
    {
        try {
            $liveUrlId = $request->input('live_url_id');

            // Find the media record for this live URL
            $media = Media::where('mediable_type', 'App\Models\MediaLiveUrl')
                ->where('mediable_id', $liveUrlId)
                ->first();

            if (!$media) {
                return response()->json([
                    'message' => 'Media not found',
                    'status' => 404
                ], 404);
            }

            // Optimize query to get only tokens
            $displays = Display::whereHas('schedule.schedule_playlists.playlists.playlist_layouts.layout.spots', function ($query) use ($media) {
                $query->where('media_id', $media->id);
            })
            ->whereNotNull('token')
            ->pluck('token');

            $urlAPI = env('URL_PDF');
            
            if (!$urlAPI) {
                return response()->json([
                    'message' => 'URL_PDF environment variable not configured',
                    'status' => 500
                ], 500);
            }

            // Dispatch jobs to queue for async processing
            foreach ($displays as $token) {
                RefreshDisplayJob::dispatch($token, $urlAPI);
            }

            return response()->json([
                'message' => "Queued refresh for {$displays->count()} displays",
                'status' => 200,
                'displays_count' => $displays->count()
            ]);
        } catch (\Throwable $th) {
            Log::error('Error refreshing displays: ' . $th->getMessage());
            return response()->json([
                'message' => 'Error refreshing displays',
                'status' => 500,
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function refreshDisplaysByHtml(Request $request)
    {
        try {
            $mediaHtmlId = $request->input('media_html_id');

            if (!$mediaHtmlId) {
                return response()->json([
                    'message' => 'Media HTML ID is required',
                    'status' => 400,
                ], 400);
            }

            $media = Media::where('mediable_type', 'App\Models\MediaHtml')
                ->where('mediable_id', $mediaHtmlId)
                ->first();

            if (!$media) {
                return response()->json([
                    'message' => 'Media not found',
                    'status' => 404,
                ], 404);
            }

            $displays = Display::whereHas('schedule.schedule_playlists.playlists.playlist_layouts.layout.spots', function ($query) use ($media) {
                $query->where('media_id', $media->id);
            })->whereNotNull('token')->get();

            $urlAPI = env('URL_PDF');
            $successCount = 0;
            $failCount = 0;

            foreach ($displays as $display) {
                if ($display->token) {
                    $url = $urlAPI . '/send_refresh_device?token=' . $display->token;
                    $response = Http::get($url);

                    if ($response->successful()) {
                        $successCount++;
                    } else {
                        $failCount++;
                    }
                }
            }

            return response()->json([
                'message' => "Refreshed $successCount displays successfully. Failed: $failCount",
                'status' => 200,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to refresh displays',
                'error' => $e->getMessage(),
                'status' => 500,
            ], 500);
        }
    }
}
