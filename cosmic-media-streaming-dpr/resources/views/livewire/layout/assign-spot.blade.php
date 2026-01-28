<div style="flex: 2;">
    <form wire:submit="edit">
        {{ $this->form }}
    </form>

    <x-filament-actions::modals />
</div>
