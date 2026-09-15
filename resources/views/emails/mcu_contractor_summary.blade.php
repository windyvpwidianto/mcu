<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Jadwal MCU Tahunan</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { width: 100%; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #0284c7; color: white; padding: 15px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { padding: 20px; border: 1px solid #ddd; border-top: none; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; font-size: 13px; }
        th { background-color: #f3f4f6; }
        .footer { margin-top: 20px; font-size: 12px; color: #666; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin:0;">Rekap Jadwal MCU Tahunan</h2>
            <p style="margin:5px 0 0 0;">{{ $contractor->contractor_name }}</p>
        </div>
        
        <div class="content">
            <p>Halo Admin Contractor,</p>
            <p>Berikut adalah rekapitulasi pengingat jadwal Medical Check-Up (MCU) tahunan untuk karyawan di bawah perusahaan Anda.</p>
            
            <p><strong>Total Karyawan dalam batch ini:</strong> {{ $users->count() }} orang</p>
            <p><strong>Tahap Pengingat:</strong> {{ str_replace('_', ' ', $stage) }}</p>
            
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>ID Badge</th>
                        <th>Nama Karyawan</th>
                        <th>Jadwal MCU Berikutnya</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $index => $user)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $user->employee_id ?? '-' }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ \Carbon\Carbon::parse($user->next_mcu_date)->translatedFormat('d F Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            <p style="margin-top:20px;">Mohon untuk segera menginformasikan kepada karyawan bersangkutan agar dapat mempersiapkan diri dan hadir sesuai dengan target jadwal MCU.</p>
        </div>
        
        <div class="footer">
            &copy; {{ date('Y') }} Klinik Perusahaan. Email ini dikirim secara otomatis, mohon tidak membalas.
        </div>
    </div>
</body>
</html>
