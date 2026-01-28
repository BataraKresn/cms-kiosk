<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Four Sections with Different Content</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            display: flex;
            flex-wrap: wrap;
            width: 100%;
        }

        #section1 {
            width: 100%;
            /* Adjust the width as needed */
            height: 120px;
            /* Adjust the height as needed */
        }

        #section2 {
            width: 100%;
            /* Adjust the width as needed */
            height: 480px;
        }

        #section3 {
            width: 100%;
            /* Adjust the width as needed */
            height: 60%;
            /* Adjust the height as needed */
            background-color: #5733ff;
        }

        #section4 {
            width: 100%;
            /* Adjust the width as needed */
            height: 10%;
            /* Adjust the height as needed */
            background-color: #33ffff;
        }
    </style>
</head>

<body>
    <section id="section1">
        <img src="/content/cover.jpg" alt="Image" height="120" width="100%">
    </section>
    <section id="section2">
        <video height="100%" width="100%" controls autoplay>
            <source src="/content/video.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </section>
    <section id="section3">
        <iframe src="/content/page.html" height="120" width="100%"></iframe>
    </section>
    <section id="section4">
        <div id="runningText">This is a running text that can be updated dynamically.</div>
    </section>
    <script>
        // Function to update the running text
        function updateRunningText() {
            const runningTextElement = document.getElementById('runningText');
            let counter = 0;

            setInterval(() => {
                runningTextElement.textContent = `Running Text: Counter ${counter}`;
                counter++;
            }, 1000); // Update the text every 1 second
        }

        // Call the function to start updating the text
        updateRunningText();
    </script>
</body>

</html>

