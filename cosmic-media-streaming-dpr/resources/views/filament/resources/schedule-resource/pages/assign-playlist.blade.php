<x-filament-panels::page>
    <livewire:schedule.assign-playlist :$record />
    <x-filament::button href="<?php echo env('URL_APP') . '/back-office/schedules'; ?>" tag="a" color="danger" style="width: 100px">
        Cancel
    </x-filament::button>
</x-filament-panels::page>
