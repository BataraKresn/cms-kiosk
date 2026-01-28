<div>
    <form wire:submit="edit">
        {{ $this->form }}

        <div class="pt-4 text-white">
            {{ $this->saveAction }}
        </div>
    </form>

    <x-filament-actions::modals />
</div>
