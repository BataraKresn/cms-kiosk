<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <title>Documents</title>
    <link rel="stylesheet" href="https://unpkg.com/grapesjs/dist/css/grapes.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body style="width: 100%; min-height: 100vh; margin: 0; padding: 0; backgroud-color: black;">
    @yield('content')
</body>

</html>
