<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Display Registered</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 720px;
            margin: 0 auto;
            padding: 80px 24px;
            text-align: center;
        }

        .card {
            background: #111827;
            border: 1px solid #1f2937;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 12px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            background: #1d4ed8;
            color: #fff;
        }

        .token {
            font-size: 20px;
            font-weight: 700;
            color: #38bdf8;
            word-break: break-all;
        }

        .muted {
            color: #94a3b8;
        }

        .cta {
            margin-top: 24px;
            padding: 12px 18px;
            border-radius: 10px;
            background: #22c55e;
            color: #0f172a;
            display: inline-block;
            font-weight: 700;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card">
            <div class="badge">Display Registered</div>
            <h1>Device siap, konten belum di-assign</h1>
            <p class="muted">Token sudah terdaftar di CMS. Silakan assign schedule untuk menampilkan konten.</p>
            <p class="token">{{ $token }}</p>
            <p class="muted">Nama Display: {{ $display->name }}</p>
            <p class="muted">ID Display: {{ $display->id }}</p>
            <span class="cta">Buka CMS dan set Schedule</span>
        </div>
    </div>
</body>

</html>
