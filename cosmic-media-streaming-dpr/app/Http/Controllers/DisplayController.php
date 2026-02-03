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
        
        // Extended cache to 10 minutes with tags for smart invalidation
        $cachedData = Cache::tags(['display', 'display_' . $token])->remember($cacheKey, 600, function() use ($token) {
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
            $forceRefresh = $request->input('force', false); // Optional: force full refresh

            $media = Media::where('mediable_type', 'App\Models\MediaVideo')
                ->where('mediable_id', $videoId)
                ->first();

            if (!$media) {
                return response()->json([
                    'message' => 'Media not found',
                    'status' => 404
                ], 404);
            }

            // Get affected displays
            $displays = Display::whereHas('schedule.schedule_playlists.playlists.playlist_layouts.layout.spots', function ($query) use ($media) {
                $query->where('media_id', $media->id);
            })
            ->whereNotNull('token')
            ->pluck('token');

            if ($displays->isEmpty()) {
                return response()->json([
                    'message' => 'No displays using this media',
                    'status' => 200,
                    'displays_count' => 0
                ]);
            }

            $urlAPI = env('URL_PDF');
            if (!$urlAPI) {
                return response()->json([
                    'message' => 'URL_PDF environment variable not configured',
                    'status' => 500
                ], 500);
            }

            // Invalidate cache for affected displays (SMART: only affected ones)
            $refreshedCount = 0;
            foreach ($displays as $token) {
                // Clear cache to force reload
                Cache::tags(['display', 'display_' . $token])->flush();
                
                // Only dispatch job if force=true (avoid unnecessary device reloads)
                if ($forceRefresh) {
                    RefreshDisplayJob::dispatch($token, $urlAPI);
                    $refreshedCount++;
                }
            }

            return response()->json([
                'message' => "Cache cleared for {$displays->count()} displays" . ($refreshedCount > 0 ? " (refresh queued: {$refreshedCount})" : ''),
                'status' => 200,
                'displays_count' => $displays->count(),
                'refreshed' => $refreshedCount
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
            $forceRefresh = $request->input('force', false);

            // Use indexed query with select() to reduce data transfer
            $media = Media::where('mediable_type', 'App\Models\MediaLiveUrl')
                ->where('mediable_id', $liveUrlId)
                ->select('id')
                ->first();

            if (!$media) {
                return response()->json([
                    'message' => 'Media not found',
                    'status' => 404
                ], 404);
            }

            // Use pluck to fetch only tokens (smaller dataset)
            $displays = Display::whereHas('schedule.schedule_playlists.playlists.playlist_layouts.layout.spots', function ($query) use ($media) {
                $query->where('media_id', $media->id);
            })
            ->whereNotNull('token')
            ->pluck('token');

            if ($displays->isEmpty()) {
                return response()->json([
                    'message' => 'No displays using this media',
                    'status' => 200,
                    'displays_count' => 0
                ]);
            }

            $urlAPI = env('URL_PDF');
            $refreshedCount = 0;

            // Clear cache and optionally dispatch refresh jobs
            foreach ($displays as $token) {
                Cache::tags(['display', 'display_' . $token])->flush();
                
                if ($forceRefresh && $urlAPI) {
                    RefreshDisplayJob::dispatch($token, $urlAPI);
                    $refreshedCount++;
                }
            }

            return response()->json([
                'message' => "Cache cleared for {$displays->count()} displays" . ($refreshedCount > 0 ? " (refresh queued: {$refreshedCount})" : ''),
                'status' => 200,
                'displays_count' => $displays->count(),
                'refreshed' => $refreshedCount
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
            $forceRefresh = $request->input('force', false);

            if (!$mediaHtmlId) {
                return response()->json([
                    'message' => 'Media HTML ID is required',
                    'status' => 400,
                ], 400);
            }

            // Use indexed query with select() to reduce data transfer
            $media = Media::where('mediable_type', 'App\Models\MediaHtml')
                ->where('mediable_id', $mediaHtmlId)
                ->select('id')
                ->first();

            if (!$media) {
                return response()->json([
                    'message' => 'Media not found',
                    'status' => 404,
                ], 404);
            }

            // Use pluck to fetch only tokens (smaller dataset)
            $displays = Display::whereHas('schedule.schedule_playlists.playlists.playlist_layouts.layout.spots', function ($query) use ($media) {
                $query->where('media_id', $media->id);
            })
            ->whereNotNull('token')
            ->pluck('token');

            if ($displays->isEmpty()) {
                return response()->json([
                    'message' => 'No displays using this media',
                    'status' => 200,
                    'displays_count' => 0
                ]);
            }

            $urlAPI = env('URL_PDF');
            $refreshedCount = 0;

            // Clear cache and optionally dispatch refresh jobs
            foreach ($displays as $token) {
                Cache::tags(['display', 'display_' . $token])->flush();
                
                if ($forceRefresh && $urlAPI) {
                    RefreshDisplayJob::dispatch($token, $urlAPI);
                    $refreshedCount++;
                }
            }

            return response()->json([
                'message' => "Cache cleared for {$displays->count()} displays" . ($refreshedCount > 0 ? " (refresh queued: {$refreshedCount})" : ''),
                'status' => 200,
                'displays_count' => $displays->count(),
                'refreshed' => $refreshedCount
            ]);
        } catch (\Exception $e) {
            Log::error('Error refreshing displays: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to refresh displays',
                'error' => $e->getMessage(),
                'status' => 500,
            ], 500);
        }
    }
}
