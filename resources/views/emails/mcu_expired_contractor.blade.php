<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f4f4f4; }
        .header { margin-bottom: 20px; }
        .footer { margin-top: 30px; font-size: 12px; color: #777; }
        .badge-expired { background-color: #fee2e2; color: #991b1b; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Pemberitahuan: Masa Berlaku MCU Karyawan Habis (EXPIRED)</h2>
        <p>Yth. Bapak/Ibu Admin Kontraktor <strong>{{ $contractorName }}</strong>,</p>
        <p>Berikut adalah daftar karyawan dari kontraktor Anda yang masa berlaku Medical Check-Up (MCU)-nya telah habis per hari ini. Sistem telah menandai status mereka sebagai <strong>EXPIRED</strong> dan menjadwalkan ulang secara otomatis.</p>
    </div>

    <p><strong>Total Expired:</strong> {{ count($expiredEmployees) }} Karyawan</p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Karyawan</th>
                <th>ID Badge</th>
                <th>Terakhir MCU</th>
                <th>Tanggal Expired</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expiredEmployees as $index => $employee)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $employee['name'] }}</td>
                <td>{{ $employee['employee_id'] ?? '-' }}</td>
                <td>{{ \Carbon\Carbon::parse($employee['last_mcu_date'])->translatedFormat('d F Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($employee['next_mcu_date'])->translatedFormat('d F Y') }}</td>
                <td><span class="badge-expired">EXPIRED - RESCHEDULE</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Harap segera menindaklanjuti pemberitahuan ini untuk memastikan karyawan Anda melakukan jadwal ulang MCU.</p>
        <p>Email ini dibuat secara otomatis oleh Sistem TOSAR. Mohon tidak membalas email ini.</p>
    </div>
</body>
</html>
