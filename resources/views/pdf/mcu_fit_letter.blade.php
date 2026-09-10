<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Keterangan Sehat (FIT TO WORK)</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            margin: 40px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0 0 0;
            font-size: 14px;
        }
        .title {
            text-align: center;
            font-weight: bold;
            font-size: 18px;
            text-decoration: underline;
            margin-bottom: 30px;
        }
        .content {
            margin-bottom: 40px;
        }
        .table-info {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .table-info td {
            padding: 5px;
            vertical-align: top;
        }
        .table-info td:first-child {
            width: 30%;
            font-weight: bold;
        }
        .table-info td:nth-child(2) {
            width: 5%;
        }
        .footer {
            margin-top: 50px;
            width: 100%;
        }
        .signature-box {
            float: right;
            text-align: center;
            width: 300px;
        }
        .signature-space {
            height: 100px;
        }
        .status-box {
            padding: 10px;
            border: 1px solid #000;
            font-weight: bold;
            display: inline-block;
            margin-top: 20px;
            text-transform: uppercase;
        }
        .notes {
            margin-top: 20px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>KLINIK PERUSAHAAN</h1>
        <p>Medical Check-Up (MCU) Report</p>
    </div>

    <div class="title">
        SURAT KETERANGAN FIT TO WORK
    </div>

    <div class="content">
        <p>Yang bertanda tangan di bawah ini, Dokter Pemeriksa Klinik Perusahaan menerangkan bahwa:</p>
        
        <table class="table-info">
            <tr>
                <td>Nama Lengkap</td>
                <td>:</td>
                <td>{{ $employee->name ?? '-' }}</td>
            </tr>
            <tr>
                <td>Nomor Pegawai / NIK</td>
                <td>:</td>
                <td>{{ $employee->nik ?? '-' }}</td>
            </tr>
            <tr>
                <td>Tanggal MCU</td>
                <td>:</td>
                <td>{{ $schedule->schedule_date ? $schedule->schedule_date->format('d F Y') : '-' }}</td>
            </tr>
        </table>

        <p>Telah menjalani pemeriksaan kesehatan (Medical Check-Up) dan berdasarkan hasil pemeriksaan medis, yang bersangkutan dinyatakan:</p>

        <div style="text-align: center;">
            <div class="status-box">
                {{ str_replace('_', ' ', $result->status) }}
            </div>
        </div>

        @if($result->status === 'fit_with_notes' && $result->doctor_site_consult)
        <div class="notes">
            <p><strong>Catatan Batasan Kerja (Site Consult):</strong></p>
            <p>{{ $result->doctor_site_consult }}</p>
        </div>
        @endif

        <p style="margin-top: 30px;">Surat keterangan ini diberikan untuk dapat dipergunakan sebagaimana mestinya.</p>
    </div>

    <div class="footer">
        <div class="signature-box">
            <p>Dikeluarkan pada tanggal: {{ date('d F Y') }}</p>
            <p><strong>Dokter Pemeriksa,</strong></p>
            <div class="signature-space"></div>
            <p>_________________________</p>
        </div>
    </div>

</body>
</html>
