<!DOCTYPE html>
<html>

<head>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            width: 1200px;
            height: 630px;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            font-family: Arial, sans-serif;
        }

        h1 {
            font-size: 70px;
            color: #f59e0b;
            margin-bottom: 20px;
        }

        p {
            font-size: 28px;
            color: white;
            margin-bottom: 10px;
        }

        .sub {
            font-size: 20px;
            color: #888;
            margin-top: 40px;
        }

        .border {
            position: absolute;
            top: 10px;
            left: 10px;
            right: 10px;
            bottom: 10px;
            border: 3px solid #f59e0b;
            pointer-events: none;
        }
    </style>
</head>

<body>
    <div class="border"></div>
    <h1>MMRatePro</h1>
    <p>Real-time Exchange Rates & Gold Prices</p>
    <p style="font-size: 18px; color: #666;">USD • SGD • EUR • THB to MMK</p>
    <div class="sub">luckeymm.online</div>
</body>

</html>