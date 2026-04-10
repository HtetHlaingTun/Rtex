<!DOCTYPE html>
<html>

<head>
    <title>Welcome to MMRatePro</title>
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
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            padding: 30px 20px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }

        .content {
            background: #f9fafb;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }

        .rate-card {
            background: white;
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            border-left: 4px solid #f59e0b;
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
            <h1>🎉 Welcome to MMRatePro!</h1>
            <p>Your trusted source for Myanmar currency rates</p>
        </div>

        <div class="content">
            <p>Dear subscriber,</p>

            <p>Thank you for subscribing to <strong>MMRatePro</strong> daily rate alerts!</p>

            <p>You'll now receive:</p>

            <div class="rate-card">
                <strong>💱 Live Exchange Rates</strong><br>
                USD, SGD, EUR, THB and more currencies
            </div>

            <div class="rate-card">
                <strong>🥇 Gold Prices</strong><br>
                World gold spot + Myanmar gold prices
            </div>

            <div class="rate-card">
                <strong>📊 Market Updates</strong><br>
                Daily trends and analysis
            </div>

            <p style="text-align: center;">
                <a href="{{ config('app.url') }}" class="button">Visit MMRatePro</a>
            </p>

            <!-- At the bottom of the email -->
            <p style="font-size: 12px; color: #666; margin-top: 30px;">
                You're receiving this because you subscribed to daily rate alerts at MMRatePro.
                <br>
                <a href="{{ route('unsubscribe', $subscriber->email) }}" style="color: #f59e0b;">
                    Click here to unsubscribe
                </a>
            </p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} MMRatePro Currency. All rights reserved.</p>
        </div>
    </div>
</body>

</html>