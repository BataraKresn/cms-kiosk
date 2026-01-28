<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Edit Media Video</title>

    <!-- Preload critical assets -->
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" as="style">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/resumable.js/1.1.0/resumable.min.js" as="script">
    <link rel="preload" href="https://code.jquery.com/jquery-3.6.4.min.js" as="script">
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <div class="container pt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header text-center">
                        <h5>Edit Media Video</h5>
                    </div>

                    <div class="card-body">
                        <div class="d-flex justify-content-end">
                            <a class="btn btn-danger" href="{!! env('URL_APP') . '/back-office/media-videos' !!}" role="button">Cancel</a>
                        </div>

                        <input type="hidden" name="id" id="id" value="{{ $data->id }}">
                        <div class="mb-3">
                            <label for="video_name" class="form-label">Video Name</label>
                            <input type="text" class="form-control" id="name" name="name"
                                value="{{ $data->name }}">
                        </div>
                        <div class="mb-3" id="upload-container">
                            <label for="browseFile" class="form-label">Upload File</label>
                            <input class="form-control" type="file" id="browseFile" accept="video/mp4">
                            <span id="file-name">{{ $data->path }}</span>
                        </div>
                        <div class="d-flex justify-content-start gap-2">
                            <a class="btn btn-warning" href="{!! env('URL_APP') . '/api/video/' . $data->path !!}" role="button"
                                target="_blank">Preview Video</a>
                            <a class="btn btn-success" href="{!! env('URL_APP') . '/downloadVideo/' . $data->id !!}" role="button"
                                target="_blank">Download Video</a>
                        </div>

                        <div class="progress mt-3" style="display: none; height: 25px">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                aria-valuemin="0" aria-valuemax="100" style="width: 0%; height: 100%">0%</div>
                        </div>
                        <p id="uploadSpeed" class="mt-2 text-center" style="font-size: 0.775rem; display: none;">Upload
                            speed: 0 Mbps</p>

                        <div id="uploadControls" class="d-flex justify-content-center mt-2" style="display: none;">
                            <button id="pauseButton" class="btn btn-warning btn-sm mx-2" hidden>Pause Upload</button>
                            <button id="resumeButton" class="btn btn-primary btn-sm mx-2" hidden>Resume
                                Upload</button>
                        </div>
                    </div>

                    <div class="card-footer p-4" style="display: none">
                        <img id="imagePreview" src="" style="width: 100%; height: auto; display: none"
                            alt="Preview Image">
                        <video id="videoPreview" src="" controls
                            style="width: 100%; height: auto; display: none"></video>
                        <button id="submitButton" class="btn btn-success mt-3">Submit</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            let browseFile = $('#browseFile');
            let submitButton = $('#submitButton');
            let progress = $('.progress');
            let uploadSpeed = $('#uploadSpeed');
            let pauseButton = $('#pauseButton');
            let resumeButton = $('#resumeButton');
            let uploadControls = $('#uploadControls');
            let uploadedBytes = 0;
            let startTime = performance.now();
            let speedSamples = [];
            const maxSamples = 5;
            let isUploading = false;
            let autoRetryCount = 0;
            const maxAutoRetries = 10;
            let lastNetworkCheck = Date.now();
            let networkCheckInterval = 2000;
            // Add adaptive chunk sizing based on network conditions
            let uploadSpeedHistory = [];
            let adaptiveChunkSizeEnabled = true;
            let initialChunkSize = 15 * 1024 * 1024;
            let currentChunkSize = initialChunkSize;
            let minChunkSize = 5 * 1024 * 1024;
            let maxChunkSize = 30 * 1024 * 1024;

            let isOnline = true;
            let selectedFile = null;

            browseFile.on('change', function() {
                if (this.files && this.files[0]) {
                    $('#file-name').text(this.files[0].name);
                }
            });

            function formatSpeed(bytesPerSecond) {
                return (bytesPerSecond / (1024 * 1024)).toFixed(2) + " MBps";
            }

            function updateAdaptiveChunkSize(bytesPerSecond) {
                if (!adaptiveChunkSizeEnabled) return;

                uploadSpeedHistory.push(bytesPerSecond);
                if (uploadSpeedHistory.length > 5) uploadSpeedHistory.shift();

                // Calculate average speed from recent history
                let avgSpeed = uploadSpeedHistory.reduce((a, b) => a + b, 0) / uploadSpeedHistory.length;

                // Adjust chunk size based on speed (target ~3 seconds per chunk)
                let targetChunkSize = avgSpeed * 3;

                // Apply limits and smoothing
                targetChunkSize = Math.max(minChunkSize, Math.min(maxChunkSize, targetChunkSize));

                // Smooth transition (30% of the difference)
                currentChunkSize = currentChunkSize + (targetChunkSize - currentChunkSize) * 0.3;

                // Round to nearest MB for cleaner values
                currentChunkSize = Math.round(currentChunkSize / (1024 * 1024)) * 1024 * 1024;

                console.log(
                    `Adaptive chunk size: ${(currentChunkSize / (1024 * 1024)).toFixed(2)}MB based on ${(avgSpeed / (1024 * 1024)).toFixed(2)}MB/s speed`
                );

                // We can't change chunk size mid-upload, but this will affect the next upload
                resumable.opts.chunkSize = currentChunkSize;
            }

            function calculateSpeed(uploadBytes) {
                let elapsedTime = (performance.now() - startTime) / 1000;
                let bytesPerSecond = uploadBytes / elapsedTime;

                speedSamples.push(bytesPerSecond);
                if (speedSamples.length > maxSamples) {
                    speedSamples.shift();
                }

                let averageSpeed = speedSamples.reduce((a, b) => a + b, 0) / speedSamples.length;

                updateAdaptiveChunkSize(averageSpeed);

                return formatSpeed(averageSpeed);
            }

            let resumable = new Resumable({
                target: '{{ route('upload.store') }}',
                query: {
                    _token: '{{ csrf_token() }}'
                },
                fileType: ['mp4'],
                chunkSize: 15 * 1024 * 1024,
                simultaneousUploads: 8,
                maxChunkRetries: 10,
                chunkRetryInterval: 1000,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                testChunks: false,
                throttleProgressCallbacks: 1,
                prioritizeFirstAndLastChunk: true,
                maxFiles: 1,
                permanentErrors: [400, 404, 415, 500, 501],
                maxFilesErrorCallback: function() {
                    Swal.fire('Error', 'Maximum number of files exceeded.', 'error');
                }
            });

            resumable.assignBrowse(browseFile[0]);

            function handleConnectionLost() {
                console.warn("Connection lost, waiting for network...");
                uploadSpeed.text("Connection lost, waiting for network...").addClass('text-danger').show();
                resumable.pause();

                // Update button states when connection is lost
                // if (isUploading) {
                //     pauseButton.prop('disabled', true);
                //     resumeButton.prop('disabled', false);
                // }
            }

            function handleConnectionRestored() {
                console.log("Connection restored, resuming upload...");
                uploadSpeed.text("Connection restored, resuming upload...").removeClass('text-danger').show();

                if (isUploading) {
                    setTimeout(() => {
                        resumable.upload();
                        uploadSpeed.text("Upload speed: calculating...").removeClass('text-danger').show();

                        // pauseButton.prop('disabled', false);
                        // resumeButton.prop('disabled', true);
                    }, 1000);
                }
            }

            window.addEventListener('online', handleConnectionRestored);
            window.addEventListener('offline', handleConnectionLost);

            pauseButton.on('click', function() {
                if (isUploading) {
                    resumable.pause();
                    isUploading = false;
                    uploadSpeed.text("Upload paused manually").addClass('text-warning').show();
                    // pauseButton.prop('disabled', true);
                    // resumeButton.prop('disabled', false);
                }
            });

            resumeButton.on('click', function() {
                if (!isUploading) {
                    resumable.upload();
                    isUploading = true;
                    uploadSpeed.text("Resuming upload...").removeClass('text-warning text-danger').show();
                    // pauseButton.prop('disabled', false);
                    // resumeButton.prop('disabled', true);
                }
            });

            function checkNetworkAndResume() {
                if (!isUploading || Date.now() - lastNetworkCheck < networkCheckInterval) {
                    return;
                }

                lastNetworkCheck = Date.now();

                fetch(window.location.href, {
                        method: 'HEAD',
                        cache: 'no-store',
                        headers: {
                            'Cache-Control': 'no-cache'
                        }
                    })
                    .then(() => {
                        if (!navigator.onLine) {
                            console.log("Network detected despite browser reporting offline");
                            handleConnectionRestored();
                        }
                    })
                    .catch(err => {
                        if (navigator.onLine) {
                            console.warn("Network request failed despite browser reporting online");
                            handleConnectionLost();
                        }
                    });
            }

            // setInterval(checkNetworkAndResume, 5000);

            resumable.on('fileAdded', async function(file) {
                selectedFile = file;
                $('.card-footer').show();

                $('#file-name').text(file.fileName);

                let fileType = file.file.type;
                let fileURL = URL.createObjectURL(file.file);
                if (fileType.startsWith('image/')) {
                    $('#imagePreview').attr('src', fileURL).show();
                    $('#videoPreview').hide();
                } else if (fileType.startsWith('video/')) {
                    $('#videoPreview').attr('src', fileURL).show();
                    $('#imagePreview').hide();
                }
            });

            submitButton.on('click', async function() {
                if (!selectedFile) {
                    Swal.fire('Error', 'Please select a file first.', 'error');
                    return;
                }

                const videoName = $('#name').val();
                if (!videoName) {
                    Swal.fire('Error', 'Please enter a video name.', 'error');
                    return;
                }

                showProgress();
                resumable.upload();
                isUploading = true;
                uploadControls.css('display', 'flex');
                // pauseButton.prop('disabled', false);
                // resumeButton.prop('disabled', true);
            });

            resumable.on('pause', function() {
                console.warn("Upload paused");
                isUploading = false;

                if (!uploadSpeed.hasClass('text-warning')) {
                    uploadSpeed.text("Upload paused, waiting to resume...").addClass('text-danger').show();
                }
            });

            resumable.on('fileProgress', async function(file) {
                if (!isOnline) {
                    isOnline = true;
                }

                isUploading = true;
                autoRetryCount = 0;

                let progress = Math.floor(file.progress() * 100);
                console.log('Upload progress: ' + progress + '%');
                updateProgress(progress);

                let newUploadedBytes = file.file.size * file.progress();
                let uploadedThisTime = newUploadedBytes - uploadedBytes;
                uploadedBytes = newUploadedBytes;

                let speedText = calculateSpeed(uploadedBytes);
                uploadSpeed.text(`Upload speed: ${speedText}`).removeClass('text-danger text-warning')
                    .show();

                // pauseButton.prop('disabled', false);
                // resumeButton.prop('disabled', true);
            });

            resumable.on('fileSuccess', async function(file, response) {
                isUploading = false;
                uploadControls.hide();

                response = JSON.parse(response);
                let videoName = $('#name').val();
                let fileName = response.name;
                let videoId = $('#id').val();

                const myHeaders = new Headers();
                myHeaders.append("Content-Type", "application/json");

                const raw = JSON.stringify({
                    "id": videoId,
                    "name": videoName,
                    "path": fileName
                });

                const requestOptions = {
                    method: "POST",
                    headers: myHeaders,
                    body: raw,
                    redirect: "follow"
                };

                try {
                    const apiResponse = await fetch("{!! env('URL_APP') !!}/api/storeEditMediaVideo",
                        requestOptions);
                    const result = await apiResponse.text();
                    console.log(result);

                    const refreshHeaders = new Headers();
                    refreshHeaders.append("Content-Type", "application/json");

                    const refreshRaw = JSON.stringify({
                        "video_id": videoId
                    });

                    const refreshOptions = {
                        method: "POST",
                        headers: refreshHeaders,
                        body: refreshRaw,
                        redirect: "follow"
                    };

                    const refreshResponse = await fetch(
                        "{!! env('URL_APP') !!}/api/refreshDisplaysByVideo",
                        refreshOptions);
                    const refreshResult = await refreshResponse.json();
                    console.log("Refresh displays response:", refreshResult);

                    let successMessage = "Edit video successfully!";
                    if (refreshResult.displays_count > 0) {
                        successMessage += ` Refreshed ${refreshResult.displays_count} displays.`;
                    }

                    Swal.fire({
                        position: "center",
                        icon: "success",
                        title: successMessage,
                        showConfirmButton: false,
                        timer: 1500
                    });

                    window.history.back();
                } catch (error) {
                    console.error('API call failed:', error);
                    Swal.fire('Error', 'Failed uploading error.', 'error');
                } finally {
                    hideProgress();
                    uploadSpeed.hide();
                }
            });

            resumable.on('fileError', async function(file, response) {
                isUploading = false;

                console.error('Upload error:', response);

                try {
                    const errorResponse = JSON.parse(response);
                    Swal.fire('Error', errorResponse.error || 'File uploading error.', 'error');
                } catch (e) {
                    Swal.fire('Error', 'File uploading error: ' + response, 'error');
                }

                hideProgress();
                uploadSpeed.hide();
            });

            resumable.on('chunkSuccess', function(file, message) {
                console.log(`Chunk uploaded successfully`);
                autoRetryCount = 0;
            });

            resumable.on('chunkError', function(file, message) {
                console.error(`Chunk error:`, message);

                if (isOnline && autoRetryCount < maxAutoRetries) {
                    autoRetryCount++;
                    const delay = Math.min(2000 * autoRetryCount, 10000);

                    uploadSpeed.text(
                            `Connection issue, retry attempt ${autoRetryCount}/${maxAutoRetries} in ${delay/1000}s...`
                        )
                        .addClass('text-warning').show();

                    setTimeout(() => {
                        console.log(
                            `Retrying chunk upload (attempt ${autoRetryCount}/${maxAutoRetries})...`
                        );
                        resumable.upload();
                    }, delay);
                } else if (autoRetryCount >= maxAutoRetries) {
                    uploadSpeed.text(
                            `Maximum retry attempts (${maxAutoRetries}) reached. Please check your connection.`
                        )
                        .addClass('text-danger').show();

                    window.addEventListener('online', function onlineHandler() {
                        resumable.upload();
                        window.removeEventListener('online', onlineHandler);
                    }, {
                        once: true
                    });
                }
            });

            async function showProgress() {
                progress.find('.progress-bar').css('width', '0%').html('0%').removeClass('bg-success');
                progress.show();
                uploadSpeed.show();
                startTime = performance.now();
                uploadedBytes = 0;
                speedSamples = [];
                autoRetryCount = 0;
                uploadControls.css('display', 'flex');

                pauseButton.prop('hidden', false);
                resumeButton.prop('hidden', false);
            }

            async function updateProgress(value) {
                progress.find('.progress-bar').css('width', `${value}%`).html(`${value}%`);
                if (value === 100) {
                    progress.find('.progress-bar').addClass('bg-success');
                }
            }

            async function hideProgress() {
                progress.hide();
                uploadSpeed.hide();
                uploadControls.hide();
                isUploading = false;
            }
        });
    </script>

    <!-- Move scripts to end of body -->
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/resumable.js/1.1.0/resumable.min.js"></script>
    <script defer src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script type="text/javascript">
        // Lazy load the script execution
        window.addEventListener('load', function() {
            $(document).ready(function() {
                let browseFile = $('#browseFile');
                let submitButton = $('#submitButton');
                let progress = $('.progress');
                let uploadSpeed = $('#uploadSpeed');
                let pauseButton = $('#pauseButton');
                let resumeButton = $('#resumeButton');
                let uploadControls = $('#uploadControls');
                let uploadedBytes = 0;
                let startTime = performance.now();
                let speedSamples = [];
                const maxSamples = 5;
                let isUploading = false;
                let autoRetryCount = 0;
                const maxAutoRetries = 10;
                let lastNetworkCheck = Date.now();
                let networkCheckInterval = 2000;
                // Add adaptive chunk sizing based on network conditions
                let uploadSpeedHistory = [];
                let adaptiveChunkSizeEnabled = true;
                let initialChunkSize = 15 * 1024 * 1024;
                let currentChunkSize = initialChunkSize;
                let minChunkSize = 5 * 1024 * 1024;
                let maxChunkSize = 30 * 1024 * 1024;

                let isOnline = true;
                let selectedFile = null;

                browseFile.on('change', function() {
                    if (this.files && this.files[0]) {
                        $('#file-name').text(this.files[0].name);
                    }
                });

                function formatSpeed(bytesPerSecond) {
                    return (bytesPerSecond / (1024 * 1024)).toFixed(2) + " MBps";
                }

                function updateAdaptiveChunkSize(bytesPerSecond) {
                    if (!adaptiveChunkSizeEnabled) return;

                    uploadSpeedHistory.push(bytesPerSecond);
                    if (uploadSpeedHistory.length > 5) uploadSpeedHistory.shift();

                    // Calculate average speed from recent history
                    let avgSpeed = uploadSpeedHistory.reduce((a, b) => a + b, 0) / uploadSpeedHistory.length;

                    // Adjust chunk size based on speed (target ~3 seconds per chunk)
                    let targetChunkSize = avgSpeed * 3;

                    // Apply limits and smoothing
                    targetChunkSize = Math.max(minChunkSize, Math.min(maxChunkSize, targetChunkSize));

                    // Smooth transition (30% of the difference)
                    currentChunkSize = currentChunkSize + (targetChunkSize - currentChunkSize) * 0.3;

                    // Round to nearest MB for cleaner values
                    currentChunkSize = Math.round(currentChunkSize / (1024 * 1024)) * 1024 * 1024;

                    console.log(
                        `Adaptive chunk size: ${(currentChunkSize / (1024 * 1024)).toFixed(2)}MB based on ${(avgSpeed / (1024 * 1024)).toFixed(2)}MB/s speed`
                    );

                    // We can't change chunk size mid-upload, but this will affect the next upload
                    resumable.opts.chunkSize = currentChunkSize;
                }

                function calculateSpeed(uploadBytes) {
                    let elapsedTime = (performance.now() - startTime) / 1000;
                    let bytesPerSecond = uploadBytes / elapsedTime;

                    speedSamples.push(bytesPerSecond);
                    if (speedSamples.length > maxSamples) {
                        speedSamples.shift();
                    }

                    let averageSpeed = speedSamples.reduce((a, b) => a + b, 0) / speedSamples.length;

                    updateAdaptiveChunkSize(averageSpeed);

                    return formatSpeed(averageSpeed);
                }

                let resumable = new Resumable({
                    target: '{{ route('upload.store') }}',
                    query: {
                        _token: '{{ csrf_token() }}'
                    },
                    fileType: ['mp4'],
                    chunkSize: 15 * 1024 * 1024,
                    simultaneousUploads: 8,
                    maxChunkRetries: 10,
                    chunkRetryInterval: 1000,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    testChunks: false,
                    throttleProgressCallbacks: 1,
                    prioritizeFirstAndLastChunk: true,
                    maxFiles: 1,
                    permanentErrors: [400, 404, 415, 500, 501],
                    maxFilesErrorCallback: function() {
                        Swal.fire('Error', 'Maximum number of files exceeded.', 'error');
                    }
                });

                resumable.assignBrowse(browseFile[0]);

                function handleConnectionLost() {
                    console.warn("Connection lost, waiting for network...");
                    uploadSpeed.text("Connection lost, waiting for network...").addClass('text-danger').show();
                    resumable.pause();

                    // Update button states when connection is lost
                    // if (isUploading) {
                    //     pauseButton.prop('disabled', true);
                    //     resumeButton.prop('disabled', false);
                    // }
                }

                function handleConnectionRestored() {
                    console.log("Connection restored, resuming upload...");
                    uploadSpeed.text("Connection restored, resuming upload...").removeClass('text-danger').show();

                    if (isUploading) {
                        setTimeout(() => {
                            resumable.upload();
                            uploadSpeed.text("Upload speed: calculating...").removeClass('text-danger').show();

                            // pauseButton.prop('disabled', false);
                            // resumeButton.prop('disabled', true);
                        }, 1000);
                    }
                }

                window.addEventListener('online', handleConnectionRestored);
                window.addEventListener('offline', handleConnectionLost);

                pauseButton.on('click', function() {
                    if (isUploading) {
                        resumable.pause();
                        isUploading = false;
                        uploadSpeed.text("Upload paused manually").addClass('text-warning').show();
                        // pauseButton.prop('disabled', true);
                        // resumeButton.prop('disabled', false);
                    }
                });

                resumeButton.on('click', function() {
                    if (!isUploading) {
                        resumable.upload();
                        isUploading = true;
                        uploadSpeed.text("Resuming upload...").removeClass('text-warning text-danger').show();
                        // pauseButton.prop('disabled', false);
                        // resumeButton.prop('disabled', true);
                    }
                });

                function checkNetworkAndResume() {
                    if (!isUploading || Date.now() - lastNetworkCheck < networkCheckInterval) {
                        return;
                    }

                    lastNetworkCheck = Date.now();

                    fetch(window.location.href, {
                            method: 'HEAD',
                            cache: 'no-store',
                            headers: {
                                'Cache-Control': 'no-cache'
                            }
                        })
                        .then(() => {
                            if (!navigator.onLine) {
                                console.log("Network detected despite browser reporting offline");
                                handleConnectionRestored();
                            }
                        })
                        .catch(err => {
                            if (navigator.onLine) {
                                console.warn("Network request failed despite browser reporting online");
                                handleConnectionLost();
                            }
                        });
                }

                // setInterval(checkNetworkAndResume, 5000);

                resumable.on('fileAdded', async function(file) {
                    selectedFile = file;
                    $('.card-footer').show();

                    $('#file-name').text(file.fileName);

                    let fileType = file.file.type;
                    let fileURL = URL.createObjectURL(file.file);
                    if (fileType.startsWith('image/')) {
                        $('#imagePreview').attr('src', fileURL).show();
                        $('#videoPreview').hide();
                    } else if (fileType.startsWith('video/')) {
                        $('#videoPreview').attr('src', fileURL).show();
                        $('#imagePreview').hide();
                    }
                });

                submitButton.on('click', async function() {
                    if (!selectedFile) {
                        Swal.fire('Error', 'Please select a file first.', 'error');
                        return;
                    }

                    const videoName = $('#name').val();
                    if (!videoName) {
                        Swal.fire('Error', 'Please enter a video name.', 'error');
                        return;
                    }

                    showProgress();
                    resumable.upload();
                    isUploading = true;
                    uploadControls.css('display', 'flex');
                    // pauseButton.prop('disabled', false);
                    // resumeButton.prop('disabled', true);
                });

                resumable.on('pause', function() {
                    console.warn("Upload paused");
                    isUploading = false;

                    if (!uploadSpeed.hasClass('text-warning')) {
                        uploadSpeed.text("Upload paused, waiting to resume...").addClass('text-danger').show();
                    }
                });

                resumable.on('fileProgress', async function(file) {
                    if (!isOnline) {
                        isOnline = true;
                    }

                    isUploading = true;
                    autoRetryCount = 0;

                    let progress = Math.floor(file.progress() * 100);
                    console.log('Upload progress: ' + progress + '%');
                    updateProgress(progress);

                    let newUploadedBytes = file.file.size * file.progress();
                    let uploadedThisTime = newUploadedBytes - uploadedBytes;
                    uploadedBytes = newUploadedBytes;

                    let speedText = calculateSpeed(uploadedBytes);
                    uploadSpeed.text(`Upload speed: ${speedText}`).removeClass('text-danger text-warning')
                        .show();

                    // pauseButton.prop('disabled', false);
                    // resumeButton.prop('disabled', true);
                });

                resumable.on('fileSuccess', async function(file, response) {
                    isUploading = false;
                    uploadControls.hide();

                    response = JSON.parse(response);
                    let videoName = $('#name').val();
                    let fileName = response.name;
                    let videoId = $('#id').val();

                    const myHeaders = new Headers();
                    myHeaders.append("Content-Type", "application/json");

                    const raw = JSON.stringify({
                        "id": videoId,
                        "name": videoName,
                        "path": fileName
                    });

                    const requestOptions = {
                        method: "POST",
                        headers: myHeaders,
                        body: raw,
                        redirect: "follow"
                    };

                    try {
                        const apiResponse = await fetch("{!! env('URL_APP') !!}/api/storeEditMediaVideo",
                            requestOptions);
                        const result = await apiResponse.text();
                        console.log(result);

                        const refreshHeaders = new Headers();
                        refreshHeaders.append("Content-Type", "application/json");

                        const refreshRaw = JSON.stringify({
                            "video_id": videoId
                        });

                        const refreshOptions = {
                            method: "POST",
                            headers: refreshHeaders,
                            body: refreshRaw,
                            redirect: "follow"
                        };

                        const refreshResponse = await fetch(
                            "{!! env('URL_APP') !!}/api/refreshDisplaysByVideo",
                            refreshOptions);
                        const refreshResult = await refreshResponse.json();
                        console.log("Refresh displays response:", refreshResult);

                        let successMessage = "Edit video successfully!";
                        if (refreshResult.displays_count > 0) {
                            successMessage += ` Refreshed ${refreshResult.displays_count} displays.`;
                        }

                        Swal.fire({
                            position: "center",
                            icon: "success",
                            title: successMessage,
                            showConfirmButton: false,
                            timer: 1500
                        });

                        window.history.back();
                    } catch (error) {
                        console.error('API call failed:', error);
                        Swal.fire('Error', 'Failed uploading error.', 'error');
                    } finally {
                        hideProgress();
                        uploadSpeed.hide();
                    }
                });

                resumable.on('fileError', async function(file, response) {
                    isUploading = false;

                    console.error('Upload error:', response);

                    try {
                        const errorResponse = JSON.parse(response);
                        Swal.fire('Error', errorResponse.error || 'File uploading error.', 'error');
                    } catch (e) {
                        Swal.fire('Error', 'File uploading error: ' + response, 'error');
                    }

                    hideProgress();
                    uploadSpeed.hide();
                });

                resumable.on('chunkSuccess', function(file, message) {
                    console.log(`Chunk uploaded successfully`);
                    autoRetryCount = 0;
                });

                resumable.on('chunkError', function(file, message) {
                    console.error(`Chunk error:`, message);

                    if (isOnline && autoRetryCount < maxAutoRetries) {
                        autoRetryCount++;
                        const delay = Math.min(2000 * autoRetryCount, 10000);

                        uploadSpeed.text(
                                `Connection issue, retry attempt ${autoRetryCount}/${maxAutoRetries} in ${delay/1000}s...`
                            )
                            .addClass('text-warning').show();

                        setTimeout(() => {
                            console.log(
                                `Retrying chunk upload (attempt ${autoRetryCount}/${maxAutoRetries})...`
                            );
                            resumable.upload();
                        }, delay);
                    } else if (autoRetryCount >= maxAutoRetries) {
                        uploadSpeed.text(
                                `Maximum retry attempts (${maxAutoRetries}) reached. Please check your connection.`
                            )
                            .addClass('text-danger').show();

                        window.addEventListener('online', function onlineHandler() {
                            resumable.upload();
                            window.removeEventListener('online', onlineHandler);
                        }, {
                            once: true
                        });
                    }
                });

                async function showProgress() {
                    progress.find('.progress-bar').css('width', '0%').html('0%').removeClass('bg-success');
                    progress.show();
                    uploadSpeed.show();
                    startTime = performance.now();
                    uploadedBytes = 0;
                    speedSamples = [];
                    autoRetryCount = 0;
                    uploadControls.css('display', 'flex');

                    pauseButton.prop('hidden', false);
                    resumeButton.prop('hidden', false);
                }

                async function updateProgress(value) {
                    progress.find('.progress-bar').css('width', `${value}%`).html(`${value}%`);
                    if (value === 100) {
                        progress.find('.progress-bar').addClass('bg-success');
                    }
                }

                async function hideProgress() {
                    progress.hide();
                    uploadSpeed.hide();
                    uploadControls.hide();
                    isUploading = false;
                }
            });
        });
    </script>
</body>
</html>
