<!DOCTYPE html>
<html>

<head>
    <title>Daily Exchange Rates - MMRatePro</title>
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

        .rate-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .rate-table th {
            background: #f59e0b;
            color: white;
            padding: 12px;
            text-align: left;
            font-size: 12px;
        }

        .rate-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        .rate-table tr:hover {
            background: #fef3c7;
        }

        .gold-card {
            background: white;
            padding: 15px;
            margin: 20px 0;
            border-radius: 8px;
            border-left: 4px solid #f59e0b;
        }

        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #666;
        }

        .button {
            display: inline-block;
            background: #f59e0b;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 8px;
            margin-top: 20px;
        }

        .trend-up {
            color: #10b981;
        }

        .trend-down {
            color: #ef4444;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>📊 Daily Exchange Rates</h1>
            <p>{{ date('F j, Y') }}</p>
        </div>

        <div class="content">
            <p>Dear subscriber,</p>
            <p>Here are today's live exchange rates and gold prices from MMRatePro.</p>

            <h3>💱 Currency Exchange Rates (MMK per unit)</h3>
            <table class="rate-table">
                <thead>
                    <tr>
                        <th>Currency</th>
                        <th>Buy Rate</th>
                        <th>Sell Rate</th>
                        <th>Change</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rates as $rate)
                    <tr>
                        <td>
                            <strong>{{ $rate['currency']['code'] }}</strong>
                            <span style="font-size: 10px; color: #666;">{{ $rate['currency']['name'] }}</span>
                        </td>
                        <td>{{ number_format($rate['buy_rate'], 2) }} MMK</td>
                        <td>{{ number_format($rate['sell_rate'], 2) }} MMK</td>
                        <td
                            class="{{ $rate['market_trend'] === 'up' ? 'trend-up' : ($rate['market_trend'] === 'down' ? 'trend-down' : '') }}">
                            @if($rate['market_trend'] === 'up') ▲ @elseif($rate['market_trend'] === 'down') ▼ @endif
                            {{ number_format(abs($rate['change_percentage']), 2) }}%
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @if(isset($gold))
            <div class="gold-card">
                <h3>🥇 Gold Prices</h3>
                <p><strong>World Gold Spot:</strong> ${{ number_format($gold['usd_price'], 2) }} / oz</p>
                <p><strong>Myanmar Gold (New System):</strong> {{ number_format($gold['mmk_price_new'], 0) }} MMK /
                    kyatthar</p>
                <p><strong>Myanmar Gold (Old System):</strong> {{ number_format($gold['mmk_price_old'], 0) }} MMK /
                    kyatthar</p>
            </div>
            @endif

            <p style="text-align: center;">
                <a href="{{ config('app.url') }}" class="button">View Live Rates →</a>
            </p>

            <!-- At the bottom of the email -->
            <p style="font-size: 12px; color: #666; margin-top: 30px;">
                You're receiving this because you subscribed to daily rate alerts at MMRatePro.
                <br>
                <a href="{{ route('unsubscribe', $subscriber->email) }}" style="color: #f59e0b;">
                    Unsubscribe
                </a>
            </p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} MMRatePro Currency. All rights reserved.</p>
        </div>
    </div>
</body>

</html>