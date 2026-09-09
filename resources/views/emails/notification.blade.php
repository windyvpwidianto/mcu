<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>{{ $subject ?? 'Notifikasi Sistem' }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
            background: #eef3f9;
            padding: 20px;
        }

        .email-container {
            max-width: 650px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .logo-header {
            background: #ffffff;
            text-align: center;
            padding: 20px 20px 10px 20px;
        }

        .logo-header img {
            max-width: 160px;
            height: auto;
        }

        .header-bar {
            background: #005fbb;
            padding: 25px;
            color: #ffffff;
            text-align: center;
        }

        .header-bar h1 {
            margin: 0;
            font-size: 21px;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        .content {
            padding: 25px;
            font-size: 15px;
            color: #333;
            line-height: 1.6;
        }

        .content p {
            margin-bottom: 15px;
        }

        .info-box {
            background: #f1f8ff;
            border-left: 4px solid #005fbb;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .btn-primary {
            display: inline-block;
            background: #005fbb;
            padding: 12px 24px;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            margin-top: 15px;
        }

        .footer {
            text-align: center;
            padding: 20px;
            font-size: 13px;
            background: #f5f7fa;
            color: #777;
        }

        .footer small {
            display: block;
            margin-top: 8px;
            color: #999;
        }
    </style>
</head>

<body>

    <div class="email-container">

        {{-- Logo Perusahaan --}}
        <div class="logo-header">
            <img src="{{ secure_url('favicon.svg') }}" alt="Company Logo">
        </div>

        {{-- Bar Judul --}}
        <div class="header-bar">
            <h1>{{ $title ?? 'Notifikasi Sistem' }}</h1>
        </div>

        {{-- Isi Konten --}}
        <div class="content">

            <p>{!! nl2br(e($messageText ?? '')) !!}</p>

            @isset($additionalInfo)
                <div class="info-box">
                    {!! nl2br(e($additionalInfo)) !!}
                </div>
            @endisset

            @isset($actionUrl)
                <a href="{{ $actionUrl }}" class="btn-primary">Lihat Detail</a>
            @endisset

        </div>

        {{-- Footer --}}
        <div class="footer">
            Email ini dikirim otomatis oleh sistem.
            <small>{{ config('app.name') }}</small>
        </div>

    </div>

</body>

</html>
