<?php

namespace App\Http\Controllers;

use App\Models\Remote;
use Illuminate\Http\Request;

/**
 * Remote Control Viewer Controller
 * 
 * Handles the web-based remote control viewer interface for admin users
 * to remotely view and control Android devices.
 * 
 * Flow:
 * 1. Admin clicks "Remote Control" button in Filament RemoteResource
 * 2. Controller validates device is online and remote control enabled
 * 3. Generates WebSocket connection details
 * 4. Renders viewer page with WebSocket client
 * 5. Viewer connects to relay server as "viewer" role
 * 6. Receives video frames from device
 * 7. Sends touch/input commands to device
 * 
 * @author Cosmic Development Team
 * @version 1.0.0
 */
class RemoteControlViewController extends Controller
{
    /**
     * Show remote control viewer page
     * 
     * @param int $record Remote device ID
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show(Request $request, $record)
    {
        // Find remote device
        $remote = Remote::find($record);
        
        if (!$remote) {
            return redirect()
                ->route('filament.admin.resources.remotes.index')
                ->with('error', 'Device not found');
        }
        
        // Validate remote control is enabled
        if (!$remote->remote_control_enabled) {
            return redirect()
                ->route('filament.admin.resources.remotes.index')
                ->with('error', 'Remote control is disabled for this device');
        }
        
        // Check device is online
        if ($remote->status !== 'Connected') {
            return redirect()
                ->route('filament.admin.resources.remotes.index')
                ->with('error', 'Device is offline (status: ' . $remote->status . ')');
        }
        
        // Get WebSocket configuration
        $wsUrl = config('remotecontrol.relay_ws_url');
        $deviceId = $remote->device_identifier;
        $deviceToken = $remote->token;
        $deviceName = $remote->device_name;
        
        // Get authenticated user info for logging
        $userId = auth()->id();
        $userName = auth()->user()->name ?? 'Unknown';
        
        return view('remote-control-viewer', [
            'remote' => $remote,
            'wsUrl' => $wsUrl,
            'deviceId' => $deviceId,
            'deviceToken' => $deviceToken,
            'deviceName' => $deviceName,
            'userId' => $userId,
            'userName' => $userName,
        ]);
    }
}
