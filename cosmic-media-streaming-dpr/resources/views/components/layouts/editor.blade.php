@extends('components/layouts/apps')

@section('content')

    <div class="grid grid-cols-2 gap-2">
        <div class="col-span-4 ..."></div>
        <div class="grid grid-cols-2">
        </div>
        <div>
            <div class="text-right mt-2">
                <button type="button" id="save-editor"
                    class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">Save
                    Change</button>
            </div>
        </div>
        <div>
            <div class="text-right mt-2">
                <button type="button" id="back"
                    class="text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-green-600 dark:hover:bg-green-700 focus:outline-none dark:focus:ring-green-800">Back</button>
            </div>
        </div>
        <div>
            <div class="text-right mt-2 mr-3">
                <button type="button"
                    class="text-white bg-red-700 dark:bg-red-800 cursor-not-allowed font-medium rounded-lg text-sm px-5 py-2.5 text-center"
                    disabled>Your token is @php echo $id @endphp</button>

            </div>
        </div>
    </div>

    <div id="gjs" style="height: 300px; border: 1px solid #ddd;"></div>

    <script src="https://unpkg.com/grapesjs"></script>
    <script src="https://unpkg.com/grapesjs-preset-newsletter"></script>

    <script type="text/javascript">
        const projectId = @json($id)

        // Initialize GrapesJS editor with plugins and options
        const editor = grapesjs.init({
            container: '#gjs',
            fromElement: true,
            plugins: ['grapesjs-preset-newsletter'],
            pluginsOpts: {
                'grapesjs-preset-newsletter': {}
            },
            storageManager: {
                type: 'local',
                autosave: true, // Automatically saves changes
                autoload: true, // Automatically loads stored data
                stepsBeforeSave: 1, // Save after every change
                options: {
                    local: {
                        key: `gjsProject-${projectId}`
                    }
                }
            }
        });

        // Access the BlockManager to add custom blocks
        const blockManager = editor.BlockManager;

        // Add custom image blocks
        const images = [{
                img: "https://www.transtv.co.id/layout/new/src/images/tv/TRANS%20TV%20live%20streaming%20tv.jpg",
                name: "TRANS TV"
            },
            {
                img: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRfp6m8VveJA_isp4vNE_U1XV_9RKvh4RYf7w&s",
                name: "TRANS 7"
            },
            {
                img: "https://thumbor.prod.vidiocdn.com/GdCgs7TSfDxuvoqhqCWKFrafCPs=/filters:quality(70)/vidio-web-prod-livestreaming/uploads/livestreaming/image/875/net-tv-b8499f.jpg",
                name: "NET TV"
            }
        ];

        images.forEach((element, index) => {
            blockManager.add(`custom-image-block-${index}`, {
                label: element.name,
                content: `<img src="${element.img}" alt="${element.name}" style="max-width: 100%;" />`,
                category: 'Image Sources',
                attributes: {
                    class: 'fa fa-image'
                }
            });
        });

        // Add custom video blocks
        const videos = [{
                video: "http://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4",
                name: "Big Buck Bunny"
            },
            {
                video: "http://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4",
                name: "Elephant Dream"
            },
            {
                video: "http://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4",
                name: "For Bigger Blazes"
            }
        ];

        videos.forEach((element, index) => {
            blockManager.add(`custom-video-block-${index}`, {
                label: element.name,
                content: `<video src="${element.video}" autoplay muted playsinline controls style="max-width: 100%;"></video>`,
                category: 'Video Sources',
                attributes: {
                    class: 'fa fa-play-circle'
                }
            });
        });

        document.getElementById('save-editor').addEventListener('click', async function() {
            // Retrieve project data from localStorage for manual inspection
            const storedData = localStorage.getItem(`gjsProject-${projectId}`);
            const htmlContent = editor.getHtml();
            const cssContent = editor.getCss();

            const formdata = new FormData();
            formdata.append("data_layout", storedData);
            formdata.append("data_html", htmlContent);
            formdata.append("data_css", cssContent);

            try {
                const apiUrl = `{{ route('api.custom-layout.save_change', ['id' => '']) }}${projectId}`;
                const result = await fetch(apiUrl, {
                    method: 'POST',
                    body: formdata,
                });
                const response = await result.json();
                console.log(response);
            } catch (error) {
                console.error("Error saving data:", error);
            }
        });

        async function load_data(projectId) {
            try {
                // Make the fetch request
                const apiUrl = `{{ route('api.custom-layout.load_data', ['id' => '']) }}${projectId}`;
                const result = await fetch(apiUrl);

                // Check if the fetch was successful
                if (!result.ok) {
                    throw new Error(`Error: ${result.status} ${result.statusText}`);
                }

                // Parse the JSON response
                const response = await result.json();

                // Retrieve stored data
                const storedData = localStorage.getItem(`gjsProject-${projectId}`);

                if (!storedData) {
                    // Store the data if it does not already exist
                    localStorage.setItem(`gjsProject-${projectId}`, response.data.data_layout);
                    console.log('Data has been stored.');

                    // Refresh the page after storing the data
                    window.location.reload();
                } else {
                    console.log('Data already exists in localStorage.');
                }
            } catch (error) {
                console.error("Error loading data:", error);
            }
        }

        load_data(projectId);

        document.getElementById('back').addEventListener('click', function() {
            // Navigate to the list page
            const url = 'http://localhost:8000/back-office/custom-layouts';
            window.location.href = url;

            // Remove the item from localStorage
            localStorage.removeItem(`gjsProject-${projectId}`);
        });
    </script>

    {{-- 
<script>
       var goBack = function() {
        console.log("Going back in history...");
        window.history.back(); // This goes back in history, but nothing is returned.
    };

    goBack();

</script> --}}
