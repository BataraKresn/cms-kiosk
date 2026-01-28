<?php

namespace App\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RemoteRepository
{
    public function getConnectedDevice(): Collection
    {
        return DB::table('remotes')
            ->where('status', 'Connected')
            ->whereNull('deleted_at')
            ->get(['name']);
    }

    public function getDisconnectDevice(): Collection
    {
        return DB::table('remotes')
            ->where('status', 'Disconnected')
            ->whereNull('deleted_at')
            ->get(['name']);
    }
}
