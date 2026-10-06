<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pemberitahuan Masa Berlaku MCU Berakhir</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #334155;
            background-color: #f8fafc;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .header {
            background: #dc2626;
            color: #ffffff;
            padding: 22px 28px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 19px;
            font-weight: 600;
            letter-spacing: 0.3px;
        }
        .content {
            padding: 28px;
            font-size: 15px;
            line-height: 1.7;
            color: #1e293b;
        }
        .content p {
            margin: 0 0 16px;
        }
        .highlight-box {
            background-color: #fef2f2;
            border-left: 4px solid #ef4444;
            padding: 14px 18px;
            margin: 20px 0;
            border-radius: 4px;
            color: #991b1b;
            font-weight: 500;
        }
        .footer {
            padding: 20px 28px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            font-size: 12px;
            color: #64748b;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Pemberitahuan Masa Berlaku MCU Berakhir</h1>
        </div>
        <div class="content">
            <p>Yth. Bapak/Ibu <strong>{{ $employeeName }}</strong>,</p>

            <p>Kami informasikan bahwa masa berlaku Medical Check Up (MCU) Anda telah berakhir pada tanggal <strong>{{ $expiredDate }}</strong>.</p>

            <div class="highlight-box">
                Mohon untuk segera melakukan konfirmasi ke Admin Departement untuk dapat dilakukan pendaftaran Medical Check Up (MCU) terbaru dengan menghubungi OHS Department.
            </div>

            <p>Terima kasih atas perhatian dan kerja samanya dalam menjaga kesehatan dan keselamatan kerja.</p>

            <p style="margin-top: 24px; margin-bottom: 0;">
                Salam sehat,<br>
                <strong>OHS Department</strong>
            </p>
        </div>
        <div class="footer">
            Email ini dibuat secara otomatis oleh Sistem TOSAR.<br>
            Mohon tidak membalas langsung ke alamat email ini.
        </div>
    </div>
</body>
</html>
