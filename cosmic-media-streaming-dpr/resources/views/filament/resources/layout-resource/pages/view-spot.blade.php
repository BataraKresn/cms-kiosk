<x-filament-panels::page>

    <div>
        <div wire:ignore x-data>
            <div class="container-layout">
                <div style="width:300px;">
                    <div class="grid-stack"></div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        @once
            <link rel="stylesheet" href="/gridstack/gridstack.min.css">
            <link rel="stylesheet" href="/gridstack/gridstack-extra.min.css">
            <link rel="stylesheet" href="/vjs/video-js.css">
            <link rel="stylesheet" href="/cms/style.css">
            <style type="text/css">
                .container-layout {
                    display: flex;
                    justify-content: center;
                }

                .grid-stack-item-content {
                    background-color: #ffbf00;
                    margin: 0;
                    overflow: auto hidden !important;
                }
            </style>
        @endonce
    @endpush

    @push('scripts')
        @once

            @php
                $options = \App\Services\LayoutService::build($record, false);
            @endphp

            <script src="/jquery/jquery.min.js"
                integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
                crossorigin="anonymous" referrerpolicy="no-referrer"></script>
            <script src="/gridstack/gridstack-all.js"></script>
            <script>
                var options = @json($options);
                var grid = GridStack.init(options);
            </script>
        @endonce
    @endpush
</x-filament-panels::page>
