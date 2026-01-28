<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CMS DPR RI</title>
    <link rel="icon" type="image/svg" href="/images/logo.svg" loading="lazy" />
    <link rel="stylesheet" href="/gridstack/gridstack.min.css">
    <link rel="stylesheet" href="/gridstack/gridstack-extra.min.css">
    <link rel="stylesheet" href="/cms/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            margin: 0;
            width: 100%;
            height: 100vh;
            background: #000;
            display: flex;
            flex-direction: column;
        }

        /* Handle grid */
        .grid-stack-item-content {
            background-color: #fff;
            margin: 0;
            border-radius: 5px;
            box-shadow: none !important;
            position: relative;
        }

        /* Resizable handle */
        .ui-resizable-se {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 20px;
            height: 20px;
            cursor: se-resize;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 3px;
        }

        /* Display width and height */
        .size-display {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.6);
            color: white;
            padding: 2px 5px;
            font-size: 12px;
            border-radius: 3px;
            z-index: 10;
        }

        /* GridStack container */
        .grid-stack-container {
            flex-grow: 1;
            height: 100%;
            /* Use full height */
            overflow-y: auto;
            overflow-x: hidden;
            border: 1px solid #ccc;
            padding: 10px;
            transform-origin: 0 0;
            /* Set the origin for zoom scaling */
            transition: transform 0.3s ease;
            /* Smooth transition for zoom */
        }

        /* Zoom controls */
        .zoom-controls {
            margin: 10px;
            display: flex;
            gap: 10px;
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 100;
        }
    </style>
</head>

<body class="bg-gray-100">

    <script>
        const data = {!! json_encode($data ?? []) !!};
        const id = {!! json_encode($id ?? null) !!};
    </script>

    <!-- Zoom Controls -->
    <div class="zoom-controls">
        <button onclick="zoomIn()" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-700">
            Zoom In
        </button>
        <button onclick="zoomOut()" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-700">
            Zoom Out
        </button>
    </div>

    <!-- GridStack Container -->
    <div class="grid-stack-container">
        <div class="grid-stack bg-blue-100"></div>
    </div>

    <!-- Add and Save buttons -->
    <div class="mt-2 mb-2 ml-2 flex space-x-2">
        <button onclick="addItem()" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-700">
            Add New Item
        </button>
        <button onclick="saveLayout()" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-700">
            Save Layout
        </button>
    </div>

    <script src="/jquery/jquery.min.js"></script>
    <script src="/gridstack/gridstack-all.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const gridContainer = document.querySelector('.grid-stack-container');
        const gridStack = document.querySelector('.grid-stack');

        // Initialize GridStack
        let grid = GridStack.init({
            column: data[0].column,
            row: data[0].row,
            float: true,
            width: data[0].width,
            height: data[0].height,
            resizable: true, // Enable resizing
        });

        // Function to update width and height display in pixels
        function updateSizeDisplay(item, widthInUnits, heightInUnits) {
            let sizeDisplay = item.querySelector('.size-display');
            if (!sizeDisplay) {
                sizeDisplay = document.createElement('div');
                sizeDisplay.classList.add('size-display');
                item.appendChild(sizeDisplay);
            }

            const widthPx = widthInUnits * 60;
            const heightPx = heightInUnits * 60;

            sizeDisplay.innerHTML = `W: ${widthPx}px, H: ${heightPx}px`;
        }

        // Add predefined items to the grid from injected data
        if (Array.isArray(data) && data.length > 0) {
            data.forEach((item, index) => {
                let widget = grid.addWidget(
                    `<div class="grid-stack-item">
                        <div class="grid-stack-item-content bg-teal-500 text-white flex items-center justify-center text-lg rounded-lg relative">
                            Item ${index + 1}
                            <button onclick="deleteItem(this)" class="absolute top-1 right-1 px-2 py-1 text-xs bg-red-500 text-white rounded">Delete</button>
                        </div>
                    </div>`, {
                        x: item.x,
                        y: item.y,
                        w: item.w,
                        h: item.h,
                        resizable: {
                            handles: 'se'
                        },
                    }
                );

                updateSizeDisplay(widget, item.w, item.h);
            });
        }

        function deleteItem(itemElement) {
            const item = grid.removeWidget(itemElement.closest('.grid-stack-item'));
        }

        function addItem() {
            // Find the maximum y value among existing items
            const existingItems = grid.getGridItems();
            const maxY = existingItems.reduce((max, item) => {
                return Math.max(max, item.y + item.h);
            }, 0);

            // Add a new widget just below the highest existing widget
            let widget = grid.addWidget(
                `<div class="grid-stack-item">
                    <div class="grid-stack-item-content bg-teal-500 text-white flex items-center justify-center text-lg rounded-lg relative">
                        New Widget
                        <button onclick="deleteItem(this)" class="absolute top-1 right-1 px-2 py-1 text-xs bg-red-500 text-white rounded">Delete</button>
                    </div>
                </div>`, {
                    x: 0,
                    y: maxY, // Set y to the maximum y value found
                    w: 6,
                    h: 3, // Increased height for a hugging effect
                    resizable: {
                        handles: 'se'
                    },
                }
            );

            updateSizeDisplay(widget, 6, 3); // Update the size display
        }

        async function saveLayout() {
            const serializedData = grid.save();
            console.log(serializedData);

            const result = serializedData.map(item => ({
                layout_id: id,
                x: item.x,
                y: item.y,
                w: item.w,
                h: item.h,
            }));
            console.log(result);

            const requestOptions = {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    spots: result
                }),
            };

            try {
                const env = "<?php echo env('URL_API'); ?>";
                const response = await fetch(env + "/spots/create/" + id, requestOptions);
                const result = await response.json();

                if (response.ok) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Save successfully!',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: result.message || 'Save unsuccessful!',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            } catch (error) {
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while saving.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                console.error('Error:', error);
            }
        }

        // Attach resize event to update width and height dynamically in pixels
        grid.on('resize', function(event, el, ui) {
            const width = ui.w;
            const height = ui.h;
            updateSizeDisplay(el, width, height);
        });

        // Zoom In Function
        function zoomIn() {
            let scale = parseFloat(getComputedStyle(gridStack).transform.split(',')[0].split('(')[1]) || 1;
            scale += 0.11;
            gridStack.style.transform = `scale(${scale})`;
        }

        // Zoom Out Function
        function zoomOut() {
            let scale = parseFloat(getComputedStyle(gridStack).transform.split(',')[0].split('(')[1]) || 1;
            scale = Math.max(0.24, scale - 0.1); // Prevent zooming out too much
            gridStack.style.transform = `scale(${scale})`;
        }
    </script>
</body>

</html>
