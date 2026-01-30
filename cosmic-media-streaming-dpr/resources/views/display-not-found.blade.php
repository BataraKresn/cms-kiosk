<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Not Found - {{ $token }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #fff;
        }

        .container {
            text-align: center;
            max-width: 600px;
            padding: 40px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .icon {
            font-size: 80px;
            margin-bottom: 20px;
            opacity: 0.9;
        }

        h1 {
            font-size: 32px;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .token {
            font-size: 24px;
            font-family: 'Courier New', monospace;
            background: rgba(255, 255, 255, 0.2);
            padding: 10px 20px;
            border-radius: 8px;
            display: inline-block;
            margin: 20px 0;
            letter-spacing: 2px;
        }

        .message {
            font-size: 18px;
            line-height: 1.6;
            margin: 20px 0;
            opacity: 0.9;
        }

        .steps {
            text-align: left;
            margin: 30px 0;
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 10px;
        }

        .steps h3 {
            margin-bottom: 15px;
            font-size: 20px;
        }

        .steps ol {
            margin-left: 20px;
        }

        .steps li {
            margin: 10px 0;
            line-height: 1.5;
        }

        .footer {
            margin-top: 30px;
            font-size: 14px;
            opacity: 0.7;
        }

        .refresh-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background: rgba(255, 255, 255, 0.9);
            color: #667eea;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .refresh-btn:hover {
            background: #fff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🚫</div>
        <h1>Display Not Found</h1>
        
        <div class="token">{{ $token }}</div>
        
        <div class="message">
            {{ $message ?? 'This display token has not been registered in the system.' }}
        </div>

        <div class="steps">
            <h3>📋 What to do:</h3>
            <ol>
                <li>Contact your system administrator</li>
                <li>Admin needs to create this Display in CMS</li>
                <li>Admin should use token: <strong>{{ $token }}</strong></li>
                <li>Admin assigns a Schedule to the Display</li>
                <li>Refresh this page or restart the app</li>
            </ol>
        </div>

        <a href="{{ url()->current() }}" class="refresh-btn">🔄 Refresh Page</a>

        <div class="footer">
            <p>Cosmic Media Streaming Platform</p>
            <p>Token: {{ $token }}</p>
        </div>
    </div>

    <script>
        // Auto refresh every 30 seconds
        setTimeout(() => {
            location.reload();
        }, 30000);
    </script>
</body>
</html>
