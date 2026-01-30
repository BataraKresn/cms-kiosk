<?php

namespace App\Http\Controllers\Api;

use App\Enums\DisplayTypeEnum;
use App\Enums\OperatingSystemEnum;
use App\Http\Controllers\Controller;
use App\Models\Display;
use App\Models\Screen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DisplayRegistrationController extends Controller
{
    /**
     * List displays with optional search
     *
     * GET /api/displays?search=DISPLAY&per_page=50
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $perPage = (int) $request->query('per_page', 50);
        $perPage = $perPage > 0 ? min($perPage, 200) : 50;

        $query = Display::query()
            ->select('id', 'name', 'screen_id', 'schedule_id', 'created_at')
            ->orderByDesc('created_at');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
        }

        $displays = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $displays->items(),
            'meta' => [
                'current_page' => $displays->currentPage(),
                'per_page' => $displays->perPage(),
                'total' => $displays->total(),
                'last_page' => $displays->lastPage(),
            ],
        ]);
    }

    /**
     * Register or update display by token
     * IMPORTANT: Display must be created manually by admin first!
     * This endpoint only updates existing displays, does not auto-create.
     *
     * POST /api/displays/register
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string|max:255',
            'name' => 'nullable|string|max:255',
            'screen_id' => 'nullable|exists:screens,id',
            'schedule_id' => 'nullable|exists:schedules,id',
            'display_type' => 'nullable|in:' . implode(',', array_column(DisplayTypeEnum::cases(), 'value')),
            'operating_system' => 'nullable|in:' . implode(',', array_column(OperatingSystemEnum::cases(), 'value')),
            'location_description' => 'nullable|string|max:255',
            'group' => 'nullable|string|max:255',
        ]);

        $token = $validated['token'];
        $display = Display::where('token', $token)->first();

        // Display not found - admin must create it manually first
        if (!$display) {
            return response()->json([
                'success' => false,
                'message' => 'Display not found. Please ask administrator to create this display first.',
                'error' => 'DISPLAY_NOT_FOUND',
                'token' => $token,
            ], 404);
        }

        // Display exists - update it
        $payload = [
            'name' => $validated['name'] ?? $display->name,
            'screen_id' => $validated['screen_id'] ?? $display->screen_id,
            'schedule_id' => $validated['schedule_id'] ?? $display->schedule_id,
            'display_type' => $validated['display_type'] ?? $display->display_type,
            'operating_system' => $validated['operating_system'] ?? $display->operating_system,
            'location_description' => $validated['location_description'] ?? $display->location_description,
            'group' => $validated['group'] ?? $display->group,
        ];

        $display->fill($payload);
        $display->save();

        Log::info('Display updated via API', [
            'display_id' => $display->id,
            'token' => $token,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Display updated successfully',
            'data' => [
                'display_id' => $display->id,
                'token' => $display->token,
                'name' => $display->name,
                'screen_id' => $display->screen_id,
                'schedule_id' => $display->schedule_id,
            ],
        ], 200);
    }
}
