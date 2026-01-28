<?php

namespace App\Http\Controllers;

use App\Enums\ScreenModeEnum;
use App\Models\Layout;
use App\Models\Schedule;
use App\Services\LayoutService;

class LayoutPreviewController extends Controller
{
    public function show($id)
    {
        $layout = Layout::findOrFail($id);
        $options = LayoutService::build($layout, true);
        $screen_aspect = $layout->screen->mode === ScreenModeEnum::PORTRAIT->value ? $layout->screen->height : $layout->screen->width;

        return view('preview', compact('layout', 'options', 'screen_aspect'));
    }

    public function showSchedule($id)
    {
        $schedule = Schedule::whereId($id)
            ->with('schedule_playlists.playlists.playlist_layouts')
            ->firstOrFail();

        $current_date = new \DateTime();
        $current_day = (int) $current_date->format('w'); // 0 for Sunday, 1 for Monday, etc.
        $current_time = $current_date->format('H:i:s');
        $display['screen']['mode'] = ScreenModeEnum::PORTRAIT->value;

        $data['data']['id'] = $schedule->id;
        $data['data']['name'] = $schedule->name;

        foreach ($schedule->schedule_playlists as $i => $row) {
            $data['data']['playlists'][$i] = $row->only('start_day', 'end_day', 'schedule_id', 'playlist_id');

            if ($current_day >= $data['data']['playlists'][$i]['start_day'] && $current_day <= $data['data']['playlists'][$i]['end_day']) {

                foreach ($row->playlists as $playlist) {
                    foreach ($playlist->playlist_layouts as $play) {
                        $data['data']['playlists'][$i]['layouts'][] = [
                            'id' => $play->id,
                            'name' => $play->layout->name,
                            'start_time' => $play->start_time,
                            'end_time' => $play->end_time,
                            'content' => json_encode(LayoutService::build($play->layout, true)),
                        ];

                        /*
                         * For set mode
                         */
                        if ($current_time >= $play->start_time && $current_time <= $play->end_time) {
                            $display['screen']['mode'] = $play->layout->screen->mode;
                        }
                    }
                }
            }
        }

        return view('schedule', compact('data', 'display'));
    }
}
