<x-filament-widgets::widget>
    <div class="flex justify-end">
        <a href="{{ env('URL_PDF') }}/generate-pdf?url={{ env('URL_APP') }}/view-pdf"
            class="bg-primary-500 text-white font-semibold py-2 px-4 rounded inline-block">
            Export PDF
        </a>
    </div>
</x-filament-widgets::widget>
