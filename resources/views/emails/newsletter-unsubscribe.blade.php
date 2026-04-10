<!DOCTYPE html>
<html>

<head>
    <title>Unsubscribed from MMRatePro</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            padding: 30px 20px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }

        .content {
            background: #f9fafb;
            padding: 30px;
            border-radius: 0 0 10px 10px;
            text-align: center;
        }

        .button {
            display: inline-block;
            background: #f59e0b;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 8px;
            margin: 20px 0;
        }

        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>😢 You've been unsubscribed</h1>
            <p>We're sad to see you go!</p>
        </div>

        <div class="content">
            <p>You will no longer receive daily rate alerts from <strong>MMRatePro</strong>.</p>

            <p>If this was a mistake or you changed your mind, you can resubscribe anytime:</p>

            <a href="{{ config('app.url') }}" class="button">Subscribe Again</a>

            <p style="font-size: 12px; color: #666; margin-top: 30px;">
                You received this email because you requested to unsubscribe from MMRatePro daily rate alerts.
                <br>
                If you didn't request this, please <a href="mailto:mmratepro@gmail.com">contact us</a>.
            </p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} MMRatePro Currency. All rights reserved.</p>
        </div>
    </div>
</body>

</html>