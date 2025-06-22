<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your OTP Code</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            color: #333;
            padding: 30px;
        }

        .container {
            max-width: 600px;
            margin: auto;
            background-color: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .otp-code {
            font-size: 36px;
            color: #2c3e50;
            font-weight: bold;
            text-align: center;
            margin: 30px 0;
        }

        .footer {
            margin-top: 40px;
            font-size: 14px;
            color: #999;
            text-align: center;
        }

        h2 {
            color: #1d3557;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2> You're Welcome {{ $name }}</h2>

        <p>Please use the OTP code below to verify your account:</p>

        <div class="otp-code">{{ $code }}</div>

        <p>This OTP will expire in 10 minutes.</p>

        <div class="footer">
            Thank you,<br>
            {{ config('app.name') }}
        </div>
    </div>
</body>
</html>
