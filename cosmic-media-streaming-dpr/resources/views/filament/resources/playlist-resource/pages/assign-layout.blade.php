<x-filament-panels::page>
    <livewire:playlist.assign-layout :$record />
    <x-filament::button href="<?php echo env('URL_APP') . '/back-office/playlists'; ?>" tag="a" color="danger" style="width: 100px">
        Cancel
    </x-filament::button>
</x-filament-panels::page>
