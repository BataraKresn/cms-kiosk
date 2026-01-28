<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Device Status Chart</title>
    <style>
        .chart-container {
            width: 100%;
            height: 300px;
            text-align: center;
        }
    </style>
</head>

<body>
    <h1>Device Status Chart</h1>
    <div class="chart-container">
        <canvas id="deviceStatusChart"></canvas>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('deviceStatusChart').getContext('2d');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: @json($data['labels']),
                datasets: [{
                    data: @json($data['datasets'][0]['data']),
                    backgroundColor: @json($data['datasets'][0]['colors']),
                }]
            },
            options: {
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>

</html>
