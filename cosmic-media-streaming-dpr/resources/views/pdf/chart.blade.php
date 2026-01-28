<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>PDF</title>
</head>

<body>
    <div>
        <canvas id="deviceStatusChart"></canvas>
    </div>

    <div>
        <canvas id="mediaPlaybackDurationChart" style="margin-top: 30px;"></canvas>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const ctx = document.getElementById('deviceStatusChart').getContext('2d');
        const url = "{{ env('GENERATE_PDF_SERVICE_URL', 'https://kiosk.mugshot.dev/generate-pdf-internal') }}";

        async function DataStatusDevices() {
            const response = await fetch(url + '/status_service');
            const fetchedData = await response.json();
            console.log(fetchedData);

            const chartData = {
                labels: [
                    "Connected : " + fetchedData.connect,
                    "Disconnected : " + fetchedData.disconnect,
                ],
                datasets: [{
                    label: 'Device mantap',
                    data: [fetchedData.connect, fetchedData
                        .disconnect
                    ], // Match data points to labels
                    backgroundColor: [
                        'rgb(255, 99, 132)', // Red for disconnected
                        'rgb(54, 162, 235)', // Blue for connected
                    ],
                    hoverOffset: 4
                }]
            };

            const deviceStatusChart = new Chart(ctx, {
                type: 'pie',
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: true, // Allows setting custom width and height
                    aspectRatio: 2, // Adjust aspect ratio to fit the widget layout
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Device Mantap'
                        }
                    }
                }
            });
        }

        DataStatusDevices();

        const cts = document.getElementById('mediaPlaybackDurationChart').getContext('2d');

        async function DataMediaPlaybackDurationChart() {
            const response = await fetch(url + '/graph_playlist');
            const fetchedData = await response.json();

            const array = fetchedData.name;
            const dataLabels = [];
            for (let index = 0; index < array.length; index++) {
                const element = array[index] + " : " + fetchedData.total[index];
                dataLabels.push(element);
            }
            const chartData = {
                labels: dataLabels,
                datasets: [{
                    label: 'Graphpic Layout Of Media Playlist',
                    data: fetchedData.total,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(255, 159, 64, 0.2)',
                        'rgba(255, 205, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(201, 203, 207, 0.2)'
                    ],
                    borderColor: [
                        'rgb(255, 99, 132)',
                        'rgb(255, 159, 64)',
                        'rgb(255, 205, 86)',
                        'rgb(75, 192, 192)',
                        'rgb(54, 162, 235)',
                        'rgb(153, 102, 255)',
                        'rgb(201, 203, 207)'
                    ],
                    borderWidth: 1
                }]
            };

            const deviceStatusChart = new Chart(cts, {
                type: 'bar',
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: true, // Allows setting custom width and height
                    aspectRatio: 2, // Adjust aspect ratio to fit the widget layout
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                        title: {
                            display: true,
                            text: 'Graphpic Layout Of Media Playlist'
                        }
                    }
                }
            });
        }

        DataMediaPlaybackDurationChart();
    </script>

</body>

</html>
