<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <title>Edit Media Video</title>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/resumable.js/1.1.0/resumable.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</head>

<body>

    <div class="container pt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header text-center">
                        <h5>Edit media video</h5>
                    </div>

                    <div class="card-body">
                        <div class="mb-3">
                            <label for="video_name" class="form-label">Video Name</label>
                            <input type="text" class="form-control" id="name" name="name">
                        </div>
                        <div class="mb-3" id="upload-container">
                            <label for="video_name" class="form-label">Upload File</label>
                            <input class="form-control" type="file" id="browseFile">
                        </div>
                        <div class="d-flex justify-content-start">
                            <a class="btn btn-transparent" href="#" role="button">Preview Video</a>
                            <a class="btn btn-transparent" href="#" role="button">Download Video</a>
                        </div>
                        {{-- <div id="upload-container" class="text-left">
                            <label for="video_name" class="form-label">Video Name</label>
                            <button id="browseFile" class="btn btn-primary">Browse File</button>
                        </div> --}}
                        <div style="display: none" class="progress mt-3" style="height: 25px">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"
                                style="width: 75%; height: 100%">75%</div>
                        </div>
                    </div>

                    <div class="card-footer p-4" style="display: none">
                        <img id="imagePreview" src="" style="width: 100%; height: auto; display: none"
                            alt="img" />
                        <video id="videoPreview" src="" controls
                            style="width: 100%; height: auto; display: none"></video>

                        <!-- Submit Button -->
                        <button id="submitButton" class="btn btn-success mt-3" style="display: none;">Submit</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        let browseFile = $('#browseFile');
        let submitButton = $('#submitButton');
        let resumable = new Resumable({
            target: '{{ route('upload.store') }}',
            query: {
                _token: '{{ csrf_token() }}'
            },
            fileType: ['png', 'jpg', 'jpeg', 'mp4'],
            chunkSize: 2 * 1024 * 1024, // 2MB chunk size
            headers: {
                'Accept': 'application/json'
            },
            testChunks: false,
            throttleProgressCallbacks: 1,
        });

        // Assign browse file event
        resumable.assignBrowse(browseFile[0]);

        // When file is selected, store it but DO NOT upload yet
        let selectedFile = null;
        resumable.on('fileAdded', function(file) {
            selectedFile = file;
            $('.card-footer').show(); // Show footer with preview

            // Preview for images/videos
            let fileType = file.file.type;
            let fileURL = URL.createObjectURL(file.file);
            if (fileType.startsWith('image/')) {
                $('#imagePreview').attr('src', fileURL).show();
                $('#videoPreview').hide();
            } else if (fileType.startsWith('video/')) {
                $('#videoPreview').attr('src', fileURL).show();
                $('#imagePreview').hide();
            }

            $('#submitButton').show(); // Show submit button
        });

        // On submit, start uploading
        submitButton.on('click', function() {
            if (!selectedFile) {
                alert('Please select a file first.');
                return;
            }

            showProgress();
            resumable.upload(); // Start the upload
        });

        // Track upload progress
        resumable.on('fileProgress', function(file) {
            updateProgress(Math.floor(file.progress() * 100));
        });

        // When upload is complete
        resumable.on('fileSuccess', function(file, response) {
            response = JSON.parse(response);

            let videoName = $('#name').val(); // Get video name input
            let fileName = response.name; // Get uploaded file name

            console.log("Video Name:", videoName);
            console.log("Uploaded File Name:", fileName);

            alert('File uploaded successfully!');

            hideProgress();
            // Do not hide submit button
        });

        // On error
        resumable.on('fileError', function(file, response) {
            alert('File uploading error.');
        });

        // Progress bar functions
        let progress = $('.progress');

        function showProgress() {
            progress.find('.progress-bar').css('width', '0%').html('0%').removeClass('bg-success');
            progress.show();
        }

        function updateProgress(value) {
            progress.find('.progress-bar').css('width', `${value}%`).html(`${value}%`);
            if (value === 100) {
                progress.find('.progress-bar').addClass('bg-success');
            }
        }

        function hideProgress() {
            progress.hide();
        }
    </script>

</body>

</html>
