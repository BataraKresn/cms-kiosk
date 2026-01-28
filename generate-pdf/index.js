const express = require('express');
const puppeteer = require('puppeteer');
const path = require('path');
var cors = require('cors');
const morgan = require('morgan');
const pool = require('./database');
const fs = require('fs');
const events = require("events");
const fsYaudah = require('fs').promises;
const multer = require('multer');
const { exec } = require('child_process');
const WebSocket = require('ws');
require('dotenv').config();

const app = express();
const port = process.env.PORT || 3333;

app.use(cors())
app.use(morgan('dev'));

// Health check endpoint
app.get('/health', (req, res) => {
    res.status(200).json({ 
        status: 'healthy',
        service: 'generate-pdf',
        timestamp: new Date().toISOString()
    });
});

app.head('/health', (req, res) => {
    res.status(200).end();
});
// Middleware to generate a unique nonce for inline scripts
// Middleware to generate a unique nonce for inline scripts
app.use((req, res, next) => {
    res.locals.nonce = Buffer.from(Date.now().toString()).toString('base64');
    next();
});

// Function to generate the PDF
async function generatePdf(urlToLoad) {
    const browser = await puppeteer.launch({
        headless: true,
        // executablePath: '/usr/bin/google-chrome',
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-gpu',
            '--disable-software-rasterizer',
            '--disable-dev-shm-usage'
        ]
    });

    if (browser) {
        console.log('Browser launched successfully');
    } else {
        console.log('Browser launched successfully');
    }

    const page = await browser.newPage();
    
    // Log page console messages and errors for debugging
    page.on('console', msg => console.log('PAGE LOG:', msg.text()));
    page.on('error', err => console.error('PAGE ERROR:', err));

    // Use the provided URL for the content to load in the browser
    await page.goto(urlToLoad, { waitUntil: 'networkidle0', timeout: 60000 });  // waitUntil: 'networkidle0' ensures all network requests are completed

    // Wait for the data to be fetched and charts to render
    try {
        await page.waitForFunction(() => {
            const canvas1 = document.getElementById('deviceStatusChart');
            const canvas2 = document.getElementById('mediaPlaybackDurationChart');
            // Check if both canvases have content rendered
            return canvas1 && canvas2 && canvas1.offsetHeight > 0 && canvas2.offsetHeight > 0;
        }, { timeout: 10000 });
        console.log('Charts rendered successfully');
    } catch (e) {
        console.log('Timeout waiting for charts, but continuing anyway');
        // Wait additional 3 seconds for chart rendering
        await page.evaluate(async () => {
            await new Promise(resolve => setTimeout(resolve, 3000));
        });
    }

    // Generate the PDF file
    const filePath = path.join(__dirname, 'output.pdf');
    await page.pdf({ path: filePath, format: 'A4', printBackground: true });

    // Close the browser after generating the PDF
    await browser.close();

    // Return the file path for the generated PDF
    return filePath;
}

// API route to trigger PDF generation and return the file for download
app.get('/generate-pdf', async (req, res) => {
    try {
        const urlToLoad = req.query.url;
        const pdfPath = await generatePdf(urlToLoad);
        res.download(pdfPath, 'output.pdf', (err) => {
            if (err) {
                res.status(500).send('Error while sending the PDF.');
            }
        });
    } catch (error) {
        console.error("PDF generation error:", error);  // Log the error details
        res.status(500).send('Error generating the PDF.');
    }
});

// Serve static files (for frontend)
app.use(express.static(path.join(__dirname, 'public')));
app.use('/node_modules', express.static(path.join(__dirname, 'node_modules')));

// Serve index.html with injected environment variable
app.get('/view-pdf', (req, res) => {
    const filePath = path.join(__dirname, 'views', 'view-pdf.html');
    res.sendFile(filePath);

    // const serviceUrl = process.env.SERVICE_REMOTE_DEVICE || 'http://localhost:3333';

    // Inject the serviceUrl properly in the HTML response
    // res.send(`
    //     <!DOCTYPE html>
    //     <html lang="en">
    //     <head>
    //         <meta charset="UTF-8">
    //         <meta name="viewport" content="width=device-width, initial-scale=1.0">
    //         <meta http-equiv="X-UA-Compatible" content="ie=edge">
    //         <title>PDF</title>
    //     </head>
    //     <body>
    //         <div>
    //             <canvas id="deviceStatusChart"></canvas>
    //         </div>

    //         <div>
    //             <canvas id="mediaPlaybackDurationChart" style="margin-top: 30px;"></canvas>
    //         </div>

    //         <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    //         <script>
    //             // Injected environment variable
    //             const url = "${serviceUrl}";  // Express server injects the value of serviceUrl

    //             // Device Status Chart
    //             const deviceStatusCtx = document.getElementById('deviceStatusChart').getContext('2d');

    //             async function DataStatusDevices() {
    //                 try {
    //                     const response = await fetch(url + '/status_service');
    //                     const fetchedData = await response.json();
    //                     console.log(fetchedData);

    //                     const chartData = {
    //                         labels: [
    //                             "Connected : " + fetchedData.connect,
    //                             "Disconnected : " + fetchedData.disconnect,
    //                         ],
    //                         datasets: [{
    //                             label: 'Device Status',
    //                             data: [fetchedData.connect, fetchedData.disconnect], // Match data points to labels
    //                             backgroundColor: [
    //                                 'rgb(255, 99, 132)', // Red for disconnected
    //                                 'rgb(54, 162, 235)', // Blue for connected
    //                             ],
    //                             hoverOffset: 4
    //                         }]
    //                     };

    //                     // Initialize the chart
    //                     new Chart(deviceStatusCtx, {
    //                         type: 'pie',
    //                         data: chartData,
    //                         options: {
    //                             responsive: true,
    //                             maintainAspectRatio: true, // Allows setting custom width and height
    //                             aspectRatio: 2, // Adjust aspect ratio to fit the widget layout
    //                             plugins: {
    //                                 legend: {
    //                                     position: 'bottom',
    //                                 },
    //                                 title: {
    //                                     display: true,
    //                                     text: 'Device Status'
    //                                 }
    //                             }
    //                         }
    //                     });
    //                 } catch (error) {
    //                     console.error("Error fetching data for device status:", error);
    //                 }
    //             }

    //             // Call function for device status chart
    //             DataStatusDevices();

    //             // Media Playback Duration Chart
    //             const mediaPlaybackCtx = document.getElementById('mediaPlaybackDurationChart').getContext('2d');

    //             async function DataMediaPlaybackDurationChart() {
    //                 try {
    //                     const response = await fetch(url + '/graph_playlist');
    //                     const fetchedData = await response.json();

    //                     const array = fetchedData.name;
    //                     const dataLabels = array.map((item, index) => \`\${item} : \${fetchedData.total[index]}\`);

    //                     const chartData = {
    //                         labels: dataLabels,
    //                         datasets: [{
    //                             label: 'Graphpic Layout Of Media Playlist',
    //                             data: fetchedData.total,
    //                             backgroundColor: [
    //                                 'rgba(255, 99, 132, 0.2)',
    //                                 'rgba(255, 159, 64, 0.2)',
    //                                 'rgba(255, 205, 86, 0.2)',
    //                                 'rgba(75, 192, 192, 0.2)',
    //                                 'rgba(54, 162, 235, 0.2)',
    //                                 'rgba(153, 102, 255, 0.2)',
    //                                 'rgba(201, 203, 207, 0.2)'
    //                             ],
    //                             borderColor: [
    //                                 'rgb(255, 99, 132)',
    //                                 'rgb(255, 159, 64)',
    //                                 'rgb(255, 205, 86)',
    //                                 'rgb(75, 192, 192)',
    //                                 'rgb(54, 162, 235)',
    //                                 'rgb(153, 102, 255)',
    //                                 'rgb(201, 203, 207)'
    //                             ],
    //                             borderWidth: 1
    //                         }]
    //                     };

    //                     // Initialize the chart
    //                     new Chart(mediaPlaybackCtx, {
    //                         type: 'bar',
    //                         data: chartData,
    //                         options: {
    //                             responsive: true,
    //                             maintainAspectRatio: true, // Allows setting custom width and height
    //                             aspectRatio: 2, // Adjust aspect ratio to fit the widget layout
    //                             plugins: {
    //                                 legend: {
    //                                     position: 'bottom',
    //                                 },
    //                                 title: {
    //                                     display: true,
    //                                     text: 'Graphpic Layout Of Media Playlist'
    //                                 }
    //                             }
    //                         }
    //                     });
    //                 } catch (error) {
    //                     console.error("Error fetching data for media playback duration chart:", error);
    //                 }
    //             }

    //             // Call function for media playback duration chart
    //             DataMediaPlaybackDurationChart();
    //         </script>
    //     </body>
    //     </html>
    // `);
});

app.get('/data_table', async (req, res) => {
    try {
        // Query the database
        const [status_device] = await pool.query("SELECT id, name, status FROM remotes WHERE deleted_at IS NULL");
        const [media_playlist] = await pool.query(`SELECT 
                    d.name, 
                    s.name as schedule_name,
                    p.name as playlist_name,
                    p.id AS playlist_id, 
                    COUNT(pl.id) AS total
                FROM 
                    displays d
                INNER JOIN 
                    schedules s ON s.id = d.schedule_id
                INNER JOIN 
                    schedule_playlists sp ON sp.schedule_id = s.id
                INNER JOIN 
                    playlists p ON p.id = sp.playlist_id
                INNER JOIN 
                    playlist_layouts pl ON pl.playlist_id = p.id
                WHERE d.deleted_at IS NULL
                GROUP BY 
                    d.name, p.id;`);

        // Send the result as JSON
        res.json({
            status_device: status_device,
            media_playlist: media_playlist
        });
    } catch (error) {
        // Handle potential errors
        console.error(error);
        res.status(500).json({ error: 'An error occurred while fetching data' });
    }
});

app.set('views', path.join(__dirname, 'views'));
app.set('view engine', 'html');

// Route for the homepage
app.get('/custom-layout', (req, res) => {
    const id = req.query.id
    console.log(id);

    if (!id) {
        return res.status(400).send('Missing "id" parameter');
    }

    const filePath = path.join(__dirname, 'views', 'index.html');
    console.log(filePath);

    // Read the HTML file and inject the ID
    fs.readFile(filePath, 'utf8', (err, data) => {
        if (err) {
            return res.status(500).send('Error loading the HTML file');
        }

        // Inject the ID into the HTML (e.g., within a script tag or as part of the content)
        const modifiedHtml = data.replace(
            '</body>',
            `<script>const layoutId = "${id}"; console.log("Layout ID:", layoutId);</script></body>`
        );

        res.send(modifiedHtml); // Send the modified HTML
    });
});

// Database route for data-layout
app.get('/data-layout', async (req, res) => {
    const layoutId = req.query.layout_id;

    const [result] = await pool.query(`SELECT *
        FROM spots
        LEFT JOIN layouts ON spots.layout_id = layouts.id
        LEFT JOIN screens ON layouts.screen_id = screens.id
        WHERE layout_id = ?`, [layoutId]);

    const data = [];
    for (let index = 0; index < result.length; index++) {
        const item = result[index];
        data.push({
            content: 'Item' + (index + 1),
            x: item.x,
            y: item.y,
            w: item.w,
            h: item.h,
        })
    }
    res.json(data)
});

app.get('/yaudah', async (req, res) => {
    try {
        const id = req.query.token

        if (!id) {
            return res.status(400).send('Missing "id" parameter');
        }

        // You can use the parameters inside the HTML or customize the response
        const filePath = path.join(__dirname, 'views', 'yaudah.html');
        const fileContent = await fsYaudah.readFile(filePath, 'utf8');

        res.send(fileContent);
    } catch (error) {
        console.error('Error reading file:', error);
        res.status(500).send('Internal Server Error');
    }
});

app.get('/emedia', (req, res) => {
    res.send(`
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
</head>
<body>
    <a id="myLink" href="https://emedia.dpr.go.id" target="_blank"></a>

    <script>
        // Tunggu hingga halaman selesai dimuat
        window.onload = function() {
            // Ambil elemen link
            const link = document.getElementById("myLink");

            // Simulasikan klik pada link
            if (link) {
                link.click();
            }
        };
    </script>
</body>
</html>
    `);
});



app.get("/graph_playlist", async (req, res) => {
    const [result] = await pool.query(
        `
        SELECT 
            d.name, 
            p.id AS playlist_id, 
            COUNT(pl.id) AS total
        FROM 
            displays d
        INNER JOIN 
            schedules s ON s.id = d.schedule_id
        INNER JOIN 
            schedule_playlists sp ON sp.schedule_id = s.id
        INNER JOIN 
            playlists p ON p.id = sp.playlist_id
        INNER JOIN 
            playlist_layouts pl ON pl.playlist_id = p.id
        WHERE d.deleted_at IS NULL 
        GROUP BY 
            d.name, p.id;
        `
    )

    const resultName = []
    const resultTotal = []
    result.forEach(element => {
        resultName.push(element.name)
        resultTotal.push(element.total)

    });

    res.status(200).json({
        message: "Graph Playlist",
        status: 200,
        name: resultName,
        total: resultTotal,
    });
})

app.get('/status_service', async (req, res) => {
    const [result] = await pool.query(
        `
            SELECT status FROM remotes WHERE deleted_at IS NULL
        `
    );

    const totalConnect = []
    const totalDisconnect = []
    result.forEach(element => {
        if (element.status === "Connected") {
            totalConnect.push("Connected")
        } else {
            totalDisconnect.push("Disconnected")
        }
    });

    const resultConnect = totalConnect.length
    const resultDisconnect = totalDisconnect.length

    res.status(200).json({
        message: "Status Device",
        status: 200,
        connect: resultConnect,
        disconnect: resultDisconnect,
    });
})

// Set up multer to handle file uploads
const storage = multer.diskStorage({
    destination: function (req, file, cb) {
        cb(null, 'uploads/');  // Store uploaded files in the 'uploads' directory
    },
    filename: function (req, file, cb) {
        cb(null, Date.now() + path.extname(file.originalname));  // Unique file name
    }
});

const upload = multer({ storage: storage });

// Always use a fixed output directory
const OUTPUT_DIR = path.join(__dirname, 'hls_output');
const HLS_PUBLIC_URL = process.env.HLS_PUBLIC_URL || `http://localhost:${port}/hls/`;

// Ensure the output directory exists
if (!fs.existsSync(OUTPUT_DIR)) {
    fs.mkdirSync(OUTPUT_DIR, { recursive: true });
}

// Endpoint to trigger the conversion (with form-data)
app.post('/convert', upload.single('videoFile'), (req, res) => {
    const inputFile = req.file.path;

    const outputFile = path.join(OUTPUT_DIR, req.file.filename + '.m3u8');

    // FFmpeg command to convert MP4 to HLS
    const ffmpegCommand = `ffmpeg -i "${inputFile}" -codec: copy -start_number 0 -hls_time 10 -hls_list_size 0 -f hls "${outputFile}"`;

    console.log(`Executing command: ${ffmpegCommand}`);

    const process = exec(ffmpegCommand);

    // Capture FFmpeg output
    process.stdout.on('data', (data) => console.log(`FFmpeg Output: ${data}`));
    process.stderr.on('data', (data) => console.error(`FFmpeg Error: ${data}`));

    // When FFmpeg finishes
    process.on('close', (code) => {
        if (code === 0) {
            // Generate the URL to the .m3u8 file
            const hlsUrl = `${HLS_PUBLIC_URL}${req.file.filename}.m3u8`;

            // Respond with success and the HLS URL
            res.json({
                message: 'HLS conversion completed successfully.',
                hlsUrl: hlsUrl
            });
        } else {
            res.status(500).json({ error: `FFmpeg process exited with code ${code}` });
        }
    });
});

// Serve HLS files statically (e.g., through /hls endpoint)
app.use('/hls', express.static(OUTPUT_DIR));

app.get('/send_refresh_device', (req, res) => {
    try {
        const token = req.query.token;

        if (!token) {
            return res.status(400).send('Token is required');
        }

        console.log(`Token received: ${token}`);

        const refreshWsUrl = process.env.REFRESH_WS_URL || 'ws://127.0.0.1:3335/ws';
        const ws = new WebSocket(refreshWsUrl);

        ws.on('error', (error) => {
            console.error('WebSocket error:', error);
            return res.status(500).send('WebSocket connection failed');
        });

        ws.on('open', () => {
            // Send the token to the WebSocket server
            ws.send(token, (err) => {
                if (err) {
                    console.error('Failed to send token:', err);
                    return res.status(500).send('Failed to send token');
                }
                console.log('Token sent to WebSocket server');

                // Close the WebSocket connection after sending the message
                ws.close();

                // Respond to the client
                res.status(200).send('Token sent successfully');
            });
        });
    } catch (error) {
        console.error('Error handling /send_refresh_device:', error);
        res.status(500).send('An error occurred while processing the request');
    }
});

app.listen(port, '0.0.0.0', () => {
    console.log(`Server is running at http://0.0.0.0:${port}`);
});