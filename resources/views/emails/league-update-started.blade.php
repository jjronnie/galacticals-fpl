<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>League Update Mail</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #008080;
            /* teal */
            font-family: Arial, sans-serif;
            color: #ffffff;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
            background-color: #006666;
            /* darker teal */
            border-radius: 10px;
        }

        .header {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .content p {
            font-size: 16px;
            line-height: 1.5;
            margin-bottom: 15px;
        }

        .status-box {
            display: inline-block;
            background-color: #00bfa5;
            /* lighter teal */
            color: #ffffff;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            margin: 15px 0;
        }

        .footer {
            margin-top: 30px;
            font-size: 14px;
            text-align: center;
            color: #cccccc;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">League Update</div>

        <div class="content">
            <p>Hello,</p>
            <p>Your league update command has just started running.</p>

            <div class="status-box">Started: {{ now()->toDayDateTimeString() }}</div>

            <p>Regards,<br>TheTechTower Team</p>
        </div>

        <div class="footer">
            &copy; 2025 TheTechTower. All rights reserved.
        </div>
    </div>
</body>

</html>